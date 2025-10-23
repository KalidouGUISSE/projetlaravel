<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Http\Resources\ClientResource;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    #[OA\Get(
        path: "/clients",
        summary: "Lister tous les clients",
        description: "Retourne la liste de tous les clients.",
        tags: ["Clients"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des clients",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Client")
                )
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié",
                content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ClientResource::collection(Client::all());
    }

    #[OA\Post(
        path: "/clients",
        summary: "Créer un nouveau client",
        description: "Crée un nouveau client avec les informations fournies.",
        tags: ["Clients"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/ClientCreateRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Client créé avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/ClientResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Erreurs de validation",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
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
        ]);

        return response()->json([
            'message' => 'Client créé avec succès !',
            'data'    => new ClientResource($client),
        ], 201);
    }


    #[OA\Get(
        path: "/clients/{id}",
        summary: "Afficher un client spécifique",
        description: "Retourne les détails d'un client par son ID.",
        tags: ["Clients"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du client",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails du client",
                content: new OA\JsonContent(ref: "#/components/schemas/Client")
            ),
            new OA\Response(
                response: 404,
                description: "Client non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    /**
     * Afficher les détails d’un client.
     */
    public function show(string $id)
    {
        $client = Client::findOrFail($id);
        return new ClientResource($client);
    }

    #[OA\Put(
        path: "/clients/{id}",
        summary: "Mettre à jour un client",
        description: "Met à jour les informations d'un client existant.",
        tags: ["Clients"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du client",
                schema: new OA\Schema(type: "string")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/ClientUpdateRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Client mis à jour avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/ClientResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Client non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Erreurs de validation",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    /**
     * Mettre à jour les informations d’un client.
     */
    public function update(Request $request, string $id)
    {
        $client = Client::findOrFail($id);
        $client->update($request->all());

        return response()->json([
            'message' => 'Client mis à jour avec succès !',
            'data'    => new ClientResource($client),
        ], 200);
    }

    #[OA\Delete(
        path: "/clients/{id}",
        summary: "Supprimer un client",
        description: "Supprime un client par son ID.",
        tags: ["Clients"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du client",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Client supprimé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Client supprimé avec succès !")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Client non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
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
