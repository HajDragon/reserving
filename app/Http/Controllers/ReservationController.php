<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Services\AvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(private readonly AvailabilityService $availabilityService) {}

    public function index(): View
    {
        $reservations = Reservation::query()
            ->with('product')
            ->where('user_id', auth()->id())
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('reservations.index', [
            'reservations' => $reservations,
        ]);
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $requestedQuantity = (int) ($validated['reserved_quantity'] ?? 1);

        $reservation = DB::transaction(function () use ($request, $validated, $requestedQuantity) {
            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $isAvailable = $this->availabilityService->checkAvailability(
                product: $product,
                startTime: $validated['start_time'],
                endTime: $validated['end_time'],
                requestedQuantity: $requestedQuantity,
            );

            if (! $isAvailable) {
                throw ValidationException::withMessages([
                    'reserved_quantity' => ['The selected time window does not have enough available units for this product.'],
                ]);
            }

            $reservation = Reservation::create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'status' => ReservationStatus::Reserved,
                'reserved_quantity' => $requestedQuantity,
            ]);

            $this->availabilityService->syncProductAvailability(
                $product,
                $validated['start_time'],
                $validated['end_time'],
            );

            return $reservation;
        }, attempts: 5);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'reservation' => $reservation,
        ], 201);
    }

    public function confirmReturned(Request $request, Reservation $reservation): JsonResponse|RedirectResponse
    {
        $updatedReservation = DB::transaction(function () use ($request, $reservation) {
            $lockedReservation = Reservation::query()
                ->whereKey($reservation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReservation->status !== ReservationStatus::Reserved) {
                throw ValidationException::withMessages([
                    'reservation' => ['Only reserved reservations can be confirmed as returned.'],
                ]);
            }

            $lockedProduct = Product::query()
                ->whereKey($lockedReservation->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedReservation->forceFill([
                'status' => ReservationStatus::Returned,
                'returned_at' => now(),
                'returned_by' => $request->user()->id,
            ])->save();

            $this->availabilityService->syncProductAvailability(
                product: $lockedProduct,
                startTime: $lockedReservation->start_time,
                endTime: $lockedReservation->end_time,
            );

            return $lockedReservation->fresh();
        }, attempts: 5);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Reservation return confirmed successfully.');
        }

        return response()->json([
            'message' => 'Reservation return confirmed successfully.',
            'reservation' => $updatedReservation,
        ]);
    }

    public function confirmOrderReturned(Request $request, ReservationOrder $reservationOrder): JsonResponse|RedirectResponse
    {
        $updatedOrder = DB::transaction(function () use ($request, $reservationOrder) {
            $lockedReservations = Reservation::query()
                ->where('reservation_order_id', $reservationOrder->id)
                ->lockForUpdate()
                ->get();

            if ($lockedReservations->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => ['This order has no reservations to confirm.'],
                ]);
            }

            foreach ($lockedReservations as $lockedReservation) {
                if ($lockedReservation->status !== ReservationStatus::Reserved) {
                    throw ValidationException::withMessages([
                        'order' => ['All reservations in this order must be reserved before confirmation.'],
                    ]);
                }
            }

            $lockedProducts = Product::query()
                ->whereIn('id', $lockedReservations->pluck('product_id')->unique()->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lockedReservations as $lockedReservation) {
                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Returned,
                    'returned_at' => now(),
                    'returned_by' => $request->user()->id,
                ])->save();

                $product = $lockedProducts->get($lockedReservation->product_id);

                if ($product instanceof Product) {
                    $this->availabilityService->syncProductAvailability(
                        product: $product,
                        startTime: $lockedReservation->start_time,
                        endTime: $lockedReservation->end_time,
                    );
                }
            }

            return $reservationOrder->fresh()->load('reservations');
        }, attempts: 5);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Reservation order return confirmed successfully.');
        }

        return response()->json([
            'message' => 'Reservation order return confirmed successfully.',
            'reservation_order' => $updatedOrder,
        ]);
    }
}
