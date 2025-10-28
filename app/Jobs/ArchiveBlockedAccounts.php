<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
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
            // Copier le compte vers la base d'archive (Neon)
            $this->archiveToNeon($compte);

            // Archiver le compte (soft delete)
            $compte->delete();

            // Log l'action
            Log::info("Compte archivé automatiquement: {$compte->numeroCompte}");
        }

        Log::info("Job ArchiveBlockedAccounts exécuté: {$comptesToArchive->count()} comptes archivés");
    }

    /**
     * Archive le compte vers la base Neon
     */
    private function archiveToNeon(Compte $compte): void
    {
        try {
            DB::connection('archive')->table('comptes')->insert([
                'id' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'client_id' => $compte->client_id,
                'type' => $compte->type,
                'solde' => $compte->solde,
                'statut' => $compte->statut,
                'metadata' => json_encode($compte->metadata),
                'motifBlocage' => $compte->motifBlocage,
                'date_debut_blocage' => $compte->date_debut_blocage,
                'date_fin_blocage' => $compte->date_fin_blocage,
                'created_at' => $compte->created_at,
                'updated_at' => $compte->updated_at,
                'deleted_at' => now(),
            ]);

            Log::info("Compte copié vers archive: {$compte->numeroCompte}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'archivage du compte {$compte->numeroCompte}: " . $e->getMessage());
            throw $e;
        }
    }
}
