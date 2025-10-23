<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Pour les API, ne pas rediriger, laisser Sanctum gérer
        if ($request->is('api/*')) {
            return null;
        }
        // Retourner null si pas d'API, pour éviter l'erreur
        return null;
    }

    /**
     * Handle an unauthenticated user.
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->is('api/*')) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthorized');
        }

        parent::unauthenticated($request, $guards);
    }
}
