<?php

use App\Models\Reservation;
use App\Models\ReservationRemovalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

test('removal request invariant holds for every reservation in the system', function () {
    $user = User::factory()->create();
    $product = \App\Models\Product::factory()->create(['quantity' => 5, 'available_quantity' => 5]);

    // A reservation that goes through: reserved -> removal_request -> (approved) -> cancelled
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => \App\Enums\ReservationStatus::Reserved,
        'reserved_quantity' => 1,
        'start_time' => Carbon::now()->addDay()->startOfHour(),
        'end_time' => Carbon::now()->addDay()->startOfHour()->addHours(2),
    ]);

    // Invariant: no status removal_request <=> no open request in the table.
    expect($reservation->status)->not->toBe(\App\Enums\ReservationStatus::RemovalRequest)
        ->and(ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->count())->toBe(0);

    // User submits a removal request → reservation becomes removal_request AND a request row exists.
    actingAs($user)
        ->postJson(route('reservations.request-removal', $reservation), ['reason' => 'Not needed anymore']);

    $reservation->refresh();
    expect($reservation->status)->toBe(\App\Enums\ReservationStatus::RemovalRequest)
        ->and(ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->where('status', 'removal_request')->count())->toBe(1);

    // Invariant direction 2: status IS removal_request <=> exactly one open request exists.
    $openRequests = ReservationRemovalRequest::query()
        ->where('reservation_id', $reservation->id)
        ->where('status', 'removal_request')
        ->count();
    expect($openRequests)->toBe(1);
});

test('removal request cannot be submitted twice for the same reservation (invariant guard)', function () {
    $user = User::factory()->create();
    $product = \App\Models\Product::factory()->create(['quantity' => 5, 'available_quantity' => 5]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => \App\Enums\ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    // First request succeeds.
    actingAs($user)
        ->postJson(route('reservations.request-removal', $reservation), ['reason' => 'First'])
        ->assertCreated();

    // Second request must fail — reservation is no longer Reserved (status guard enforces the invariant).
    actingAs($user)
        ->postJson(route('reservations.request-removal', $reservation), ['reason' => 'Second'])
        ->assertUnprocessable();

    expect(ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->count())->toBe(1);
});

test('admin approving the request closes the invariant loop (status leaves removal_request, request is reviewed)', function () {
    $user = User::factory()->create();
    $product = \App\Models\Product::factory()->create(['quantity' => 5, 'available_quantity' => 5]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => \App\Enums\ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    actingAs($user)->postJson(route('reservations.request-removal', $reservation), ['reason' => 'Not needed']);

    $removalRequest = ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->firstOrFail();
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->patch(route('reservation-removal-requests.update-status', $removalRequest), [
            'status' => 'approved',
        ])
        ->assertRedirect();

    $reservation->refresh();
    $removalRequest->refresh();

    // After approval: reservation left removal_request (now cancelled), request is reviewed.
    expect($reservation->status)->toBe(\App\Enums\ReservationStatus::Cancelled)
        ->and($removalRequest->status)->toBe('approved')
        // Invariant holds: no reservation sits in removal_request without an OPEN (unreviewed) request row.
        ->and(ReservationRemovalRequest::query()->where('status', 'removal_request')->count())->toBe(0);
});
