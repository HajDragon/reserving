<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\CarbonInterface;

class AvailabilityService
{
    public function checkAvailability(int $productId, CarbonInterface|string $startTime, CarbonInterface|string $endTime): bool
    {
        $hasOverlap = Reservation::query()
            ->where('product_id', $productId)
            ->whereIn('status', [ReservationStatus::Reserved, ReservationStatus::Pending])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        return ! $hasOverlap;
    }
}
