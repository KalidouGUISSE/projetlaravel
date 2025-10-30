<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Kalidou GUISSE",
    version: "0.1",
    description: "Documentation de mon API Laravel pour la gestion des clients et comptes"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Serveur de développement"
)]
#[OA\Server(
    url: "https://kalidou-guisse-projetlaravel.onrender.com",
    description: "Serveur de production"
)]
#[OA\Schema(
    schema: "Client",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "c7917097-4734-4dfd-a462-b6c8f8d4aea3"),
        new OA\Property(property: "nom", type: "string", example: "Kilback"),
        new OA\Property(property: "prenom", type: "string", example: "Laury"),
        new OA\Property(property: "email", type: "string", format: "email", example: "brigitte.kuhn@example.net"),
        new OA\Property(property: "telephone", type: "string", example: "+1-201-736-0670"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2025-10-22T11:37:37.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2025-10-22T11:37:37.000000Z")
    ]
)]
#[OA\Schema(
    schema: "Compte",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "281f3aa6-bffe-41bd-8c31-2b02a1e49275"),
        new OA\Property(property: "numeroCompte", type: "string", example: "23886990999492"),
        new OA\Property(property: "titulaire", type: "string", example: "Kilback Laury"),
        new OA\Property(property: "type", type: "string", enum: ["epargne", "cheque"], example: "cheque"),
        new OA\Property(property: "solde", type: "number", format: "float", example: 1558.59),
        new OA\Property(property: "devise", type: "string", example: "FCFA"),
        new OA\Property(property: "dateCreation", type: "string", format: "date-time", example: "2025-10-22T11:37:37.000000Z"),
        new OA\Property(property: "statut", type: "string", enum: ["actif", "bloque", "ferme"], example: "actif"),
        new OA\Property(property: "motifBlocage", type: "string", nullable: true, example: "Inactivité de 30+ jours"),
        new OA\Property(
            property: "metadata",
            type: "object",
            properties: [
                new OA\Property(property: "derniereModification", type: "string", format: "date-time", example: "2025-10-22T11:37:37.082038Z"),
                new OA\Property(property: "version", type: "integer", example: 1)
            ]
        )
    ]
)]
#[OA\Schema(
    schema: "CompteResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(
            property: "data",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/Compte")
        ),
        new OA\Property(
            property: "pagination",
            type: "object",
            properties: [
                new OA\Property(property: "currentPage", type: "integer", example: 1),
                new OA\Property(property: "totalPages", type: "integer", example: 5),
                new OA\Property(property: "totalItems", type: "integer", example: 50),
                new OA\Property(property: "hasNext", type: "boolean", example: true),
                new OA\Property(property: "hasPrevious", type: "boolean", example: false)
            ]
        ),
        new OA\Property(
            property: "links",
            type: "object",
            properties: [
                new OA\Property(property: "self", type: "string", example: "https://kalidou-guisse-projetlaravel.onrender.com/guisse/v1/comptes?page=1"),
                new OA\Property(property: "first", type: "string", example: "https://kalidou-guisse-projetlaravel.onrender.com/guisse/v1/comptes?page=1"),
                new OA\Property(property: "last", type: "string", example: "https://kalidou-guisse-projetlaravel.onrender.com/guisse/v1/comptes?page=5"),
                new OA\Property(property: "next", type: "string", nullable: true, example: "https://kalidou-guisse-projetlaravel.onrender.com/guisse/v1/comptes?page=2"),
                new OA\Property(property: "previous", type: "string", nullable: true, example: null)
            ]
        )
    ]
)]
#[OA\Schema(
    schema: "ClientResponse",
    type: "object",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Client créé avec succès !"),
        new OA\Property(property: "data", ref: "#/components/schemas/Client")
    ]
)]
#[OA\Schema(
    schema: "ClientCreateRequest",
    type: "object",
    required: ["nom", "prenom", "email", "telephone"],
    properties: [
        new OA\Property(property: "nom", type: "string", example: "Kilback"),
        new OA\Property(property: "prenom", type: "string", example: "Laury"),
        new OA\Property(property: "email", type: "string", format: "email", example: "brigitte.kuhn@example.net"),
        new OA\Property(property: "telephone", type: "string", example: "+1-201-736-0670")
    ]
)]
#[OA\Schema(
    schema: "ClientUpdateRequest",
    type: "object",
    properties: [
        new OA\Property(property: "nom", type: "string", example: "Kilback"),
        new OA\Property(property: "prenom", type: "string", example: "Laury"),
        new OA\Property(property: "email", type: "string", format: "email", example: "brigitte.kuhn@example.net"),
        new OA\Property(property: "telephone", type: "string", example: "+1-201-736-0670")
    ]
)]
#[OA\Schema(
    schema: "ErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(property: "message", type: "string", example: "Une erreur est survenue"),
        new OA\Property(property: "errors", type: "object", nullable: true, example: null),
        new OA\Property(property: "code", type: "integer", example: 500)
    ]
)]
#[OA\Schema(
    schema: "ValidationErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(property: "message", type: "string", example: "Les données fournies sont invalides"),
        new OA\Property(
            property: "errors",
            type: "object",
            properties: [
                new OA\Property(
                    property: "nom",
                    type: "array",
                    items: new OA\Items(type: "string"),
                    example: ["Le champ nom est obligatoire."]
                ),
                new OA\Property(
                    property: "email",
                    type: "array",
                    items: new OA\Items(type: "string"),
                    example: ["Le champ email doit être une adresse email valide."]
                )
            ]
        ),
        new OA\Property(property: "code", type: "integer", example: 422)
    ]
)]
#[OA\Schema(
    schema: "NotFoundErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(property: "message", type: "string", example: "Client non trouvé"),
        new OA\Property(property: "errors", type: "object", nullable: true, example: null),
        new OA\Property(property: "code", type: "integer", example: 404)
    ]
)]
#[OA\Schema(
    schema: "UnauthorizedErrorResponse",
    type: "object",
    properties: [
        new OA\Property(property: "status", type: "string", example: "error"),
        new OA\Property(property: "message", type: "string", example: "Non authentifié"),
        new OA\Property(property: "errors", type: "object", nullable: true, example: null),
        new OA\Property(property: "code", type: "integer", example: 401)
    ]
)]
#[OA\Schema(
    schema: "Admin",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "c7917097-4734-4dfd-a462-b6c8f8d4aea3"),
        new OA\Property(property: "nom", type: "string", example: "Kilback"),
        new OA\Property(property: "prenom", type: "string", example: "Laury"),
        new OA\Property(property: "email", type: "string", format: "email", example: "brigitte.kuhn@example.net"),
        new OA\Property(property: "telephone", type: "string", example: "+1-201-736-0670"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2025-10-22T11:37:37.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2025-10-22T11:37:37.000000Z")
    ]
)]
#[OA\Schema(
    schema: "AdminResponse",
    type: "object",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Admin créé avec succès !"),
        new OA\Property(property: "data", ref: "#/components/schemas/Admin")
    ]
)]
#[OA\Schema(
    schema: "AdminCreateRequest",
    type: "object",
    required: ["nom", "prenom", "email", "telephone"],
    properties: [
        new OA\Property(property: "nom", type: "string", example: "Kilback"),
        new OA\Property(property: "prenom", type: "string", example: "Laury"),
        new OA\Property(property: "email", type: "string", format: "email", example: "brigitte.kuhn@example.net"),
        new OA\Property(property: "telephone", type: "string", example: "+1-201-736-0670")
    ]
)]
#[OA\Schema(
    schema: "LoginRequest",
    type: "object",
    required: ["email", "password"],
    properties: [
        new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
        new OA\Property(property: "password", type: "string", format: "password", example: "password")
    ]
)]
#[OA\Schema(
    schema: "AuthData",
    type: "object",
    properties: [
        new OA\Property(property: "user", ref: "#/components/schemas/User"),
        new OA\Property(
            property: "permissions",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["read_clients", "write_clients", "delete_clients"]
        ),
        new OA\Property(property: "token_type", type: "string", example: "Bearer"),
        new OA\Property(property: "access_token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
        new OA\Property(property: "expires_in", type: "integer", example: 3600)
    ]
)]
#[OA\Schema(
    schema: "AuthResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(property: "message", type: "string", example: "Connexion réussie"),
        new OA\Property(property: "data", ref: "#/components/schemas/AuthData")
    ]
)]
#[OA\Schema(
    schema: "User",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Admin User"),
        new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
        new OA\Property(property: "role", type: "string", enum: ["admin", "client"], example: "admin")
    ]
)]
#[OA\Schema(
    schema: "ErrorAuthResponse",
    type: "object",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: false),
        new OA\Property(property: "message", type: "string", example: "Identifiants invalides"),
        new OA\Property(property: "errors", type: "object", nullable: true, example: null),
        new OA\Property(property: "code", type: "integer", example: 401)
    ]
)]
#[OA\Post(
    path: "/guisse/v1/auth/login",
    operationId: "loginUser",
    summary: "Connexion utilisateur",
    description: "Authentifie un utilisateur et retourne un token d'accès avec les permissions",
    tags: ["Authentification"],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/LoginRequest")
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: "Connexion réussie",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Connexion réussie"),
                    new OA\Property(
                        property: "data",
                        type: "object",
                        properties: [
                            new OA\Property(property: "user", ref: "#/components/schemas/User"),
                            new OA\Property(
                                property: "permissions",
                                type: "array",
                                items: new OA\Items(type: "string"),
                                example: ["read_clients", "write_clients", "delete_clients"]
                            ),
                            new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                            new OA\Property(property: "access_token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
                            new OA\Property(property: "expires_in", type: "integer", example: 3600)
                        ]
                    )
                ],
                type: "object"
            )
        ),
        new OA\Response(
            response: 401,
            description: "Identifiants invalides",
            content: new OA\JsonContent(ref: "#/components/schemas/ErrorAuthResponse")
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
#[OA\Post(
    path: "/guisse/v1/auth/refresh",
    operationId: "refreshToken",
    summary: "Rafraîchir le token d'accès",
    description: "Utilise le refresh token pour obtenir un nouveau token d'accès",
    tags: ["Authentification"],
    security: [["bearerAuth" => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: "Token rafraîchi",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Token rafraîchi"),
                    new OA\Property(
                        property: "data",
                        type: "object",
                        properties: [
                            new OA\Property(property: "user", ref: "#/components/schemas/User"),
                            new OA\Property(
                                property: "permissions",
                                type: "array",
                                items: new OA\Items(type: "string"),
                                example: ["read_clients", "write_clients", "delete_clients"]
                            ),
                            new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                            new OA\Property(property: "expires_in", type: "integer", example: 3600)
                        ]
                    )
                ],
                type: "object"
            )
        ),
        new OA\Response(
            response: 401,
            description: "Refresh token invalide",
            content: new OA\JsonContent(ref: "#/components/schemas/ErrorAuthResponse")
        ),
        new OA\Response(
            response: 500,
            description: "Erreur serveur",
            content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
        )
    ]
)]
#[OA\Post(
    path: "/guisse/v1/auth/logout",
    operationId: "logoutUser",
    summary: "Déconnexion utilisateur",
    description: "Invalide les tokens de l'utilisateur connecté",
    tags: ["Authentification"],
    security: [["bearerAuth" => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: "Déconnexion réussie",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Déconnexion réussie"),
                    new OA\Property(property: "data", type: "object", nullable: true, example: null)
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
            response: 500,
            description: "Erreur serveur",
            content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
        )
    ]
)]
#[OA\Components(
    securitySchemes: [
        new OA\SecurityScheme(
            securityScheme: "bearerAuth",
            type: "http",
            scheme: "bearer",
            bearerFormat: "JWT"
        )
    ]
)]
class OpenApiDocumentation {}