<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Generator;

class SwaggerController extends Controller
{
    public function json()
    {
        try {
            $openapi = Generator::scan([app_path('Http/Controllers'), app_path('Swagger')]);
            // Mettre à jour l'URL du serveur de manière dynamique depuis les variables d'environnement
            if (isset($openapi->servers) && count($openapi->servers) > 0) {
                $openapi->servers[0]->url = '/api/v1';
            }
            return response()->json($openapi);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ui()
    {
        return view('swagger-ui');
    }
}