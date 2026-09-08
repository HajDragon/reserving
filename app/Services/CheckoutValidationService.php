<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Validates cart items against locked product state and creates the
 * reservations for a checkout, inside the caller's transaction.
 */
class CheckoutValidationService
{
    public function __construct(private readonly AvailabilityService $availabilityService) {}

    /**
     * Validate every cart item against the locked products and create a
     * pending reservation per item.
     *
     * @param  iterable<CartItem>  $cartItems
     * @param  Collection<int, Product>  $lockedProducts  Products keyed by id, selected with lockForUpdate.
     */
    public function validateAndCreateReservations(
        iterable $cartItems,
        Collection $lockedProducts,
        User $user,
        ReservationOrder $reservationOrder,
    ): void {
        foreach ($cartItems as $cartItem) {
            $product = $lockedProducts->get($cartItem->product_id);

            $this->validateCartItem($cartItem, $product);

            Reservation::query()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'reservation_order_id' => $reservationOrder->id,
                'start_time' => $cartItem->start_time,
                'end_time' => $cartItem->end_time,
                'status' => ReservationStatus::Pending,
                'reserved_quantity' => $cartItem->requested_quantity,
                'extra_wishes' => $cartItem->extra_wishes,
            ]);
        }
    }

    private function validateCartItem(CartItem $cartItem, ?Product $product): void
    {
        if (! $product instanceof Product) {
            $this->fail($cartItem, 'A selected product is no longer available.');
        }

        if ($cartItem->requested_quantity > $product->quantity) {
            $this->fail($cartItem, 'The requested quantity exceeds available product quantity.');
        }

        if ($cartItem->requested_quantity > $product->available_quantity) {
            $this->fail($cartItem, 'The requested quantity exceeds current available inventory.');
        }

        $isAvailable = $this->availabilityService->checkAvailability(
            product: $product,
            startTime: $cartItem->start_time,
            endTime: $cartItem->end_time,
            requestedQuantity: $cartItem->requested_quantity,
        );

        if (! $isAvailable) {
            $this->fail($cartItem, 'The selected time window does not have enough available units for this product.');
        }
    }

    private function fail(CartItem $cartItem, string $message): never
    {
        throw ValidationException::withMessages([
            "items.{$cartItem->id}" => [$message],
        ]);
    }
}
