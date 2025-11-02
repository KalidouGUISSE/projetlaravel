<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Http\Resources\ClientResource;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    #[OA\Get(
          path: "/clients",
          operationId: "getClientsList",
          summary: "Lister tous les clients avec pagination",
          description: "Retourne la liste paginée des clients avec support pour la pagination, le tri et les filtres.",
          security: [["bearerAuth" => []]],
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
        operationId: "createClient",
        summary: "Créer un nouveau client",
        description: "Crée un nouveau client avec les informations fournies.",
        security: [["bearerAuth" => []]],
        tags: ["Clients"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nom", "prenom", "email", "telephone", "nci"],
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Kilback"),
                    new OA\Property(property: "prenom", type: "string", example: "Laury"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "kuhn@example.net"),
                    new OA\Property(property: "telephone", type: "string", example: "+2117865470"),
                    new OA\Property(property: "nci", type: "string", description: "Numéro de Carte d'Identité (13 chiffres)", example: "1234567890123"),
                    new OA\Property(property: "adresse", type: "string", example: "Dakar, Sénégal")
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Client créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Client créé avec succès !"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Client"),
                        new OA\Property(property: "generated_password", type: "string", example: "kK31ctO7PW3D")
                    ],
                    type: "object"
                )
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
        // Générer un mot de passe aléatoire
        $password = Str::random(12);

        $client = Client::create([
            'id'        => Str::uuid()->toString(),
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'telephone' => $request->telephone,
            'nci'       => $request->nci,
            'adresse'   => $request->adresse,
            'password'  => Hash::make($password),
        ]);

        return response()->json([
            'message' => 'Client créé avec succès !',
            'data'    => new ClientResource($client),
            'generated_password' => $password, // Inclure le mot de passe généré dans la réponse
        ], 201);
    }


    #[OA\Get(
        path: "/clients/{id}",
        operationId: "getClientById",
        summary: "Afficher un client spécifique",
        description: "Retourne les détails d'un client par son ID.",
        security: [["bearerAuth" => []]],
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
        operationId: "updateClient",
        summary: "Mettre à jour un client",
        description: "Met à jour les informations d'un client existant. Tous les champs sont optionnels, mais au moins un doit être modifié.",
        security: [["bearerAuth" => []]],
        tags: ["Clients"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du client",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "titulaire", type: "string", description: "Nom du titulaire", example: "Fatou Ndiaye"),
                    new OA\Property(
                        property: "informationsClient",
                        type: "object",
                        properties: [
                            new OA\Property(property: "telephone", type: "string", description: "Numéro de téléphone portable Sénégalais (+221XXXXXXXXX)", example: "+221781234567"),
                            new OA\Property(property: "email", type: "string", format: "email", example: "fatou.ndiaye@example.com"),
                            new OA\Property(property: "password", type: "string", minLength: 8, example: "monNouveauMotDePasse2025"),
                            new OA\Property(property: "nci", type: "string", description: "Numéro de Carte d'Identité (13 chiffres)", example: "9876543210987")
                        ]
                    )
                ],
                type: "object",
                example: [
                    "titulaire" => "Fatou Ndiaye",
                    "informationsClient" => [
                        "telephone" => "+221781234567",
                        "email" => "fatou.ndiaye@example.com",
                        "password" => "monNouveauMotDePasse2025",
                        "nci" => "9876543210987"
                    ]
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Client mis à jour avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Client mis à jour avec succès"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Client")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Aucun champ modifié",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Au moins un champ doit être modifié")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Non authentifié",
                content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedErrorResponse")
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
    public function update(UpdateClientRequest $request, string $id)
    {
        $client = Client::findOrFail($id);

        $updateData = [];

        // Mettre à jour le titulaire si fourni
        if ($request->has('titulaire')) {
            $updateData['titulaire'] = $request->titulaire;
        }

        // Mettre à jour les informations du client si fournies
        if ($request->has('informationsClient')) {
            $info = $request->informationsClient;

            if (isset($info['telephone'])) {
                $updateData['telephone'] = $info['telephone'];
            }

            if (isset($info['email'])) {
                $updateData['email'] = $info['email'];
            }

            if (isset($info['password'])) {
                $updateData['password'] = Hash::make($info['password']);
            }

            if (isset($info['nci'])) {
                $updateData['nci'] = $info['nci'];
            }
        }

        // Mettre à jour le client
        $client->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Client mis à jour avec succès',
            'data'    => new ClientResource($client),
        ], 200);
    }

    #[OA\Delete(
        path: "/clients/{id}",
        operationId: "deleteClient",
        summary: "Supprimer un client",
        description: "Supprime un client par son ID.",
        security: [["bearerAuth" => []]],
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

    #[OA\Get(
        path: "/clients/telephone/{telephone}",
        operationId: "getClientByTelephone",
        summary: "Récupérer un client par numéro de téléphone",
        description: "Retourne les détails d'un client en utilisant son numéro de téléphone.",
        security: [["bearerAuth" => []]],
        tags: ["Clients"],
        parameters: [
            new OA\Parameter(
                name: "telephone",
                in: "path",
                required: true,
                description: "Numéro de téléphone du client",
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
     * Récupérer un client par numéro de téléphone.
     */
    public function getByTelephone(string $telephone)
    {
        // Normaliser le numéro de téléphone pour la recherche
        $normalizedTelephone = $this->normalizeTelephone($telephone);

        $client = Client::where('telephone', $normalizedTelephone)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun client trouvé avec ce numéro de téléphone.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ClientResource($client),
        ], 200);
    }

    /**
     * Normaliser un numéro de téléphone sénégalais.
     * Accepte +221XXXXXXXXX ou XXXXXXXXX (9 chiffres).
     * Retourne toujours +221XXXXXXXXX.
     */
    private function normalizeTelephone(string $telephone): string
    {
        // Supprimer tous les espaces et caractères non numériques sauf +
        $cleaned = preg_replace('/[^\d+]/', '', $telephone);

        // Si commence par +221, c'est déjà normalisé
        if (str_starts_with($cleaned, '+221')) {
            return $cleaned;
        }

        // Si commence par 221, ajouter +
        if (str_starts_with($cleaned, '221')) {
            return '+' . $cleaned;
        }

        // Si c'est 9 chiffres, ajouter +221
        if (preg_match('/^\d{9}$/', $cleaned)) {
            return '+221' . $cleaned;
        }

        // Retourner tel quel si ne correspond pas aux patterns attendus
        return $telephone;
    }
}
