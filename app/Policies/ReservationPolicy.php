<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id || (bool) $user->is_admin;
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id
            && $reservation->status === ReservationStatus::Pending;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id
            && $reservation->status === ReservationStatus::Pending;
    }

    public function requestRemoval(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id;
    }

    public function confirmReturn(User $user, Reservation $reservation): bool
    {
        return (bool) $user->is_admin;
    }
}
