<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Client::all(), 200);
    }

    /**
     * Créer un nouveau client (avec validation).
     */
    public function store(StoreClientRequest $request)
    {
        $client = Client::create([
            'id'        => Str::uuid()->toString(),
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'telephone' => $request->telephone,
            'adresse'   => $request->adresse,
        ]);

        return response()->json([
            'message' => 'Client créé avec succès !',
            'data'    => $client,
        ], 201);
    }


    /**
     * Afficher les détails d’un client.
     */
    public function show(string $id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client, 200);
    }

    /**
     * Mettre à jour les informations d’un client.
     */
    public function update(Request $request, string $id)
    {
        $client = Client::findOrFail($id);
        $client->update($request->all());

        return response()->json([
            'message' => 'Client mis à jour avec succès !',
            'data'    => $client,
        ], 200);
    }

    /**
     * Supprimer un client.
     */
    public function destroy(string $id)
    {
        Client::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Client supprimé avec succès !',
        ], 200);
    }
}
