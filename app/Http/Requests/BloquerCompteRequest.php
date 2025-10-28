<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BloquerCompteRequest extends FormRequest
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
            'motif' => 'required|string|max:255',
            'duree' => 'required_without:date_debut|integer|min:1',
            'unite' => 'required_without:date_debut|string|in:jour,jours,semaine,semaines,mois,annee,annees',
            'date_debut' => 'nullable|date|after:now',
            'date_fin' => 'nullable|date|after:date_debut',
        ];
    }

    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif de blocage est obligatoire.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.max' => 'Le motif ne peut pas dépasser 255 caractères.',
            'duree.required_without' => 'La durée est obligatoire si aucune date de début n\'est spécifiée.',
            'duree.integer' => 'La durée doit être un nombre entier.',
            'duree.min' => 'La durée doit être d\'au moins 1.',
            'unite.required_without' => 'L\'unité est obligatoire si aucune date de début n\'est spécifiée.',
            'unite.in' => 'L\'unité doit être l\'une des suivantes : jour, jours, semaine, semaines, mois, annee, annees.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_debut.after' => 'La date de début doit être dans le futur.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after' => 'La date de fin doit être après la date de début.',
        ];
    }
}
