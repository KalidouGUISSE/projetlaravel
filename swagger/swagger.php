<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Tutoriel",
    version: "0.1",
    description: "Documentation de mon API Laravel pour la gestion des clients et comptes"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000/api",
    description: "Serveur local de développement"
)]
#[OA\Schema(
    schema: "Client",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "string", format: "uuid", example: "123e4567-e89b-12d3-a456-426614174000"),
        new OA\Property(property: "nom", type: "string", example: "Doe"),
        new OA\Property(property: "prenom", type: "string", example: "John"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
        new OA\Property(property: "telephone", type: "string", example: "+221 77 123 45 67"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
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
class OpenApiDocumentation {}
