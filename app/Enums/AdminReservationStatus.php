<?php

namespace App\Enums;

enum AdminReservationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Returned = 'returned';

    public function toReservationStatus(): ReservationStatus
    {
        return match ($this) {
            self::Pending => ReservationStatus::Pending,
            self::Approved => ReservationStatus::Reserved,
            self::Rejected => ReservationStatus::Cancelled,
            self::Returned => ReservationStatus::Returned,
        };
    }

    public static function fromReservationStatus(ReservationStatus $status): self
    {
        return match ($status) {
            ReservationStatus::Pending => self::Pending,
            ReservationStatus::Reserved => self::Approved,
            ReservationStatus::Cancelled => self::Rejected,
            ReservationStatus::Returned => self::Returned,
        };
    }
}
