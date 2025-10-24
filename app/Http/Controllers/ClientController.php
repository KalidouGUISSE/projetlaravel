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
         summary: "Lister tous les clients avec pagination",
         description: "Retourne la liste paginée des clients avec support pour la pagination, le tri et les filtres.",
         tags: ["Clients"],
         parameters: [
             new OA\Parameter(
                 name: "page",
                 in: "query",
                 required: false,
                 description: "Numéro de la page",
                 schema: new OA\Schema(type: "integer", minimum: 1, default: 1)
             ),
             new OA\Parameter(
                 name: "limit",
                 in: "query",
                 required: false,
                 description: "Nombre d'éléments par page (max 100)",
                 schema: new OA\Schema(type: "integer", minimum: 1, maximum: 100, default: 10)
             ),
             new OA\Parameter(
                 name: "sort",
                 in: "query",
                 required: false,
                 description: "Champ de tri",
                 schema: new OA\Schema(type: "string", enum: ["created_at", "nom", "prenom", "email"], default: "created_at")
             ),
             new OA\Parameter(
                 name: "order",
                 in: "query",
                 required: false,
                 description: "Ordre de tri",
                 schema: new OA\Schema(type: "string", enum: ["asc", "desc"], default: "desc")
             ),
             new OA\Parameter(
                 name: "search",
                 in: "query",
                 required: false,
                 description: "Rechercher dans nom, prenom ou email",
                 schema: new OA\Schema(type: "string")
             )
         ],
         responses: [
             new OA\Response(
                 response: 200,
                 description: "Liste paginée des clients",
                 content: new OA\JsonContent(
                     properties: [
                         new OA\Property(property: "success", type: "boolean", example: true),
                         new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Client")),
                         new OA\Property(property: "pagination", type: "object", properties: [
                             new OA\Property(property: "currentPage", type: "integer", example: 1),
                             new OA\Property(property: "totalPages", type: "integer", example: 1),
                             new OA\Property(property: "totalItems", type: "integer", example: 10),
                             new OA\Property(property: "hasNext", type: "boolean", example: false),
                             new OA\Property(property: "hasPrevious", type: "boolean", example: false)
                         ])
                     ],
                     type: "object"
                 )
             ),
             new OA\Response(
                 response: 500,
                 description: "Erreur serveur",
                 content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
             )
         ]
     )]
    /**
      * Display a listing of the resource with pagination.
      */
     public function index(Request $request)
     {
         $query = Client::query();

         // Appliquer la recherche si fournie
         if ($request->has('search') && !empty($request->search)) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('nom', 'like', "%{$search}%")
                   ->orWhere('prenom', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
             });
         }

         // Appliquer le tri
         $sort = $request->get('sort', 'created_at');
         $order = $request->get('order', 'desc');
         $query->orderBy($sort, $order);

         // Paginer les résultats
         $paginated = $query->paginate($request->get('limit', 10));

         return response()->json([
             'success' => true,
             'data' => ClientResource::collection($paginated->items()),
             'pagination' => [
                 'currentPage' => $paginated->currentPage(),
                 'totalPages' => $paginated->lastPage(),
                 'totalItems' => $paginated->total(),
                 'hasNext' => $paginated->hasMorePages(),
                 'hasPrevious' => $paginated->currentPage() > 1,
             ],
         ], 200);
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
