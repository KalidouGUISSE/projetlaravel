<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Laravel\Passport\Client;
use App\Models\User;
use App\Models\Admin;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Connexion utilisateur (User, Admin ou Client)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Essayer d'abord avec User
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return $this->createAuthResponse($user, 'user');
        }

        // Si pas trouvé dans User, essayer avec Admin
        $admin = Admin::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            return $this->createAuthResponse($admin, 'admin');
        }

        // Si pas trouvé dans Admin, essayer avec Client
        $client = \App\Models\Client::where('email', $request->email)->first();

        if ($client && Hash::check($request->password, $client->password)) {
            return $this->createAuthResponse($client, 'client');
        }

        return $this->errorResponse('Identifiants invalides', 401);
    }

    /**
     * Créer la réponse d'authentification
     */
    private function createAuthResponse($entity, string $type)
    {
        // Créer le token avec scopes basés sur le rôle/type
        $role = $this->getRoleForEntity($entity, $type);
        $scopes = $this->getScopesForRole($role);
        $token = $entity->createToken('API Token', $scopes);

        // Créer le refresh token
        $refreshToken = $entity->createToken('Refresh Token', ['refresh']);

        // Stocker les tokens dans les cookies
        $accessTokenCookie = cookie('access_token', $token->accessToken, 60, '/', null, true, true); // 1 heure
        $refreshTokenCookie = cookie('refresh_token', $refreshToken->accessToken, 60 * 24 * 7, '/', null, true, true); // 7 jours

        $userData = $this->getUserDataForEntity($entity, $type);

        return $this->successResponse([
            'user' => $userData,
            'permissions' => $this->getPermissionsForRole($role),
            'token_type' => 'Bearer',
            'access_token' => $token->accessToken,
            'expires_in' => 3600, // 1 heure
        ], 'Connexion réussie')->withCookie($accessTokenCookie)->withCookie($refreshTokenCookie);
    }

    /**
     * Rafraîchir le token d'accès
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return $this->errorResponse('Refresh token manquant', 401);
        }

        // Vérifier et décoder le refresh token
        $token = \Laravel\Passport\Token::findToken($refreshToken);

        if (!$token || $token->revoked || $token->expires_at < now()) {
            return $this->errorResponse('Refresh token invalide', 401);
        }

        $user = $token->user;

        // Révoquer l'ancien token
        $token->revoke();

        // Créer de nouveaux tokens
        $scopes = $this->getScopesForRole($user->role);
        $newToken = $user->createToken('API Token', $scopes);
        $newRefreshToken = $user->createToken('Refresh Token', ['refresh']);

        // Mettre à jour les cookies
        $accessTokenCookie = cookie('access_token', $newToken->accessToken, 60, '/', null, true, true);
        $refreshTokenCookie = cookie('refresh_token', $newRefreshToken->accessToken, 60 * 24 * 7, '/', null, true, true);

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'permissions' => $this->getPermissionsForRole($user->role),
            'token_type' => 'Bearer',
            'access_token' => $newToken->accessToken,
            'expires_in' => 3600,
        ], 'Token rafraîchi')->withCookie($accessTokenCookie)->withCookie($refreshTokenCookie);
    }

    /**
     * Déconnexion utilisateur
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            // Révoquer tous les tokens de l'utilisateur
            $user->tokens->each(function ($token) {
                $token->revoke();
            });
        }

        // Supprimer les cookies
        $accessTokenCookie = Cookie::forget('access_token');
        $refreshTokenCookie = Cookie::forget('refresh_token');

        return $this->successResponse(null, 'Déconnexion réussie')
            ->withCookie($accessTokenCookie)
            ->withCookie($refreshTokenCookie);
    }

    /**
     * Obtenir les scopes pour un rôle
     */
    private function getScopesForRole(string $role): array
    {
        $scopes = [
            'admin' => ['read', 'write', 'delete', 'manage-users'],
            'client' => ['read-own', 'write-own'],
        ];

        return $scopes[$role] ?? [];
    }

    /**
     * Obtenir le rôle pour une entité donnée
     */
    private function getRoleForEntity($entity, string $type): string
    {
        if ($type === 'admin') {
            return 'admin';
        } elseif ($type === 'client') {
            return 'client';
        } else {
            return $entity->role ?? 'client';
        }
    }

    /**
     * Obtenir les données utilisateur pour une entité donnée
     */
    private function getUserDataForEntity($entity, string $type): array
    {
        if ($type === 'admin') {
            return [
                'id' => $entity->id,
                'name' => $entity->nom . ' ' . $entity->prenom,
                'email' => $entity->email,
                'role' => 'admin',
                'type' => 'admin'
            ];
        } elseif ($type === 'client') {
            return [
                'id' => $entity->id,
                'name' => $entity->nom . ' ' . $entity->prenom,
                'email' => $entity->email,
                'role' => 'client',
                'type' => 'client'
            ];
        } else {
            return [
                'id' => $entity->id,
                'name' => $entity->name,
                'email' => $entity->email,
                'role' => $entity->role,
                'type' => 'user'
            ];
        }
    }

    /**
     * Obtenir les permissions pour un rôle
     */
    private function getPermissionsForRole(string $role): array
    {
        $permissions = [
            'admin' => [
                'read_clients', 'write_clients', 'delete_clients',
                'read_comptes', 'write_comptes', 'delete_comptes',
                'block_comptes', 'unblock_comptes',
                'read_transactions', 'write_transactions',
                'manage_admins'
            ],
            'client' => [
                'read_own_comptes', 'read_own_transactions',
                'transfer_money'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}