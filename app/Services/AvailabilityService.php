<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use Carbon\CarbonInterface;

class AvailabilityService
{
    public function remainingCapacity(Product $product, CarbonInterface|string $startTime, CarbonInterface|string $endTime): int
    {
        $reservedQuantity = Reservation::query()
            ->where('product_id', $product->id)
            ->whereIn('status', [ReservationStatus::Reserved->value, ReservationStatus::Pending->value])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->sum('reserved_quantity');

        return max($product->quantity - (int) $reservedQuantity, 0);
    }

    public function checkAvailability(Product $product, CarbonInterface|string $startTime, CarbonInterface|string $endTime, int $requestedQuantity = 1): bool
    {
        if ($requestedQuantity < 1) {
            return false;
        }

        return $requestedQuantity <= $this->remainingCapacity($product, $startTime, $endTime);
    }

    public function syncProductAvailability(Product $product, CarbonInterface|string $startTime, CarbonInterface|string $endTime): void
    {
        $product->forceFill([
            'is_active' => $this->remainingCapacity($product, $startTime, $endTime) > 0,
        ])->save();
    }
}
