<?php

namespace App\Observers;

use App\Actions\Reservations\AdjustProductInventoryAction;
use App\Models\Reservation;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        app(AdjustProductInventoryAction::class)->deductForReservation($reservation);
    }
}
