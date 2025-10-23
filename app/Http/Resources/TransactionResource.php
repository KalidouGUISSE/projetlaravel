<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'type' => $this->type,
            'montant' => $this->montant,
            'devise' => $this->devise,
            'description' => $this->description,
            'dateTransaction' => $this->dateTransaction->toISOString(),
            'statut' => $this->statut,
            'compte' => new CompteResource($this->whenLoaded('compte')),
        ];
    }
}
