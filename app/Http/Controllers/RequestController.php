<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class RequestController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle the request and return a response.
     */
    public function handle(Request $request)
    {
        // Récupérer les données de la requête
        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'input' => $request->all(),
            'query' => $request->query(),
        ];

        // Retourner une réponse JSON
        return response()->json([
            'success' => true,
            'message' => 'Requête reçue avec succès.',
            'data' => $data,
        ], 200);
    }
}
