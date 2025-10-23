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
                $baseUrl = env('APP_ENV') === 'local' ? env('APP_URL') : env('APP_URL_PROD');
                // Forcer HTTPS en production pour éviter les problèmes de contenu mixte
                if (env('APP_ENV') !== 'local' || str_contains($baseUrl, 'render.com')) {
                    $baseUrl = str_replace('http://', 'https://', $baseUrl);
                }
                $openapi->servers[0]->url = $baseUrl . '/api/v1';
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