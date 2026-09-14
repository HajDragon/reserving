<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
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
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 5, 'available_quantity' => 5]);

    // A reservation that goes through: reserved -> removal_request -> (approved) -> cancelled
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
        'start_time' => Carbon::now()->addDay()->startOfHour(),
        'end_time' => Carbon::now()->addDay()->startOfHour()->addHours(2),
    ]);

    // Assert
    // Invariant: no status removal_request <=> no open request in the table.
    expect($reservation->status)->not->toBe(ReservationStatus::RemovalRequest)
        ->and(ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->count())->toBe(0);

    // Act
    // User submits a removal request -> reservation becomes removal_request AND a request row exists.
    actingAs($user)
        ->postJson(route('reservations.request-removal', $reservation), ['reason' => 'Not needed anymore']);

    // Assert
    $reservation->refresh();
    expect($reservation->status)->toBe(ReservationStatus::RemovalRequest)
        ->and(ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->where('status', 'removal_request')->count())->toBe(1);

    // Invariant direction 2: status IS removal_request <=> exactly one open request exists.
    $openRequests = ReservationRemovalRequest::query()
        ->where('reservation_id', $reservation->id)
        ->where('status', 'removal_request')
        ->count();
    expect($openRequests)->toBe(1);
});

test('removal request cannot be submitted twice for the same reservation (invariant guard)', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 5, 'available_quantity' => 5]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    // Act
    // First request succeeds.
    actingAs($user)
        ->postJson(route('reservations.request-removal', $reservation), ['reason' => 'First'])
    // Assert
        ->assertCreated();

    // Act
    // Second request must fail — reservation is no longer Reserved (status guard enforces the invariant).
    actingAs($user)
        ->postJson(route('reservations.request-removal', $reservation), ['reason' => 'Second'])
    // Assert
        ->assertUnprocessable();

    expect(ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->count())->toBe(1);
});

test('admin approving the request closes the invariant loop (status leaves removal_request, request is reviewed)', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 5, 'available_quantity' => 5]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    // Act
    actingAs($user)->postJson(route('reservations.request-removal', $reservation), ['reason' => 'Not needed']);

    // Arrange
    $removalRequest = ReservationRemovalRequest::query()->where('reservation_id', $reservation->id)->firstOrFail();
    $admin = User::factory()->admin()->create();

    // Act
    actingAs($admin)
        ->patch(route('reservation-removal-requests.update-status', $removalRequest), [
            'status' => 'approved',
        ])
    // Assert
        ->assertRedirect();

    $reservation->refresh();
    $removalRequest->refresh();

    // After approval: reservation left removal_request (now cancelled), request is reviewed.
    expect($reservation->status)->toBe(ReservationStatus::Cancelled)
        ->and($removalRequest->status)->toBe('approved')
        // Invariant holds: no reservation sits in removal_request without an OPEN (unreviewed) request row.
        ->and(ReservationRemovalRequest::query()->where('status', 'removal_request')->count())->toBe(0);
});
