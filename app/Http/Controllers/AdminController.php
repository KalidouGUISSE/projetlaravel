<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\Compte;
use App\Http\Requests\StoreAdminRequest;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    #[OA\Get(
        path: "/admins/comptes",
        summary: "Lister tous les comptes (Admin)",
        description: "Retourne la liste de tous les comptes avec leurs clients associés.",
        tags: ["Admins"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des comptes",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/CompteResponse")
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
    /**
     * Admin peut récupérer la liste de tous les comptes
     */
    public function getAllComptes()
    {
        $comptes = Compte::with('client')->get();

        return response()->json([
            'status' => 'success',
            'data' => $comptes
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
            'data'    => $admin,
        ], 201);
    }
}
