<?php

namespace App\Console\Commands;

use App\Mail\PickupReminderMail;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPickupReminders extends Command
{
    protected $signature = 'send:pickup-reminders {--dry-run} {--time=08:00}';

    protected $description = 'Send reminder emails for reservations with pickup tomorrow.';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $time = $this->option('time') ?? config('reservations.reminder_time', '08:00');

        $this->info('Finding reservations for pickup tomorrow...');

        $tz = config('app.timezone');
        $tomorrow = CarbonImmutable::now($tz)->addDay()->startOfDay();
        $dateString = $tomorrow->toDateString();

        $statuses = config('reservations.reminder_statuses', ['Reserved']);

        $query = Reservation::query()
            ->with(['user'])
            ->whereDate('start_time', $dateString)
            ->whereIn('status', $statuses)
            ->whereNull('reminder_sent_at');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No reservations found for tomorrow.');

            return 0;
        }

        $this->info("Found {$count} reservations. Sending reminders...");

        $sent = 0;
        $failed = 0;

        foreach ($query->cursor() as $reservation) {
            try {
                if ($dry) {
                    $this->line("[dry] Would send reminder to: {$reservation->user->email}");
                } else {
                    Mail::to($reservation->user)->queue(new PickupReminderMail($reservation));
                    $reservation->reminder_sent_at = now();
                    $reservation->save();
                    $sent++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Failed sending pickup reminder', ['id' => $reservation->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Done. Sent: {$sent}, Failed: {$failed}");

        return 0;
    }
}
