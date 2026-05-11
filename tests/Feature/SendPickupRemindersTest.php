<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Mail\PickupReminderMail;
use App\Models\Reservation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendPickupRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_reminders_and_marks_sent()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $tomorrow = CarbonImmutable::now()->addDay()->startOfDay()->addHour();

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'start_time' => $tomorrow->toDateTimeString(),
            'status' => ReservationStatus::Reserved->value,
        ]);

        $this->assertNull($reservation->reminder_sent_at);

        Artisan::call('send:pickup-reminders');

        Mail::assertQueued(PickupReminderMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });

        $reservation->refresh();
        $this->assertNotNull($reservation->reminder_sent_at);
    }

    public function test_dry_run_does_not_mark_sent()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'dry@example.com']);

        $tomorrow = CarbonImmutable::now()->addDay()->startOfDay()->addHour();

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'start_time' => $tomorrow->toDateTimeString(),
            'status' => ReservationStatus::Reserved->value,
        ]);

        Artisan::call('send:pickup-reminders', ['--dry-run' => true]);

        Mail::assertNothingQueued();

        $reservation->refresh();
        $this->assertNull($reservation->reminder_sent_at);
    }
}
