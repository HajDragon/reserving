<?php

use App\Enums\ReservationStatus;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\ReturnedReservationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

test('admin can confirm single reservation as returned', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create([
        'quantity' => 1,
        'is_active' => false,
    ]);

    $reservation = Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservations.confirm-returned', $reservation));

    $response
        ->assertOk()
        ->assertJsonPath('reservation.status', ReservationStatus::Returned->value);

    $returnedReservation = Reservation::withTrashed()->findOrFail($reservation->id);

    expect($returnedReservation->status)->toBe(ReservationStatus::Returned)
        ->and($returnedReservation->returned_at)->not->toBeNull()
        ->and($returnedReservation->returned_by)->toBe($admin->id)
        ->and($returnedReservation->trashed())->toBeTrue()
        ->and(ReturnedReservationLog::query()->where('reservation_id', $reservation->id)->exists())->toBeTrue()
        ->and($product->refresh()->is_active)->toBeTrue();
});

test('non admin cannot confirm single reservation as returned', function () {
    $user = User::factory()->create();

    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservations.confirm-returned', $reservation));

    $response->assertForbidden();
});

test('confirm single return fails for non reserved reservation', function () {
    $admin = User::factory()->admin()->create();

    $reservation = Reservation::factory()->returned()->create();

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservations.confirm-returned', $reservation));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('reservation');
});

test('admin can confirm all reservations in an order as returned', function () {
    $admin = User::factory()->admin()->create();
    $orderOwner = User::factory()->create();

    $productA = Product::factory()->create(['quantity' => 2, 'is_active' => false]);
    $productB = Product::factory()->create(['quantity' => 2, 'is_active' => false]);

    $reservationOrder = ReservationOrder::factory()->create([
        'user_id' => $orderOwner->id,
    ]);

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'product_id' => $productA->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'product_id' => $productB->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservation-orders.confirm-returned', $reservationOrder));

    $response
        ->assertOk()
        ->assertJsonPath('reservation_order.returned_count', 2);

    expect(Reservation::query()->where('reservation_order_id', $reservationOrder->id)->count())->toBe(0)
        ->and(Reservation::withTrashed()->where('reservation_order_id', $reservationOrder->id)->count())->toBe(2)
        ->and(ReturnedReservationLog::query()->where('reservation_order_id', $reservationOrder->id)->count())->toBe(2)
        ->and(ReservationOrder::query()->whereKey($reservationOrder->id)->exists())->toBeTrue()
        ->and($productA->refresh()->is_active)->toBeTrue()
        ->and($productB->refresh()->is_active)->toBeTrue();

    $historyResponse = $this
        ->actingAs($orderOwner)
        ->get(route('reservations.index'));

    $historyResponse
        ->assertOk()
        ->assertDontSeeText((string) $productA->name)
        ->assertDontSeeText((string) $productB->name);
});

test('marking full order returned re-enables product with quantity one', function () {
    $admin = User::factory()->admin()->create();
    $orderOwner = User::factory()->create();

    $product = Product::factory()->create([
        'quantity' => 1,
        'is_active' => false,
    ]);

    $reservationOrder = ReservationOrder::factory()->create([
        'user_id' => $orderOwner->id,
    ]);

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('reservation-orders.confirm-returned', $reservationOrder));

    $response
        ->assertRedirect()
        ->assertSessionHas('status', 'Reservation order return confirmed successfully.');

    expect($product->refresh()->is_active)->toBeTrue()
        ->and(ReservationOrder::query()->whereKey($reservationOrder->id)->exists())->toBeTrue()
        ->and(Reservation::query()->where('reservation_order_id', $reservationOrder->id)->exists())->toBeFalse()
        ->and(ReturnedReservationLog::query()->where('reservation_order_id', $reservationOrder->id)->exists())->toBeTrue();
});

test('non admin cannot confirm order return', function () {
    $user = User::factory()->create();
    $reservationOrder = ReservationOrder::factory()->create();

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservation-orders.confirm-returned', $reservationOrder));

    $response->assertForbidden();
});

test('confirm order return processes mixed statuses and archives the order', function () {
    $admin = User::factory()->admin()->create();
    $reservationOrder = ReservationOrder::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 2,
        'is_active' => false,
    ]);

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Returned,
        'returned_at' => now()->subHour(),
    ]);

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservation-orders.confirm-returned', $reservationOrder));

    $response
        ->assertOk()
        ->assertJsonPath('reservation_order.returned_count', 2);

    expect(Reservation::query()->where('reservation_order_id', $reservationOrder->id)->exists())->toBeFalse()
        ->and(ReservationOrder::query()->whereKey($reservationOrder->id)->exists())->toBeTrue()
        ->and(ReturnedReservationLog::query()->where('reservation_order_id', $reservationOrder->id)->count())->toBe(2);
});

test('admin can update reservation status from dashboard flow', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $reservation = Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Pending,
    ]);

    $response = $this
        ->actingAs($admin)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => 'rejected',
            'rejection_reason' => 'Item currently unavailable for selected period.',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('reservation.status', ReservationStatus::Cancelled->value);

    expect($reservation->refresh()->status)->toBe(ReservationStatus::Cancelled)
        ->and($reservation->reviewed_by)->toBe($admin->id)
        ->and($reservation->rejection_reason)->not->toBeNull();

    Mail::assertQueued(ReservationRejectedMail::class, 1);
});

test('admin can approve pending reservation from web form and edit reservation details', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['quantity' => 5]);
    $start = now()->addDays(2)->startOfHour();
    $end = $start->copy()->addHours(2);

    $reservation = Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Pending,
        'start_time' => $start,
        'end_time' => $end,
        'reserved_quantity' => 1,
    ]);

    $nextStart = $start->copy()->addDay();
    $nextEnd = $nextStart->copy()->addHours(3);

    $response = $this
        ->actingAs($admin)
        ->patch(route('reservations.update-status', $reservation), [
            'status' => 'approved',
            'start_time' => $nextStart->toDateTimeString(),
            'end_time' => $nextEnd->toDateTimeString(),
            'reserved_quantity' => 2,
            'extra_wishes' => 'Updated by admin',
        ]);

    $response
        ->assertRedirect()
        ->assertSessionHas('status', 'Reservation status updated successfully.');

    expect($reservation->refresh()->status)->toBe(ReservationStatus::Reserved)
        ->and($reservation->reviewed_by)->toBe($admin->id)
        ->and($reservation->reserved_quantity)->toBe(2)
        ->and($reservation->extra_wishes)->toBe('Updated by admin');

    Mail::assertQueued(ReservationApprovedMail::class, 1);
});

test('status update rejects unknown status value', function () {
    $admin = User::factory()->admin()->create();
    $reservation = Reservation::factory()->returned()->create();

    $response = $this
        ->actingAs($admin)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => 'invalid-status',
        ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

test('confirming return keeps cached product inventory aligned with active reservations', function () {
    $admin = User::factory()->admin()->create();

    $product = Product::factory()->create([
        'quantity' => 3,
        'available_quantity' => 3,
        'is_active' => true,
    ]);

    $reservation = Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 2,
    ]);

    $this->actingAs($admin)
        ->postJson(route('reservations.confirm-returned', $reservation))
        ->assertOk();

    $activeReservedQuantity = Reservation::query()
        ->where('product_id', $product->id)
        ->whereIn('status', [ReservationStatus::Reserved->value, ReservationStatus::Pending->value])
        ->sum('reserved_quantity');

    $refreshedProduct = $product->refresh();
    $expectedAvailableQuantity = max($refreshedProduct->quantity - (int) $activeReservedQuantity, 0);

    expect($refreshedProduct->available_quantity)->toBe($expectedAvailableQuantity)
        ->and($refreshedProduct->is_active)->toBe($expectedAvailableQuantity > 0);
});
