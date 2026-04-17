<?php

namespace App\Actions\Reservations;

use App\Models\Product;
use App\Models\Reservation;

class AdjustProductInventoryAction
{
    public function deductForReservation(Reservation $reservation): void
    {
        if ($reservation->reserved_quantity <= 0) {
            return;
        }

        $product = Product::query()
            ->whereKey($reservation->product_id)
            ->lockForUpdate()
            ->first();

        if (! $product instanceof Product) {
            return;
        }

        $nextAvailableQuantity = max($product->available_quantity - $reservation->reserved_quantity, 0);

        $product->forceFill([
            'available_quantity' => $nextAvailableQuantity,
            'is_active' => $nextAvailableQuantity > 0,
        ])->save();
    }

    public function restoreForReservation(Reservation $reservation): void
    {
        if ($reservation->reserved_quantity <= 0) {
            return;
        }

        $product = Product::query()
            ->whereKey($reservation->product_id)
            ->lockForUpdate()
            ->first();

        if (! $product instanceof Product) {
            return;
        }

        $restoredAvailableQuantity = min($product->available_quantity + $reservation->reserved_quantity, $product->quantity);

        $product->forceFill([
            'available_quantity' => $restoredAvailableQuantity,
            'is_active' => $restoredAvailableQuantity > 0,
        ])->save();
    }
}
