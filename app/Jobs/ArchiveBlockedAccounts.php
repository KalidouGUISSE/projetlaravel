<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiveBlockedAccounts implements ShouldQueue
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
        // Récupérer les comptes bloqués dont la date de début de blocage est échue
        $comptesToArchive = Compte::where('statut', 'bloque')
            ->whereNotNull('date_debut_blocage')
            ->where('date_debut_blocage', '<=', now())
            ->get();

        foreach ($comptesToArchive as $compte) {
            // Archiver le compte (soft delete)
            $compte->delete();

            // Log l'action
            Log::info("Compte archivé automatiquement: {$compte->numeroCompte}");
        }

        Log::info("Job ArchiveBlockedAccounts exécuté: {$comptesToArchive->count()} comptes archivés");
    }
}
