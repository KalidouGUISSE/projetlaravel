<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compte;
use OpenApi\Attributes as OA;

class CompteController extends Controller
{
    #[OA\Get(
        path: "/comptes",
        summary: "Lister les comptes",
        description: "Retourne la liste complète des comptes avec leurs clients associés .",
        tags: ["Comptes"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des comptes",
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
    public function index(Request $request)
    {
        $comptes = Compte::with('client')->get();

        return response()->json([
            'status' => 'success',
            'data' => $comptes
        ]);
    }
}
