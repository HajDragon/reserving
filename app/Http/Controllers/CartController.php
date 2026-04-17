<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Mail\ReservationOrderSubmittedToAdminMail;
use App\Mail\ReservationPendingReviewMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private readonly AvailabilityService $availabilityService) {}

    public function index(Request $request): View
    {
        $cart = $request->user()
            ->cart()
            ->firstOrCreate([]);

        $cart->load(['items.product']);

        return view('carts.index', [
            'cart' => $cart,
            'user' => $request->user(),
        ]);
    }

    public function store(StoreCartItemRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $cart = $this->currentUserCart($request->user());
        $defaultStartTime = Carbon::now()->addDay()->startOfHour();
        $defaultEndTime = $defaultStartTime->copy()->addHours(2);

        $cartItem = $cart->items()->create([
            'product_id' => $validated['product_id'],
            'start_time' => $defaultStartTime,
            'end_time' => $defaultEndTime,
            'requested_quantity' => 1,
            'extra_wishes' => null,
        ]);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Item added to cart successfully.');
        }

        return response()->json([
            'message' => 'Cart item added successfully.',
            'cart_item' => $cartItem->load('product'),
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $ownedCartItem = $this->currentUserCart($request->user())->items()->findOrFail($cartItem->getKey());

        $ownedCartItem->update([
            'product_id' => $validated['product_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'requested_quantity' => $validated['requested_quantity'],
            'extra_wishes' => $validated['extra_wishes'] ?? null,
        ]);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('carts.index')
                ->with('status', 'Cart item updated successfully.');
        }

        return response()->json([
            'message' => 'Cart item updated successfully.',
            'cart_item' => $ownedCartItem->fresh()->load('product'),
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $ownedCartItem = $this->currentUserCart($request->user())->items()->findOrFail($cartItem->getKey());
        $ownedCartItem->delete();

        if (! $request->expectsJson()) {
            return redirect()
                ->route('carts.index')
                ->with('status', 'Cart item removed successfully.');
        }

        return response()->json([
            'message' => 'Cart item removed successfully.',
        ]);
    }

    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $cart = $this->currentUserCart($request->user());
        $cart->load(['items']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Your cart is empty. Add items before checkout.'],
            ]);
        }

        $reservationOrder = DB::transaction(function () use ($cart, $request) {
            $cartItems = $cart->items()->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Your cart is empty. Add items before checkout.'],
                ]);
            }

            $productIds = $cartItems->pluck('product_id')->unique()->sort()->values();

            $lockedProducts = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $reservationOrder = ReservationOrder::query()->create([
                'user_id' => $request->user()->id,
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $lockedProducts->get($cartItem->product_id);

                if (! $product instanceof Product) {
                    throw ValidationException::withMessages([
                        "items.{$cartItem->id}" => ['A selected product is no longer available.'],
                    ]);
                }

                if ($cartItem->requested_quantity > $product->quantity) {
                    throw ValidationException::withMessages([
                        "items.{$cartItem->id}" => ['The requested quantity exceeds available product quantity.'],
                    ]);
                }

                if ($cartItem->requested_quantity > $product->available_quantity) {
                    throw ValidationException::withMessages([
                        "items.{$cartItem->id}" => ['The requested quantity exceeds current available inventory.'],
                    ]);
                }

                $isAvailable = $this->availabilityService->checkAvailability(
                    product: $product,
                    startTime: $cartItem->start_time,
                    endTime: $cartItem->end_time,
                    requestedQuantity: $cartItem->requested_quantity,
                );

                if (! $isAvailable) {
                    throw ValidationException::withMessages([
                        "items.{$cartItem->id}" => ['The selected time window does not have enough available units for this product.'],
                    ]);
                }

                Reservation::query()->create([
                    'user_id' => $request->user()->id,
                    'product_id' => $product->id,
                    'reservation_order_id' => $reservationOrder->id,
                    'start_time' => $cartItem->start_time,
                    'end_time' => $cartItem->end_time,
                    'status' => ReservationStatus::Pending,
                    'reserved_quantity' => $cartItem->requested_quantity,
                    'extra_wishes' => $cartItem->extra_wishes,
                ]);
            }

            // Reconcile touched products once per checkout order.
            $this->availabilityService->reconcileProducts($lockedProducts);

            $cart->items()->delete();

            return $reservationOrder->load('reservations.product');
        }, attempts: 5);

        Mail::to($request->user())->queue(new ReservationPendingReviewMail($reservationOrder));

        $admins = User::query()
            ->where('is_admin', true)
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin)->queue(new ReservationOrderSubmittedToAdminMail($reservationOrder));
        }

        if (! $request->expectsJson()) {
            return redirect()
                ->route('carts.index')
                ->with('status', 'Checkout completed successfully.');
        }

        return response()->json([
            'message' => 'Checkout completed successfully.',
            'reservation_order' => $reservationOrder,
        ], 201);
    }

    private function currentUserCart(User $user): Cart
    {
        return $user->cart()->firstOrCreate([]);
    }
}
