<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('non admin user cannot access reserving dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('reserving.index'));

    $response->assertForbidden();
});

test('admin can access reserving dashboard and view reservation cards', function () {
    $admin = User::factory()->admin()->create();
    $reservingUser = User::factory()->create([
        'name' => 'Reserving User',
        'email' => 'reserving@example.com',
    ]);
    $product = Product::factory()->create([
        'name' => 'Filming Camera',
    ]);

    Reservation::factory()->create([
        'user_id' => $reservingUser->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 2,
        'extra_wishes' => 'Tripod needed',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('reserving.index'));

    $response
        ->assertOk()
        ->assertSeeText('Reserving Admin Dashboard')
        ->assertSeeText('Filming Camera')
        ->assertSeeText('Reserving User')
        ->assertSeeText('reserving@example.com')
        ->assertSeeText('Tripod needed')
        ->assertSeeText('2');
});

test('admin can filter reserving dashboard by status and date range', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $productA = Product::factory()->create(['name' => 'Status Match Product']);
    $productB = Product::factory()->create(['name' => 'Status Miss Product']);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $productA->id,
        'status' => ReservationStatus::Reserved,
        'start_time' => Carbon::parse('2026-04-20 10:00:00'),
        'end_time' => Carbon::parse('2026-04-21 10:00:00'),
    ]);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $productB->id,
        'status' => ReservationStatus::Returned,
        'start_time' => Carbon::parse('2026-04-15 10:00:00'),
        'end_time' => Carbon::parse('2026-04-16 10:00:00'),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('reserving.index', [
            'status' => ReservationStatus::Reserved->value,
            'start_from' => '2026-04-19',
            'start_to' => '2026-04-21',
        ]));

    $response
        ->assertOk()
        ->assertSeeText('Status Match Product')
        ->assertDontSeeText('Status Miss Product');
});

test('admin can filter reserving dashboard by start and return weekdays', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $mondayProduct = Product::factory()->create(['name' => 'Monday Start Product']);
    $tuesdayProduct = Product::factory()->create(['name' => 'Tuesday Start Product']);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $mondayProduct->id,
        'status' => ReservationStatus::Reserved,
        'start_time' => Carbon::parse('2026-04-20 10:00:00'), // Monday
        'end_time' => Carbon::parse('2026-04-24 10:00:00'), // Friday
    ]);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $tuesdayProduct->id,
        'status' => ReservationStatus::Reserved,
        'start_time' => Carbon::parse('2026-04-21 10:00:00'), // Tuesday
        'end_time' => Carbon::parse('2026-04-25 10:00:00'), // Saturday
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('reserving.index', [
            'start_weekday' => 1,
            'return_weekday' => 5,
        ]));

    $response
        ->assertOk()
        ->assertSeeText('Monday Start Product')
        ->assertDontSeeText('Tuesday Start Product');
});

test('calendar view uses weekday filters as well', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $matchingProduct = Product::factory()->create(['name' => 'Calendar Monday Product']);
    $nonMatchingProduct = Product::factory()->create(['name' => 'Calendar Tuesday Product']);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $matchingProduct->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
        'start_time' => Carbon::parse('2026-04-20 09:00:00'),
        'end_time' => Carbon::parse('2026-04-20 12:00:00'),
    ]);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $nonMatchingProduct->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
        'start_time' => Carbon::parse('2026-04-21 09:00:00'),
        'end_time' => Carbon::parse('2026-04-21 12:00:00'),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('reserving.index', [
            'view' => 'calendar',
            'month' => '2026-04',
            'start_weekday' => 1,
        ]));

    $response
        ->assertOk()
        ->assertSeeText('Calendar View')
        ->assertSeeText('April 2026')
        ->assertSeeText('Calendar Monday Product')
        ->assertDontSeeText('Calendar Tuesday Product');
});
