<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compte;
use OpenApi\Attributes as OA;

class CompteController extends Controller
{
    #[OA\Get(
        path: "/v1/comptes",
        summary: "Lister les comptes du client connecté",
        description: "Retourne la liste des comptes du client authentifié.",
        tags: ["Comptes"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des comptes du client",
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
     * Client peut récupérer la liste de ses comptes
     */
    public function index(Request $request)
    {
        // Assumer que le client est authentifié et son ID est disponible via auth
        // Pour l'exemple, on utilise un client_id fictif ou via auth
        // En production, utiliser auth()->user()->client_id ou similaire
        $clientId = $request->input('client_id'); // Temporaire, à remplacer par auth

        if (!$clientId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client ID requis'
            ], 400);
        }

        $comptes = Compte::where('client_id', $clientId)->with('client')->get();

        return response()->json([
            'status' => 'success',
            'data' => $comptes
        ], 200);
    }
}
