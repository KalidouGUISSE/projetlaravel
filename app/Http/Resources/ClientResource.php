<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'titulaire' => $this->titulaire,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'nci' => $this->nci,
            'adresse' => $this->adresse,
            'dateCreation' => $this->created_at->toISOString(),
            'dateModification' => $this->updated_at->toISOString(),
            'comptes' => CompteResource::collection($this->whenLoaded('comptes')),
        ];
    }
}
