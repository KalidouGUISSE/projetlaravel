<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteRequest;
use App\Models\Compte;

class CompteController extends Controller
{
    public function store(CompteRequest $request)
    {
        // Les données sont déjà validées par CompteRequest
        $data = $request->validated();

        $compte = Compte::create($data);

        return response()->json([
            'message' => 'Compte créé avec succès',
            'compte' => $compte
        ], 201);
    }
}
