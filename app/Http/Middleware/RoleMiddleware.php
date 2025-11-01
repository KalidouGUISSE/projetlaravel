<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Récupérer l'utilisateur depuis notre middleware personnalisé
        $user = $request->get('authenticated_user');

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // Déterminer le rôle réel de l'utilisateur
        $userRole = $this->getUserRole($user);

        if ($userRole !== $role) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Permissions insuffisantes pour cette opération'
            ], 403);
        }

        // Ajouter les permissions de l'utilisateur à la requête
        $permissions = $this->getPermissionsForRole($userRole);
        $request->merge(['user_permissions' => $permissions]);

        return $next($request);
    }

    /**
     * Détermine le rôle réel de l'utilisateur en vérifiant les tables User, Admin et Client
     */
    private function getUserRole($user): string
    {
        // Vérifier d'abord si c'est un admin (UUID)
        $admin = \App\Models\Admin::where('id', $user->id)->first();
        if ($admin) {
            return 'admin';
        }

        // Vérifier si c'est un client (UUID)
        $client = \App\Models\Client::where('id', $user->id)->first();
        if ($client) {
            return 'client';
        }

        // Sinon, c'est un user (ID numérique ou string)
        $userRecord = \App\Models\User::where('id', $user->id)->first();
        if ($userRecord) {
            return $userRecord->role ?? 'client';
        }

        // Par défaut, considérer comme client
        return 'client';
    }

    /**
     * Récupère les permissions pour un rôle donné
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