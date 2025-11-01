<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier d'abord le token avec une approche personnalisée
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token d\'authentification manquant'
            ], 401);
        }

        // Décoder le JWT pour obtenir le JTI (token ID)
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token d\'authentification invalide'
            ], 401);
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        if (!$payload || !isset($payload['jti'])) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token d\'authentification invalide'
            ], 401);
        }

        $jti = $payload['jti'];

        // Récupérer le token depuis la base de données
        $accessToken = \Laravel\Passport\Token::where('id', $jti)->first();
        if (!$accessToken || $accessToken->revoked || $accessToken->expires_at < now()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token d\'authentification invalide ou expiré'
            ], 401);
        }

        // Récupérer l'utilisateur selon son type (UUID ou bigint)
        $user = $this->getUserByToken($accessToken);
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Utilisateur non trouvé dans le système'
            ], 401);
        }

        // Ajouter l'utilisateur à la requête pour les middlewares suivants
        $request->merge(['authenticated_user' => $user]);
        // Ne pas utiliser setUser car cela cause des problèmes de type
        // Au lieu de cela, nous utilisons notre propre logique dans les middlewares suivants

        return $next($request);
    }

    /**
     * Récupérer l'utilisateur selon le token Passport
     */
    private function getUserByToken($accessToken)
    {
        $userId = $accessToken->user_id;

        // Essayer d'abord comme admin (UUID)
        $admin = \App\Models\Admin::where('id', $userId)->first();
        if ($admin) {
            return $admin;
        }

        // Essayer comme client (UUID)
        $client = \App\Models\Client::where('id', $userId)->first();
        if ($client) {
            return $client;
        }

        // Essayer comme user (bigint ou string)
        $user = \App\Models\User::where('id', $userId)->first();
        if ($user) {
            return $user;
        }

        return null;
    }
}