<?php

use App\Enums\ReservationStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

test('two users cannot both reserve the last item (second checkout is rejected)', function () {
    // ponytail: in-memory SQLite serializes writes, so true row-level contention is
    // exercised by the production MySQL lockForUpdate(); this test pins the business
    // outcome (one succeeds, one fails) that the lock guarantees.
    $product = Product::factory()->create([
        'quantity' => 1,
        'available_quantity' => 1,
    ]);

    $start = Carbon::now()->addDays(2)->startOfHour();
    $end = (clone $start)->addHours(4);

    $first = User::factory()->create();
    $firstCart = $first->cart()->create();
    CartItem::factory()->create([
        'cart_id' => $firstCart->id,
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => $end,
        'requested_quantity' => 1,
    ]);

    $second = User::factory()->create();
    $secondCart = $second->cart()->create();
    $secondItem = CartItem::factory()->create([
        'cart_id' => $secondCart->id,
        'product_id' => $product->id,
        'start_time' => (clone $start)->addHour(),
        'end_time' => (clone $end)->addHour(),
        'requested_quantity' => 1,
    ]);

    // First user checks out and takes the only unit.
    $firstResponse = actingAs($first)->postJson(route('carts.checkout'));
    $firstResponse->assertCreated();
    expect(Reservation::query()->where('user_id', $first->id)->count())->toBe(1);

    // Second user requests an overlapping window: only failure is acceptable.
    $secondResponse = actingAs($second)->postJson(route('carts.checkout'));

    $secondResponse
        ->assertUnprocessable()
        ->assertJsonValidationErrors("items.{$secondItem->id}");

    expect(Reservation::query()->where('user_id', $second->id)->count())->toBe(0)
        ->and(Reservation::query()->count())->toBe(1)
        // The losing cart keeps its items so the user can pick another slot.
        ->and($secondCart->refresh()->items()->count())->toBe(1)
        // The successful order is intact and untouched by the failure.
        ->and(ReservationOrder::query()->count())->toBe(1);
});

test('reservation store enforces date boundaries and normalizes timezone input', function () {
    $user = User::factory()->create();
    // ponytail: 4 units so the observer's per-reservation deduction (factory + boundary 2)
    // never starves boundary 3; this test exercises date/timezone rules, not inventory.
    $product = Product::factory()->create([
        'quantity' => 4,
        'available_quantity' => 4,
    ]);

    // Boundary 1: start_time exactly "now" is rejected (after:now is strict).
    $now = Carbon::now();
    actingAs($user)
        ->postJson(route('reservations.store'), [
            'product_id' => $product->id,
            'start_time' => $now->toDateTimeString(),
            'end_time' => (clone $now)->addHour()->toDateTimeString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('start_time');
    expect(Reservation::count())->toBe(0);

    // Boundary 2: back-to-back reservations (end == next start) do NOT overlap.
    $dayStart = Carbon::now()->addDay()->startOfHour();
    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'start_time' => $dayStart,
        'end_time' => (clone $dayStart)->addHours(2),
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    actingAs($user)
        ->postJson(route('reservations.store'), [
            'product_id' => $product->id,
            'start_time' => (clone $dayStart)->addHours(2)->toDateTimeString(),
            'end_time' => (clone $dayStart)->addHours(4)->toDateTimeString(),
        ])
        ->assertCreated();
    expect(Reservation::count())->toBe(2);

    // Boundary 3: a +02:00 timezone-offset input is normalized to the app timezone.
    $offsetStart = Carbon::now()->addDays(3)->startOfHour()->tz('Europe/Amsterdam');
    $offsetEnd = (clone $offsetStart)->addHour();

    actingAs($user)
        ->postJson(route('reservations.store'), [
            'product_id' => $product->id,
            'start_time' => $offsetStart->toIso8601String(),
            'end_time' => $offsetEnd->toIso8601String(),
        ])
        ->assertCreated();

    $stored = Reservation::query()->latest('id')->first();
    expect(Carbon::parse($stored->start_time)->equalTo($offsetStart->utc()))->toBeTrue()
        ->and(Carbon::parse($stored->end_time)->equalTo($offsetEnd->utc()))->toBeTrue();
});
