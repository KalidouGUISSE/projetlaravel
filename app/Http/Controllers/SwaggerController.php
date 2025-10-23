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