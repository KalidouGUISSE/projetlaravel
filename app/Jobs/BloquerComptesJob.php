<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BloquerComptesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $compteIds;
    protected string $motif;
    protected int $duree;
    protected string $unite;

    /**
     * Create a new job instance.
     */
    public function __construct(array $compteIds, string $motif, int $duree, string $unite)
    {
        $this->compteIds = $compteIds;
        $this->motif = $motif;
        $this->duree = $duree;
        $this->unite = $unite;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $dateDebut = now();
        $dateFin = $this->calculerDateFinBlocage($this->duree, $this->unite);

        $comptesBloques = 0;

        foreach ($this->compteIds as $compteId) {
            $compte = Compte::find($compteId);

            if (!$compte) {
                Log::warning("Compte non trouvé pour blocage via job: {$compteId}");
                continue;
            }

            // Vérifier que le compte est actif et de type épargne
            if ($compte->statut !== 'actif' || $compte->type !== 'epargne') {
                Log::warning("Impossible de bloquer le compte {$compte->numeroCompte}: statut={$compte->statut}, type={$compte->type}");
                continue;
            }

            // Bloquer le compte
            $compte->update([
                'statut' => 'bloque',
                'motifBlocage' => $this->motif,
                'date_debut_blocage' => $dateDebut,
                'date_fin_blocage' => $dateFin,
            ]);

            // Mettre à jour les metadata
            $metadata = $compte->metadata ?? [];
            $metadata['derniereModification'] = now();
            $metadata['version'] = ($metadata['version'] ?? 1) + 1;
            $metadata['blocageViaJob'] = true;
            $metadata['dateBlocageViaJob'] = now();
            $compte->metadata = $metadata;
            $compte->save();

            $comptesBloques++;
            Log::info("Compte bloqué automatiquement via job: {$compte->numeroCompte}");
        }

        Log::info("Job BloquerComptesJob exécuté: {$comptesBloques} comptes bloqués sur " . count($this->compteIds) . " demandés");
    }

    /**
     * Calculer la date de fin de blocage en fonction de la durée et de l'unité
     */
    private function calculerDateFinBlocage(int $duree, string $unite): Carbon
    {
        $dateFin = now();

        switch ($unite) {
            case 'jour':
            case 'jours':
                return $dateFin->addDays($duree);
            case 'semaine':
            case 'semaines':
                return $dateFin->addWeeks($duree);
            case 'mois':
                return $dateFin->addMonths($duree);
            case 'annee':
            case 'annees':
                return $dateFin->addYears($duree);
            default:
                throw new \InvalidArgumentException("Unité de temps invalide: {$unite}");
        }
    }
}
