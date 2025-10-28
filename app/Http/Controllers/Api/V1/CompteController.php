<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compte;
use App\Models\Client;
use App\Models\Admin;
use App\Traits\ApiResponseTrait;
use App\Http\Resources\CompteResource;
use App\Http\Requests\CompteRequest;
use App\Http\Requests\UpdateCompteRequest;
use App\Http\Requests\BloquerCompteRequest;
use App\Http\Requests\DebloquerCompteRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class CompteController extends Controller
{
    use ApiResponseTrait;
    #[OA\Get(
        path: "/comptes",
        summary: "Lister les comptes avec pagination et filtres",
        description: "Retourne la liste des comptes avec support pour la pagination, le tri et les filtres.",
        tags: ["Comptes"],
        parameters: [
            new OA\Parameter(
                name: "client_id",
                in: "query",
                required: false,
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
        $clientId = $request->input('client_id');

        if ($clientId) {
            $query = Compte::where('client_id', $clientId)->with('client');
        } else {
            // Lister tous les comptes si pas de client_id fourni
            $query = Compte::with('client');
        }

        // Appliquer les filtres
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numeroCompte', 'like', '%' . $search . '%')
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('titulaire', 'like', '%' . $search . '%')
                                  ->orWhere('nom', 'like', '%' . $search . '%')
                                  ->orWhere('prenom', 'like', '%' . $search . '%');
                  });
            });
        }

        // Appliquer le tri
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        // Pagination
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
        path: "/comptes",
        summary: "Créer un nouveau compte",
        description: "Crée un nouveau compte pour un client, en créant le client si nécessaire. Requiert une authentification.",
        security: [
            new OA\SecurityScheme(
                securityScheme: "bearerAuth",
                type: "http",
                scheme: "bearer"
            )
        ],
        tags: ["Comptes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "soldeInitial", "devise", "client", "client.nci"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["cheque", "epargne"], example: "cheque"),
                    new OA\Property(property: "soldeInitial", type: "number", minimum: 10000, example: 500000),
                    new OA\Property(property: "devise", type: "string", example: "FCFA"),
                    new OA\Property(property: "client", type: "object", properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid", nullable: true, example: null),
                        new OA\Property(property: "titulaire", type: "string", example: "Hawa BB Wane"),
                        new OA\Property(property: "nci", type: "string", example: "1234567890123"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "cheikh.sy@example.com"),
                        new OA\Property(property: "telephone", type: "string", example: "+221771234567"),
                        new OA\Property(property: "adresse", type: "string", example: "Dakar, Sénégal")
                    ])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Compte créé avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/CompteResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function store(CompteRequest $request)
    {
        try {
            $data = $request->validated();

            // Vérifier si le client existe
            $client = null;
            if ($data['client']['id']) {
                $client = Client::find($data['client']['id']);
                // Si le client existe mais que des champs sont fournis, les mettre à jour
                if ($client && (!empty($data['client']['titulaire']) || !empty($data['client']['email']) || !empty($data['client']['telephone']) || !empty($data['client']['adresse']))) {
                    $client->update(array_filter([
                        'titulaire' => $data['client']['titulaire'] ?: $client->titulaire,
                        'email' => $data['client']['email'] ?: $client->email,
                        'telephone' => $data['client']['telephone'] ?: $client->telephone,
                        'adresse' => $data['client']['adresse'] ?: $client->adresse,
                        'nci' => $data['client']['nci'] ?: $client->nci,
                    ]));
                }
            } else {
                // Chercher par email ou téléphone
                $client = Client::where('email', $data['client']['email'])->orWhere('telephone', $data['client']['telephone'])->first();
            }

            if (!$client) {
                // Créer le client
                $password = Str::random(12); // Générer un mot de passe
                $code = Str::random(6); // Générer un code

                $client = Client::create([
                    'id' => (string) Str::uuid(),
                    'nom' => explode(' ', $data['client']['titulaire'])[0] ?? '',
                    'prenom' => implode(' ', array_slice(explode(' ', $data['client']['titulaire']), 1)) ?? '',
                    'titulaire' => $data['client']['titulaire'],
                    'email' => $data['client']['email'],
                    'telephone' => $data['client']['telephone'],
                    'nci' => $data['client']['nci'],
                    'adresse' => $data['client']['adresse'],
                    'password' => Hash::make($password),
                    'code' => $code,
                ]);

                // Envoyer email avec mot de passe (désactivé pour les tests)
                // Mail::raw("Votre mot de passe est : {$password}", function ($message) use ($client) {
                //     $message->to($client->email)->subject('Mot de passe de connexion');
                // });

                // Log temporaire au lieu d'envoyer l'email
                Log::info("Email non envoyé - Mot de passe pour {$client->email} : {$password}");

                // Envoyer SMS avec code (simulation)
                // Ici, utiliser un service SMS réel, pour l'instant log
                Log::info("SMS envoyé à {$client->telephone} : Code : {$code}");
            }

            // Créer le compte
            $compte = Compte::create([
                'client_id' => $client->id,
                'type' => $data['type'],
                'solde' => $data['soldeInitial'], // Solde initial
                'statut' => 'actif',
            ]);

            // Le numéro de compte est généré automatiquement dans le boot

            return $this->successResponse(
                $this->getCompteData($compte),
                'Compte créé avec succès',
                201
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création du compte : ' . $e->getMessage(), 500);
        }
    }

    /**
     * Générer la structure de données du compte pour la réponse
     */
    private function getCompteData(Compte $compte): array
    {
        return [
            'id' => $compte->id,
            'numeroCompte' => $compte->numeroCompte,
            'titulaire' => $compte->client->titulaire ?? $compte->client->nom . ' ' . $compte->client->prenom,
            'type' => $compte->type,
            'solde' => $compte->solde,
            'devise' => 'FCFA',
            'dateCreation' => $compte->created_at->toISOString(),
            'statut' => $compte->statut,
            'metadata' => $compte->metadata,
        ];
    }

    #[OA\Get(
        path: "/comptes/{id}",
        summary: "Récupérer un compte spécifique",
        description: "Admin peut récupérer n'importe quel compte. Client peut récupérer seulement ses propres comptes.",
        security: [
            new OA\SecurityScheme(
                securityScheme: "bearerAuth",
                type: "http",
                scheme: "bearer"
            )
        ],
        tags: ["Comptes"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails du compte",
                content: new OA\JsonContent(ref: "#/components/schemas/CompteResponse")
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Accès refusé")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Compte non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function show(string $id)
    {
        try {
            $compte = Compte::with('client')->findOrFail($id);

            // Retourner directement les données du compte sans vérifications d'auth
            return $this->successResponse(
                $this->getCompteData($compte),
                'Compte récupéré avec succès',
                200
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Compte non trouvé', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération du compte : ' . $e->getMessage(), 500);
        }
    }


    #[OA\Put(
        path: "/comptes/{id}",
        summary: "Mettre à jour un compte",
        description: "Met à jour les informations d'un compte existant. Permet de modifier le type, le solde, le statut et les informations du client associé.",
        security: [
            new OA\SecurityScheme(
                securityScheme: "bearerAuth",
                type: "http",
                scheme: "bearer"
            )
        ],
        tags: ["Comptes"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte à mettre à jour",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["cheque", "epargne"], example: "epargne"),
                    new OA\Property(property: "solde", type: "number", minimum: 0, example: 750000),
                    new OA\Property(property: "statut", type: "string", enum: ["actif", "bloque", "ferme"], example: "actif"),
                    new OA\Property(property: "client", type: "object", properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid", example: "72f09e5b-e8f0-42e7-87c9-b2a8cb281adb"),
                        new OA\Property(property: "titulaire", type: "string", example: "Hawa BB Wane"),
                        new OA\Property(property: "nci", type: "string", example: "1234567890123"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "hawa.wane@example.com"),
                        new OA\Property(property: "telephone", type: "string", example: "+221771234567"),
                        new OA\Property(property: "adresse", type: "string", example: "Dakar, Sénégal")
                    ])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Compte mis à jour avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/CompteResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Compte non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function update(UpdateCompteRequest $request, string $id)
    {
        try {
            $compte = Compte::with('client')->findOrFail($id);
            $data = $request->validated();

            // Mettre à jour les informations du compte
            $updateData = [];
            if (isset($data['type'])) {
                $updateData['type'] = $data['type'];
            }
            if (isset($data['solde'])) {
                $updateData['solde'] = $data['solde'];
            }
            if (isset($data['statut'])) {
                $updateData['statut'] = $data['statut'];
            }

            if (!empty($updateData)) {
                $compte->update($updateData);

                // Mettre à jour les metadata
                $metadata = $compte->metadata ?? [];
                $metadata['derniereModification'] = now();
                $metadata['version'] = ($metadata['version'] ?? 1) + 1;
                $compte->metadata = $metadata;
                $compte->save();
            }

            // Mettre à jour les informations du client si fourni
            if (isset($data['client']) && !empty($data['client'])) {
                $clientData = array_filter($data['client'], function($value) {
                    return $value !== null && $value !== '';
                });

                if (!empty($clientData)) {
                    $compte->client->update($clientData);
                }
            }

            // Recharger le compte avec les relations mises à jour
            $compte->load('client');

            return $this->successResponse(
                $this->getCompteData($compte),
                'Compte mis à jour avec succès',
                200
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Compte non trouvé', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la mise à jour du compte : ' . $e->getMessage(), 500);
        }
    }


    #[OA\Delete(
        path: "/comptes/{id}",
        summary: "Supprimer un compte (soft delete)",
        description: "Effectue une suppression douce du compte en changeant le statut à 'ferme' et en définissant la date de fermeture.",
        security: [
            new OA\SecurityScheme(
                securityScheme: "bearerAuth",
                type: "http",
                scheme: "bearer"
            )
        ],
        tags: ["Comptes"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte à supprimer",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Compte supprimé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Compte supprimé avec succès"),
                        new OA\Property(property: "data", properties: [
                            new OA\Property(property: "id", type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000"),
                            new OA\Property(property: "numeroCompte", type: "string", example: "C00123456"),
                            new OA\Property(property: "statut", type: "string", example: "ferme"),
                            new OA\Property(property: "dateFermeture", type: "string", format: "date-time", example: "2025-10-19T11:15:00Z")
                        ], type: "object")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Compte non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function destroy(string $id)
    {
        try {
            $compte = Compte::findOrFail($id);

            // Effectuer le soft delete
            $compte->delete();

            // Retourner les données mises à jour
            return $this->successResponse([
                'id' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'statut' => $compte->statut,
                'dateFermeture' => $compte->deleted_at->toISOString(),
            ], 'Compte supprimé avec succès', 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Compte non trouvé', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression du compte : ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/comptes/{id}/bloquer",
        summary: "Bloquer un compte",
        description: "Bloque un compte épargne actif avec un motif et une durée spécifiée.",
        security: [
            new OA\SecurityScheme(
                securityScheme: "bearerAuth",
                type: "http",
                scheme: "bearer"
            )
        ],
        tags: ["Comptes"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte à bloquer",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["motif", "duree", "unite"],
                properties: [
                    new OA\Property(property: "motif", type: "string", example: "Activité suspecte détectée"),
                    new OA\Property(property: "duree", type: "integer", example: 30),
                    new OA\Property(property: "unite", type: "string", enum: ["jour", "jours", "semaine", "semaines", "mois", "annee", "annees"], example: "mois")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Compte bloqué avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/CompteResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides ou compte déjà bloqué",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Compte non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function bloquer(BloquerCompteRequest $request, string $id)
    {
        try {
            $compte = Compte::findOrFail($id);

            // Vérifier que le compte est actif
            if ($compte->statut !== 'actif') {
                return $this->errorResponse('Seul un compte actif peut être bloqué.', 400);
            }

            // Vérifier que c'est un compte épargne
            if ($compte->type !== 'epargne') {
                return $this->errorResponse('Seul un compte épargne peut être bloqué.', 400);
            }

            $data = $request->validated();

            // Calculer la date de fin de blocage
            $dateDebut = now();
            $dateFin = $this->calculerDateFinBlocage($data['duree'], $data['unite']);

            // Bloquer le compte
            $compte->update([
                'statut' => 'bloque',
                'motifBlocage' => $data['motif'],
                'date_debut_blocage' => $dateDebut,
                'date_fin_blocage' => $dateFin,
            ]);

            // Mettre à jour les metadata
            $metadata = $compte->metadata ?? [];
            $metadata['derniereModification'] = now();
            $metadata['version'] = ($metadata['version'] ?? 1) + 1;
            $compte->metadata = $metadata;
            $compte->save();

            return $this->successResponse([
                'id' => $compte->id,
                'statut' => $compte->statut,
                'motifBlocage' => $compte->motifBlocage,
                'dateBlocage' => $compte->date_debut_blocage->toISOString(),
                'dateDeblocagePrevue' => $compte->date_fin_blocage->toISOString(),
            ], 'Compte bloqué avec succès', 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Compte non trouvé', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du blocage du compte : ' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/comptes/{id}/debloquer",
        summary: "Débloquer un compte",
        description: "Débloque un compte bloqué avec un motif spécifié.",
        security: [
            new OA\SecurityScheme(
                securityScheme: "bearerAuth",
                type: "http",
                scheme: "bearer"
            )
        ],
        tags: ["Comptes"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte à débloquer",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["motif"],
                properties: [
                    new OA\Property(property: "motif", type: "string", example: "Vérification complétée")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Compte débloqué avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/CompteResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides ou compte non bloqué",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Compte non trouvé",
                content: new OA\JsonContent(ref: "#/components/schemas/NotFoundErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function debloquer(DebloquerCompteRequest $request, string $id)
    {
        try {
            $compte = Compte::findOrFail($id);

            // Vérifier que le compte est bloqué
            if ($compte->statut !== 'bloque') {
                return $this->errorResponse('Seul un compte bloqué peut être débloqué.', 400);
            }

            $data = $request->validated();

            // Débloquer le compte
            $compte->update([
                'statut' => 'actif',
                'motifBlocage' => null,
                'date_debut_blocage' => null,
                'date_fin_blocage' => null,
            ]);

            // Mettre à jour les metadata
            $metadata = $compte->metadata ?? [];
            $metadata['derniereModification'] = now();
            $metadata['version'] = ($metadata['version'] ?? 1) + 1;
            $compte->metadata = $metadata;
            $compte->save();

            return $this->successResponse([
                'id' => $compte->id,
                'statut' => $compte->statut,
                'dateDeblocage' => now()->toISOString(),
            ], 'Compte débloqué avec succès', 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Compte non trouvé', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du déblocage du compte : ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculer la date de fin de blocage en fonction de la durée et de l'unité
     */
    private function calculerDateFinBlocage(int $duree, string $unite): Carbon
    {
        $dateFin = now();

        switch ($unite) {
            case 'jour':
            case 'jours':
                return $dateFin->addDays($duree);
            case 'semaine':
            case 'semaines':
                return $dateFin->addWeeks($duree);
            case 'mois':
                return $dateFin->addMonths($duree);
            case 'annee':
            case 'annees':
                return $dateFin->addYears($duree);
            default:
                throw new \InvalidArgumentException("Unité de temps invalide: {$unite}");
        }
    }
}
