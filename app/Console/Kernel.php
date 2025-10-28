<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Archiver les comptes bloqués dont la date de début de blocage est échue (toutes les heures)
        $schedule->job(new \App\Jobs\ArchiveBlockedAccounts)->hourly();

        // Désarchiver les comptes bloqués dont la date de fin de blocage est échue (toutes les heures)
        $schedule->job(new \App\Jobs\UnarchiveBlockedAccounts)->hourly();

        // Débloquer automatiquement les comptes dont la période de blocage est expirée (toutes les heures)
        $schedule->job(new \App\Jobs\DebloquerComptesExpires)->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
