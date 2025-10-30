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
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        if ($user->role !== $role) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Permissions insuffisantes pour cette opération'
            ], 403);
        }

        // Ajouter les permissions de l'utilisateur à la requête
        $permissions = $this->getPermissionsForRole($user->role);
        $request->merge(['user_permissions' => $permissions]);

        return $next($request);
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