<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Kalidou GUISSE",
    version: "0.1",
    description: "Documentation de mon API Laravel pour la gestion des clients et comptes"
)]
#[OA\Server(
    url: "https://projetlaravel-2.onrender.com/api/v1",
    description: "Serveur de production (dynamique via contrôleur)"
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
        new OA\Property(property: "client_id", type: "string", format: "uuid", example: "c7917097-4734-4dfd-a462-b6c8f8d4aea3"),
        new OA\Property(property: "numeroCompte", type: "string", example: "23886990999492"),
        new OA\Property(property: "type", type: "string", enum: ["epargne", "cheque"], example: "cheque"),
        new OA\Property(property: "solde", type: "number", format: "float", example: 1558.59),
        new OA\Property(property: "statut", type: "string", enum: ["actif", "bloque", "ferme"], example: "actif"),
        new OA\Property(
            property: "metadata",
            type: "object",
            properties: [
                new OA\Property(property: "derniereModification", type: "string", format: "date-time", example: "2025-10-22T11:37:37.082038Z"),
                new OA\Property(property: "version", type: "integer", example: 1)
            ]
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2025-10-22T11:37:37.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2025-10-22T11:37:37.000000Z"),
        new OA\Property(property: "client", ref: "#/components/schemas/Client")
    ]
)]
#[OA\Schema(
    schema: "CompteResponse",
    type: "object",
    properties: [
        new OA\Property(property: "status", type: "string", example: "success"),
        new OA\Property(
            property: "data",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/Compte")
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
class OpenApiDocumentation {}