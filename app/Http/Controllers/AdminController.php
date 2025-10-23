<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\Compte;
use App\Http\Requests\StoreAdminRequest;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\AdminResource;
use App\Http\Resources\CompteResource;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    use ApiResponseTrait;
    #[OA\Get(
        path: "/admins/comptes",
        summary: "Lister tous les comptes avec pagination et filtres (Admin)",
        description: "Retourne la liste paginée de tous les comptes avec support pour la pagination, le tri et les filtres.",
        tags: ["Admins"],
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
     * Admin peut récupérer la liste de tous les comptes avec pagination et filtres
     */
    public function getAllComptes(Request $request)
    {
        $query = Compte::with('client');

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

    #[OA\Post(
        path: "/admins",
        summary: "Créer un nouvel admin",
        description: "Crée un nouvel admin avec les informations fournies.",
        tags: ["Admins"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/AdminCreateRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Admin créé avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/AdminResponse")
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
     * Créer un nouvel admin (avec validation).
     */
    public function store(StoreAdminRequest $request)
    {
        $admin = Admin::create([
            'id'        => Str::uuid()->toString(),
            'nom'       => $request->nom,
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'telephone' => $request->telephone,
        ]);

        return response()->json([
            'message' => 'Admin créé avec succès !',
            'data'    => new AdminResource($admin),
        ], 201);
    }
}
