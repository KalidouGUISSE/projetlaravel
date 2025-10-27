<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Tutoriel",
    version: "0.1",
    description: "Documentation de mon API Laravel pour la gestion des clients et comptes"
)]
#[OA\Server(
    url: "https://kalidou-guisse-projetlaravel.onrender.com",
    description: "Serveur de production"
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
#[OA\Put(
    path: "/clients/{id}",
    summary: "Mettre à jour un client",
    description: "Met à jour les informations d'un client existant. Tous les champs sont optionnels, mais au moins un doit être modifié.",
    security: [
        new OA\SecurityScheme(
            securityScheme: "bearerAuth",
            type: "http",
            scheme: "bearer"
        )
    ],
    tags: ["Clients"],
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
class OpenApiDocumentation {}
