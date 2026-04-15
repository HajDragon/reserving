<?php

use App\Enums\ReservationStatus;
use App\Models\ReservationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('non admin cannot access reservation logs panel', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('cms.reservation-logs.index'));

    $response->assertForbidden();
});

test('admin can search returned logs by product name', function () {
    $admin = User::factory()->admin()->create();

    ReservationLog::factory()->create([
        'product_name' => 'Sony Lens Kit',
        'status' => ReservationStatus::Returned,
    ]);

    ReservationLog::factory()->create([
        'product_name' => 'Tripod Pro',
        'status' => ReservationStatus::Returned,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('cms.reservation-logs.index', [
            'search' => 'Sony',
        ]));

    $response
        ->assertOk()
        ->assertSeeText('Sony Lens Kit')
        ->assertDontSeeText('Tripod Pro');
});

test('admin can filter returned logs by weekdays', function () {
    $admin = User::factory()->admin()->create();

    ReservationLog::factory()->create([
        'product_name' => 'Monday Return Device',
        'start_time' => Carbon::parse('2026-04-20 09:00:00'),
        'end_time' => Carbon::parse('2026-04-24 10:00:00'),
        'status' => ReservationStatus::Returned,
    ]);

    ReservationLog::factory()->create([
        'product_name' => 'Tuesday Return Device',
        'start_time' => Carbon::parse('2026-04-21 09:00:00'),
        'end_time' => Carbon::parse('2026-04-25 10:00:00'),
        'status' => ReservationStatus::Returned,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('cms.reservation-logs.index', [
            'start_weekday' => 1,
            'return_weekday' => 5,
        ]));

    $response
        ->assertOk()
        ->assertSeeText('Monday Return Device')
        ->assertDontSeeText('Tuesday Return Device');
});
