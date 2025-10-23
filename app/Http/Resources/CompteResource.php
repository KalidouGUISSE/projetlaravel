<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calcul du solde
        $solde = 0;
        if ($this->transactions) {
            $debits = $this->transactions->where('statut', 'validee')
                          ->whereIn('type', ['retrait', 'virement', 'frais'])
                          ->sum('montant');
            $credits = $this->transactions->where('statut', 'validee')
                           ->where('type', 'depot')
                           ->sum('montant');
            $solde = $credits - $debits;
        }

        return [
            'id' => $this->id,
            'numeroCompte' => $this->numeroCompte,
            'type' => $this->type,
            'solde' => $solde,
            'devise' => 'FCFA',
            'statut' => $this->statut,
            'dateCreation' => $this->created_at->toISOString(),
            'dateModification' => $this->updated_at->toISOString(),
            'client' => new ClientResource($this->whenLoaded('client')),
            'metadata' => $this->metadata,
        ];
    }
}
