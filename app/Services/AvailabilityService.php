<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\CarbonInterface;

class AvailabilityService
{
    public function checkAvailability(int $productId, CarbonInterface|string $startTime, CarbonInterface|string $endTime): bool
    {
        $hasOverlap = Reservation::query()
            ->where('product_id', $productId)
            ->whereIn('status', ['confirmed', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        return ! $hasOverlap;
    }
}
