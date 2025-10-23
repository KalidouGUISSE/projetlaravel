<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autoriser toutes les requêtes, adapter si nécessaire
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:epargne,cheque',
            'solde' => 'required|numeric|min:0',
            'statut' => 'nullable|in:actif,bloque,ferme',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être soit "epargne", soit "cheque".',
            'solde.required' => 'Le solde est obligatoire.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde doit être supérieur ou égal à 0.',
            'statut.in' => 'Le statut doit être "actif", "bloque" ou "ferme".',
        ];
    }
}
