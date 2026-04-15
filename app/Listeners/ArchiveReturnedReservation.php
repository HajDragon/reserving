<?php

namespace App\Listeners;

use App\Actions\Reservations\AdjustProductInventoryAction;
use App\Events\ReservationReturned;
use App\Models\ReturnedReservationLog;

class ArchiveReturnedReservation
{
    public function __construct(private readonly AdjustProductInventoryAction $inventoryAction) {}

    /**
     * Handle the event.
     */
    public function handle(ReservationReturned $event): void
    {
        $reservation = $event->reservation;

        ReturnedReservationLog::query()->create([
            'reservation_id' => $reservation->id,
            'reservation_order_id' => $reservation->reservation_order_id,
            'product_id' => $reservation->product_id,
            'user_id' => $reservation->user_id,
            'product_name' => $reservation->product?->name ?? 'N/A',
            'quantity' => $reservation->reserved_quantity,
            'returned_at' => $reservation->returned_at ?? now(),
            'reservation_start_time' => $reservation->start_time,
            'reservation_end_time' => $reservation->end_time,
            'extra_wishes' => $reservation->extra_wishes,
        ]);

        $this->inventoryAction->restoreForReservation($reservation);

        $reservation->delete();
    }
}
