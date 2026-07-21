<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:cleanup-old-data';

    /**
     * The console command description.
     * Cleans up old session data and expired reservation logs per GDPR data retention policy.
     */
    protected $description = 'Verwijder oude sessies en verlopen reserveringslogs (AVG dataminimalisatie)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $beforeSession = now()->subMinutes(config('session.lifetime', 120));
        $beforeLogs = Carbon::now()->subYears(2); // 2 year retention for logs

        // Clean old sessions (GDPR data minimization)
        $deletedSessions = DB::table('sessions')
            ->where('last_activity', '<', $beforeSession->timestamp)
            ->delete();

        $this->info("Oude sessies verwijderd: {$deletedSessions}");

        // Clean expired reservation logs (keep 2 years)
        $deletedLogs = 0;
        if (DB::getSchemaBuilder()->hasTable('reservation_logs')) {
            $deletedLogs = DB::table('reservation_logs')
                ->where('created_at', '<', $beforeLogs)
                ->delete();
        }
        $this->info("Oude reservation logs verwijderd (>2 jaar): {$deletedLogs}");

        $this->info("Dataverwijdering voltooid — AVG data-retentiebeleid uitgevoerd.");
        return Command::SUCCESS;
    }
}
