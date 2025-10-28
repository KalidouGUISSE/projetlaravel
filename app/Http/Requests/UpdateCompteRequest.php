<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalPhoneRule;
use App\Rules\SenegalNciRule;

class UpdateCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autoriser toutes les requêtes, adapter si nécessaire
    }

    public function rules(): array
    {
        $compteId = $this->route('id'); // ID du compte depuis la route

        return [
            'type' => 'sometimes|required|in:cheque,epargne',
            'solde' => 'sometimes|required|numeric|min:0',
            'statut' => 'sometimes|required|in:actif,bloque,ferme',
            'client' => 'sometimes|array',
            'client.id' => 'nullable|uuid|exists:clients,id',
            'client.titulaire' => 'nullable|string|max:255',
            'client.nci' => ['nullable', new SenegalNciRule()],
            'client.email' => 'nullable|email|unique:clients,email,' . ($this->input('client.id') ?: ''),
            'client.telephone' => ['nullable', new SenegalPhoneRule(), 'unique:clients,telephone,' . ($this->input('client.id') ?: '')],
            'client.adresse' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être "cheque" ou "epargne".',
            'solde.required' => 'Le solde est obligatoire.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être "actif", "bloque" ou "ferme".',
            'client.id.exists' => 'Le client sélectionné n\'existe pas.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        ];
    }
}