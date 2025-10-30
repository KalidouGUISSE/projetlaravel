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

            // Convertir en array pour modification
            $json = json_decode(json_encode($openapi), true);

            // Ajouter les serveurs manuellement si non présents
            if (!isset($json['servers']) || !is_array($json['servers']) || count($json['servers']) === 0) {
                $json['servers'] = [
                    [
                        'url' => url('/guisse/v1'),
                        'description' => 'Serveur de développement'
                    ]
                ];
            } else {
                // Mettre à jour l'URL du serveur de manière dynamique
                $json['servers'][0]['url'] = url('/');
            }

            return response()->json($json);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ui()
    {
        return view('swagger-ui');
    }
}