<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DebloquerComptesExpires implements ShouldQueue
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
        // Récupérer les comptes bloqués dont la date de fin de blocage est dépassée
        $comptesToUnblock = Compte::where('statut', 'bloque')
            ->whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<=', now())
            ->get();

        foreach ($comptesToUnblock as $compte) {
            // Débloquer le compte automatiquement
            $compte->update([
                'statut' => 'actif',
                'motifBlocage' => null,
                'date_debut_blocage' => null,
                'date_fin_blocage' => null,
            ]);

            // Mettre à jour les metadata
            $metadata = $compte->metadata ?? [];
            $metadata['derniereModification'] = now();
            $metadata['version'] = ($metadata['version'] ?? 1) + 1;
            $metadata['deblocageAutomatique'] = true;
            $metadata['dateDeblocageAutomatique'] = now();
            $compte->metadata = $metadata;
            $compte->save();

            // Log l'action
            Log::info("Compte débloqué automatiquement: {$compte->numeroCompte}");
        }

        Log::info("Job DebloquerComptesExpires exécuté: {$comptesToUnblock->count()} comptes débloqués");
    }
}
