<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\Compte;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\AdminResource;
use App\Http\Resources\CompteResource;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    use ApiResponseTrait;

    #[OA\Get(
        path: "/admins",
        summary: "Lister tous les admins",
        description: "Retourne la liste de tous les admins avec support pour la pagination, le tri et les filtres.",
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
                description: "Liste paginée des admins",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Admin")),
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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Admin::query();

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
            'data' => AdminResource::collection($paginated->items()),
            'pagination' => [
                'currentPage' => $paginated->currentPage(),
                'totalPages' => $paginated->lastPage(),
                'totalItems' => $paginated->total(),
                'hasNext' => $paginated->hasMorePages(),
                'hasPrevious' => $paginated->currentPage() > 1,
            ],
        ], 200);
    }

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

    #[OA\Get(
        path: "/admins/{id}",
        summary: "Afficher un admin spécifique",
        description: "Retourne les détails d'un admin par son ID.",
        tags: ["Admins"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'admin",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails de l'admin",
                content: new OA\JsonContent(ref: "#/components/schemas/Admin")
            ),
            new OA\Response(
                response: 404,
                description: "Admin non trouvé",
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $admin = Admin::findOrFail($id);
            return new AdminResource($admin);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admin non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'admin : ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/admins/{id}",
        summary: "Mettre à jour un admin",
        description: "Met à jour les informations d'un admin existant.",
        tags: ["Admins"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'admin",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Admin"),
                    new OA\Property(property: "prenom", type: "string", example: "Test"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin.test@example.com"),
                    new OA\Property(property: "telephone", type: "string", example: "+221701231374")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Admin mis à jour avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/AdminResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Admin non trouvé",
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
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, string $id)
    {
        try {
            $admin = Admin::findOrFail($id);
            $admin->update($request->validated());

            return response()->json([
                'message' => 'Admin mis à jour avec succès !',
                'data'    => new AdminResource($admin),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admin non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'admin : ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/admins/{id}",
        summary: "Supprimer un admin",
        description: "Supprime un admin par son ID.",
        tags: ["Admins"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'admin",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Admin supprimé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Admin supprimé avec succès !")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Admin non trouvé",
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $admin = Admin::findOrFail($id);
            $admin->delete();

            return response()->json([
                'message' => 'Admin supprimé avec succès !',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admin non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'admin : ' . $e->getMessage()
            ], 500);
        }
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
