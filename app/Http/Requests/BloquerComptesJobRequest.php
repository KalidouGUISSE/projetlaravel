<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BloquerComptesJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'compte_ids' => 'required|array|min:1',
            'compte_ids.*' => 'required|string|uuid',
            'motif' => 'required|string|max:255',
            'duree' => 'required|integer|min:1',
            'unite' => 'required|string|in:jour,jours,semaine,semaines,mois,annee,annees',
        ];
    }

    public function messages(): array
    {
        return [
            'compte_ids.required' => 'La liste des IDs de comptes est obligatoire.',
            'compte_ids.array' => 'Les IDs de comptes doivent être un tableau.',
            'compte_ids.min' => 'Au moins un compte doit être spécifié.',
            'compte_ids.*.required' => 'Chaque ID de compte est obligatoire.',
            'compte_ids.*.string' => 'Chaque ID de compte doit être une chaîne de caractères.',
            'compte_ids.*.uuid' => 'Chaque ID de compte doit être un UUID valide.',
            'motif.required' => 'Le motif de blocage est obligatoire.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.max' => 'Le motif ne peut pas dépasser 255 caractères.',
            'duree.required' => 'La durée de blocage est obligatoire.',
            'duree.integer' => 'La durée doit être un nombre entier.',
            'duree.min' => 'La durée doit être d\'au moins 1.',
            'unite.required' => 'L\'unité de temps est obligatoire.',
            'unite.in' => 'L\'unité doit être l\'une des suivantes : jour, jours, semaine, semaines, mois, annee, annees.',
        ];
    }
}
