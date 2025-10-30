<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

class AddRoleToToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ajouter le rôle de l'utilisateur aux claims du token si c'est une réponse de création de token
        if ($response->getStatusCode() === 200 && $request->isMethod('post')) {
            $user = auth('api')->user();
            if ($user) {
                // Ici nous pourrions modifier la réponse pour inclure des claims personnalisés
                // Mais Passport gère cela automatiquement via les scopes
            }
        }

        return $response;
    }
}