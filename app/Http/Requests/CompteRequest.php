<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalPhoneRule;
use App\Rules\SenegalNciRule;

class CompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autoriser toutes les requêtes, adapter si nécessaire
    }

    public function rules(): array
    {
        $clientId = $this->input('client.id');

        return [
            'type' => 'required|in:cheque,epargne',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string|in:FCFA',
            'client' => 'required|array',
            'client.id' => 'nullable|uuid|exists:clients,id',
            'client.titulaire' => $clientId ? 'nullable|string|max:255' : 'required|string|max:255',
            'client.nci' => ['nullable', new SenegalNciRule()],
            'client.email' => $clientId ? 'nullable|email|unique:clients,email,' . $clientId : 'required|email|unique:clients,email',
            'client.telephone' => $clientId ? ['nullable', new SenegalPhoneRule(), 'unique:clients,telephone,' . $clientId] : ['required', new SenegalPhoneRule(), 'unique:clients,telephone'],
            'client.adresse' => $clientId ? 'nullable|string|max:255' : 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être "cheque" ou "epargne".',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être supérieur ou égal à 10000.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être FCFA.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.id.exists' => 'Le client sélectionné n\'existe pas.',
            'client.titulaire.required' => 'Le nom du titulaire est requis.',
            'client.email.required' => 'L\'email est requis.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le téléphone est requis.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.adresse.required' => 'L\'adresse est requise.',
        ];
    }
}
