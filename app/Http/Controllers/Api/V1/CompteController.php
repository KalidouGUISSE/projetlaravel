<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compte;
use App\Models\Client;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\CompteResource;
use OpenApi\Attributes as OA;

class CompteController extends Controller
{
    use ApiResponseTrait;
    #[OA\Get(
        path: "/v1/comptes",
        summary: "Lister les comptes avec pagination et filtres",
        description: "Retourne la liste des comptes avec support pour la pagination, le tri et les filtres.",
        tags: ["Comptes"],
        parameters: [
            new OA\Parameter(
                name: "client_id",
                in: "query",
                required: true,
                description: "ID du client pour filtrer les comptes",
                schema: new OA\Schema(type: "string", format: "uuid", example: "72f09e5b-e8f0-42e7-87c9-b2a8cb281adb")
            ),
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
                schema: new OA\Schema(type: "string", enum: ["created_at", "solde", "numeroCompte"], default: "created_at")
            ),
            new OA\Parameter(
                name: "order",
                in: "query",
                required: false,
                description: "Ordre de tri",
                schema: new OA\Schema(type: "string", enum: ["asc", "desc"], default: "desc")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                required: false,
                description: "Filtrer par type de compte",
                schema: new OA\Schema(type: "string", enum: ["epargne", "cheque"])
            ),
            new OA\Parameter(
                name: "statut",
                in: "query",
                required: false,
                description: "Filtrer par statut",
                schema: new OA\Schema(type: "string", enum: ["actif", "bloque", "ferme"])
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                required: false,
                description: "Rechercher dans numeroCompte, nom ou prenom du client",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste paginée des comptes",
                content: new OA\JsonContent(ref: "#/components/schemas/CompteResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Paramètres invalides",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
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
     * Client peut récupérer la liste de ses comptes avec pagination et filtres
     */
    public function index(Request $request)
    {
        // Assumer que le client est authentifié et son ID est disponible via auth
        // Pour l'exemple, on utilise un client_id fictif ou via auth
        // En production, utiliser auth()->user()->client_id ou similaire
        $clientId = $request->input('client_id'); // Temporaire, à remplacer par auth

        if (!$clientId) {
            // Pour le test, utiliser le premier client si pas fourni
            $clientId = Client::first()->id ?? null;
            if (!$clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun client trouvé'
                ], 404);
            }
        }

        $query = Compte::where('client_id', $clientId)->with('client');

        // Utiliser le trait pour la pagination, mais transformer avec CompteResource
        $paginated = $query->paginate($request->get('limit', 10));

        return response()->json([
            'success' => true,
            'data' => CompteResource::collection($paginated->items()),
            'pagination' => [
                'currentPage' => $paginated->currentPage(),
                'totalPages' => $paginated->lastPage(),
                'totalItems' => $paginated->total(),
                'hasNext' => $paginated->hasMorePages(),
                'hasPrevious' => $paginated->currentPage() > 1,
            ],
        ], 200);
    }
}
