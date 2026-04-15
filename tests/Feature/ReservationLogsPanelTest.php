<?php

use App\Models\ReturnedReservationLog;
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

    ReturnedReservationLog::factory()->create([
        'product_name' => 'Sony Lens Kit',
        'returned_at' => Carbon::parse('2026-04-15 09:00:00'),
    ]);

    ReturnedReservationLog::factory()->create([
        'product_name' => 'Tripod Pro',
        'returned_at' => Carbon::parse('2026-04-16 09:00:00'),
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

test('admin can filter returned logs by return weekday', function () {
    $admin = User::factory()->admin()->create();

    ReturnedReservationLog::factory()->create([
        'product_name' => 'Monday Return Device',
        'returned_at' => Carbon::parse('2026-04-20 10:00:00'),
    ]);

    ReturnedReservationLog::factory()->create([
        'product_name' => 'Tuesday Return Device',
        'returned_at' => Carbon::parse('2026-04-21 10:00:00'),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('cms.reservation-logs.index', [
            'returned_weekday' => 1,
        ]));

    $response
        ->assertOk()
        ->assertSeeText('Monday Return Device')
        ->assertDontSeeText('Tuesday Return Device');
});
