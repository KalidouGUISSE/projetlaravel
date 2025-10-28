<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UnarchiveBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Récupérer les comptes archivés (soft deleted) bloqués dont la date de fin de blocage est échue
        $comptesToUnarchive = Compte::onlyTrashed()
            ->where('statut', 'bloque')
            ->whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<=', now())
            ->get();

        foreach ($comptesToUnarchive as $compte) {
            // Restaurer le compte (unarchive)
            $compte->restore();

            // Log l'action
            Log::info("Compte désarchivé automatiquement: {$compte->numeroCompte}");
        }

        Log::info("Job UnarchiveBlockedAccounts exécuté: {$comptesToUnarchive->count()} comptes désarchivés");
    }
}
