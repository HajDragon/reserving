<?php

use App\Enums\AdminReservationStatus;
use App\Enums\ReservationStatus;

/*
 * Unit test: AdminReservationStatus ↔ ReservationStatus mapping.
 * The admin panel uses its own status labels; this test ensures
 * both directions of the mapping stay in sync.
 */

test('toReservationStatus maps every admin status correctly', function () {
    expect(AdminReservationStatus::Pending->toReservationStatus())->toBe(ReservationStatus::Pending);
    expect(AdminReservationStatus::Approved->toReservationStatus())->toBe(ReservationStatus::Reserved);
    expect(AdminReservationStatus::StillWaitingForReturn->toReservationStatus())->toBe(ReservationStatus::StillWaitingForReturn);
    expect(AdminReservationStatus::Rejected->toReservationStatus())->toBe(ReservationStatus::Cancelled);
    expect(AdminReservationStatus::Returned->toReservationStatus())->toBe(ReservationStatus::Returned);
});

test('fromReservationStatus maps every reservation status correctly', function () {
    expect(AdminReservationStatus::fromReservationStatus(ReservationStatus::Pending))->toBe(AdminReservationStatus::Pending);
    expect(AdminReservationStatus::fromReservationStatus(ReservationStatus::Reserved))->toBe(AdminReservationStatus::Approved);
    expect(AdminReservationStatus::fromReservationStatus(ReservationStatus::StillWaitingForReturn))->toBe(AdminReservationStatus::StillWaitingForReturn);
    expect(AdminReservationStatus::fromReservationStatus(ReservationStatus::Cancelled))->toBe(AdminReservationStatus::Rejected);
    expect(AdminReservationStatus::fromReservationStatus(ReservationStatus::Returned))->toBe(AdminReservationStatus::Returned);
});

test('mapping is bidirectional — round-trip preserves identity for applicable statuses', function () {
    $adminStatuses = [
        AdminReservationStatus::Pending,
        AdminReservationStatus::Approved,
        AdminReservationStatus::StillWaitingForReturn,
        AdminReservationStatus::Rejected,
        AdminReservationStatus::Returned,
    ];

    foreach ($adminStatuses as $adminStatus) {
        $reservationStatus = $adminStatus->toReservationStatus();
        $roundTrip = AdminReservationStatus::fromReservationStatus($reservationStatus);

        expect($roundTrip)->toBe($adminStatus);
    }
});
