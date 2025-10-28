<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BloquerComptesProgrammes implements ShouldQueue
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
        // Récupérer les comptes actifs avec une date de début de blocage arrivée
        $comptesToBlock = Compte::where('statut', 'actif')
            ->where('type', 'epargne')
            ->whereNotNull('date_debut_blocage')
            ->where('date_debut_blocage', '<=', now())
            ->get();

        foreach ($comptesToBlock as $compte) {
            // Vérifier que le compte n'est pas déjà bloqué
            if ($compte->statut === 'bloque') {
                Log::warning("Tentative de blocage d'un compte déjà bloqué: {$compte->numeroCompte}");
                continue;
            }

            // Bloquer le compte automatiquement
            $compte->update([
                'statut' => 'bloque',
            ]);

            // Mettre à jour les metadata
            $metadata = $compte->metadata ?? [];
            $metadata['derniereModification'] = now();
            $metadata['version'] = ($metadata['version'] ?? 1) + 1;
            $metadata['blocageAutomatique'] = true;
            $metadata['dateBlocageAutomatique'] = now();
            $compte->metadata = $metadata;
            $compte->save();

            // Log l'action
            Log::info("Compte bloqué automatiquement (programmé): {$compte->numeroCompte}");
        }

        Log::info("Job BloquerComptesProgrammes exécuté: {$comptesToBlock->count()} comptes bloqués");
    }
}
