<?php

use App\Enums\ReservationStatus;

/*
 * Unit test: ReservationStatus state machine.
 * Validates every allowed and forbidden transition so that the
 * canTransitionTo() contract is locked down.
 */

test('pending can transition to reserved and cancelled', function () {
    // Assert
    expect(ReservationStatus::Pending->canTransitionTo(ReservationStatus::Reserved))->toBeTrue();
    expect(ReservationStatus::Pending->canTransitionTo(ReservationStatus::Cancelled))->toBeTrue();
});

test('pending cannot transition to any other status', function () {
    // Assert
    $forbidden = [
        ReservationStatus::Pending,
        ReservationStatus::RemovalRequest,
        ReservationStatus::StillWaitingForReturn,
        ReservationStatus::Returned,
    ];

    foreach ($forbidden as $status) {
        expect(ReservationStatus::Pending->canTransitionTo($status))->toBeFalse(
            "Pending should NOT transition to {$status->value}"
        );
    }
});

test('reserved can transition to still waiting, returned, cancelled, removal request', function () {
    // Assert
    $allowed = [
        ReservationStatus::StillWaitingForReturn,
        ReservationStatus::Returned,
        ReservationStatus::Cancelled,
        ReservationStatus::RemovalRequest,
    ];

    foreach ($allowed as $status) {
        expect(ReservationStatus::Reserved->canTransitionTo($status))->toBeTrue(
            "Reserved SHOULD transition to {$status->value}"
        );
    }
});

test('reserved cannot transition to pending', function () {
    // Assert
    expect(ReservationStatus::Reserved->canTransitionTo(ReservationStatus::Pending))->toBeFalse();
});

test('removal request can transition back to reserved or to cancelled', function () {
    // Assert
    expect(ReservationStatus::RemovalRequest->canTransitionTo(ReservationStatus::Reserved))->toBeTrue();
    expect(ReservationStatus::RemovalRequest->canTransitionTo(ReservationStatus::Cancelled))->toBeTrue();
});

test('still waiting for return can transition to returned or cancelled', function () {
    // Assert
    expect(ReservationStatus::StillWaitingForReturn->canTransitionTo(ReservationStatus::Returned))->toBeTrue();
    expect(ReservationStatus::StillWaitingForReturn->canTransitionTo(ReservationStatus::Cancelled))->toBeTrue();
});

test('returned is a terminal state — no transitions allowed', function () {
    // Assert
    foreach (ReservationStatus::cases() as $status) {
        expect(ReservationStatus::Returned->canTransitionTo($status))->toBeFalse(
            "Returned should NOT transition to {$status->value}"
        );
    }
});

test('cancelled is a terminal state — no transitions allowed', function () {
    // Assert
    foreach (ReservationStatus::cases() as $status) {
        expect(ReservationStatus::Cancelled->canTransitionTo($status))->toBeFalse(
            "Cancelled should NOT transition to {$status->value}"
        );
    }
});

test('each status returns a human-readable label', function () {
    // Assert
    expect(ReservationStatus::Pending->label())->toBe('Pending');
    expect(ReservationStatus::Reserved->label())->toBe('Reserved');
    expect(ReservationStatus::RemovalRequest->label())->toBe('Removal Request');
    expect(ReservationStatus::StillWaitingForReturn->label())->toBe('Still Waiting for Return');
    expect(ReservationStatus::Returned->label())->toBe('Returned');
    expect(ReservationStatus::Cancelled->label())->toBe('Cancelled');
});
