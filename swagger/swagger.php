<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Gestion Comptes Bancaires",
    version: "1.0",
    description: "API Laravel pour la gestion des clients, comptes bancaires et authentification séparée admin/client"
)]
#[OA\Server(
    url: "http://127.0.0.1:8001",
    description: "Serveur de développement local"
)]
#[OA\Server(
    url: "https://kalidou-guisse-projetlaravel.onrender.com",
    description: "Serveur de production"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Schema(
    schema: "Client",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "123e4567-e89b-12d3-a456-426614174000"),
        new OA\Property(property: "nom", type: "string", example: "Doe"),
        new OA\Property(property: "prenom", type: "string", example: "John"),
        new OA\Property(property: "titulaire", type: "string", example: "John Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
        new OA\Property(property: "telephone", type: "string", example: "+221771234567"),
        new OA\Property(property: "nci", type: "string", description: "Numéro de Carte d'Identité (13 chiffres)", example: "1234567890123"),
        new OA\Property(property: "adresse", type: "string", example: "Dakar, Sénégal"),
        new OA\Property(property: "dateCreation", type: "string", format: "date-time", example: "2025-10-27T11:01:29.000000Z"),
        new OA\Property(property: "dateModification", type: "string", format: "date-time", example: "2025-10-27T11:15:29.000000Z")
    ]
)]
#[OA\Schema(
    schema: "Admin",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "123e4567-e89b-12d3-a456-426614174000"),
        new OA\Property(property: "nom", type: "string", example: "Admin"),
        new OA\Property(property: "prenom", type: "string", example: "Test"),
        new OA\Property(property: "email", type: "string", format: "email", example: "admin@test.com"),
        new OA\Property(property: "telephone", type: "string", example: "+221771234567"),
        new OA\Property(property: "dateCreation", type: "string", format: "date-time", example: "2025-10-27T11:01:29.000000Z"),
        new OA\Property(property: "dateModification", type: "string", format: "date-time", example: "2025-10-27T11:15:29.000000Z")
    ]
)]
#[OA\Schema(
    schema: "AuthResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(property: "message", type: "string", example: "Connexion réussie"),
        new OA\Property(property: "data", type: "object", properties: [
            new OA\Property(property: "user", type: "object", properties: [
                new OA\Property(property: "id", type: "string", format: "uuid"),
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "email", type: "string", format: "email"),
                new OA\Property(property: "role", type: "string", enum: ["admin", "client"]),
                new OA\Property(property: "type", type: "string", enum: ["admin", "client"])
            ]),
            new OA\Property(property: "permissions", type: "array", items: new OA\Items(type: "string")),
            new OA\Property(property: "token_type", type: "string", example: "Bearer"),
            new OA\Property(property: "access_token", type: "string"),
            new OA\Property(property: "expires_in", type: "integer", example: 3600)
        ])
    ]
)]
#[OA\Schema(
    schema: "CompteResponse",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid"),
        new OA\Property(property: "numeroCompte", type: "string", example: "COMP-12345678"),
        new OA\Property(property: "titulaire", type: "string"),
        new OA\Property(property: "type", type: "string", enum: ["epargne", "cheque"]),
        new OA\Property(property: "solde", type: "number", format: "float"),
        new OA\Property(property: "devise", type: "string", example: "FCFA"),
        new OA\Property(property: "dateCreation", type: "string", format: "date-time"),
        new OA\Property(property: "statut", type: "string", enum: ["actif", "bloque", "ferme"]),
        new OA\Property(property: "metadata", type: "object"),
        new OA\Property(property: "client", ref: "#/components/schemas/Client")
    ]
)]
#[OA\Schema(
    schema: "ValidationErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(property: "message", type: "string", example: "Erreurs de validation"),
        new OA\Property(property: "errors", type: "object")
    ]
)]
#[OA\Schema(
    schema: "UnauthorizedErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(property: "message", type: "string", example: "Non authentifié"),
        new OA\Property(property: "errors", type: "object")
    ]
)]
#[OA\Schema(
    schema: "NotFoundErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(property: "message", type: "string", example: "Ressource non trouvée"),
        new OA\Property(property: "errors", type: "object")
    ]
)]
#[OA\Schema(
    schema: "ErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(property: "message", type: "string", example: "Erreur serveur"),
        new OA\Property(property: "errors", type: "object")
    ]
)]
#[OA\Schema(
    schema: "Compte",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "123e4567-e89b-12d3-a456-426614174001"),
        new OA\Property(property: "client_id", type: "string", format: "uuid", example: "123e4567-e89b-12d3-a456-426614174000"),
        new OA\Property(property: "numeroCompte", type: "string", example: "COMP-12345678"),
        new OA\Property(property: "type", type: "string", example: "courant"),
        new OA\Property(property: "solde", type: "number", format: "float", example: 1000.50),
        new OA\Property(property: "statut", type: "string", example: "actif"),
        new OA\Property(property: "metadata", type: "object", example: ["derniereModification" => "2023-10-23T04:13:35.000000Z", "version" => 1]),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
        new OA\Property(property: "client", ref: "#/components/schemas/Client")
    ]
)]
#[OA\PathItem(
    path: "/clients/{id}",
    parameters: [
        new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            description: "ID du client",
            schema: new OA\Schema(type: "string", format: "uuid")
        )
    ]
)]
#[OA\Post(
    path: "/guisse/v1/auth/admin/login",
    summary: "Connexion administrateur",
    description: "Authentification d'un administrateur avec email et mot de passe",
    tags: ["Authentification"],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "admin@test.com"),
                new OA\Property(property: "password", type: "string", example: "password123")
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: "Connexion réussie",
            content: new OA\JsonContent(ref: "#/components/schemas/AuthResponse")
        ),
        new OA\Response(
            response: 401,
            description: "Identifiants invalides",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "message", type: "string", example: "Identifiants invalides")
                ]
            )
        )
    ]
)]
#[OA\Post(
    path: "/guisse/v1/auth/client/login",
    summary: "Connexion client",
    description: "Authentification d'un client avec email et mot de passe",
    tags: ["Authentification"],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "maurine18@example.org"),
                new OA\Property(property: "password", type: "string", example: "password123")
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: "Connexion réussie",
            content: new OA\JsonContent(ref: "#/components/schemas/AuthResponse")
        ),
        new OA\Response(
            response: 401,
            description: "Identifiants invalides",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "message", type: "string", example: "Identifiants invalides")
                ]
            )
        )
    ]
)]
#[OA\Post(
    path: "/guisse/v1/auth/logout",
    summary: "Déconnexion",
    description: "Déconnexion de l'utilisateur actuel",
    security: [["bearerAuth" => []]],
    tags: ["Authentification"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Déconnexion réussie",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Déconnexion réussie")
                ]
            )
        )
    ]
)]
#[OA\Get(
    path: "/guisse/v1/comptes",
    summary: "Lister les comptes",
    description: "Admin: récupère tous les comptes. Client: récupère seulement ses comptes.",
    security: [["bearerAuth" => []]],
    tags: ["Comptes"],
    parameters: [
        new OA\Parameter(
            name: "client_id",
            in: "query",
            required: false,
            description: "ID du client (seulement pour admin)",
            schema: new OA\Schema(type: "string", format: "uuid")
        ),
        new OA\Parameter(
            name: "page",
            in: "query",
            required: false,
            description: "Numéro de page",
            schema: new OA\Schema(type: "integer", minimum: 1, default: 1)
        ),
        new OA\Parameter(
            name: "limit",
            in: "query",
            required: false,
            description: "Nombre d'éléments par page",
            schema: new OA\Schema(type: "integer", minimum: 1, maximum: 100, default: 10)
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
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Liste des comptes",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/CompteResponse")),
                    new OA\Property(property: "pagination", type: "object", properties: [
                        new OA\Property(property: "currentPage", type: "integer"),
                        new OA\Property(property: "totalPages", type: "integer"),
                        new OA\Property(property: "totalItems", type: "integer"),
                        new OA\Property(property: "hasNext", type: "boolean"),
                        new OA\Property(property: "hasPrevious", type: "boolean")
                    ])
                ]
            )
        ),
        new OA\Response(
            response: 403,
            description: "Accès refusé",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "message", type: "string", example: "Permissions insuffisantes pour cette opération")
                ]
            )
        )
    ]
)]
class OpenApiDocumentation {}
