<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Reserved = 'reserved';
    case StillWaitingForReturn = 'still_waiting_for_return';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Reserved => 'Reserved',
            self::StillWaitingForReturn => 'Still Waiting for Return',
            self::Returned => 'Returned',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Check if the status is a valid transition from current status.
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Pending => in_array($newStatus, [self::Reserved, self::Cancelled]),
            self::Reserved => in_array($newStatus, [self::StillWaitingForReturn, self::Returned, self::Cancelled]),
            self::StillWaitingForReturn => in_array($newStatus, [self::Returned, self::Cancelled]),
            self::Returned => false, // Terminal state
            self::Cancelled => false, // Terminal state
        };
    }
}
