<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = now();
        $host = $request->getHost();
        $operation = $request->method();
        $resource = $request->path();
        $user = $request->get('authenticated_user');

        // Déterminer le rôle de l'utilisateur (admin ou user)
        $userRole = null;
        $userId = null;

        if ($user) {
            $userId = $user->id;
            // Vérifier manuellement dans les tables pour déterminer le rôle
            $userRole = $this->getUserRole($user);
        }

        // Log de début d'opération
        Log::info("Opération de modification", [
            'date_heure' => $startTime->toISOString(),
            'host' => $host,
            'nom_operation' => $operation,
            'ressource' => $resource,
            'user_id' => $userId,
            'user_role' => $userRole,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $response = $next($request);

        $endTime = now();

        // Log de fin d'opération avec plus de détails
        Log::info("Opération terminée", [
            'date_heure' => $endTime->toISOString(),
            'host' => $host,
            'nom_operation' => $operation,
            'ressource' => $resource,
            'status' => $response->getStatusCode(),
            'user_id' => $userId,
            'user_role' => $userRole,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration_ms' => $startTime->diffInMilliseconds($endTime),
        ]);

        // Stocker dans la base de données si c'est une opération importante
        if (in_array($operation, ['POST', 'PUT', 'DELETE']) && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->storeOperationLog($request, $response, $user);
        }

        return $response;
    }

    /**
     * Stocke le log d'opération dans la base de données
     */
    private function storeOperationLog(Request $request, Response $response, $user): void
    {
        try {
            // Déterminer si c'est un admin ou un user pour le stockage du user_id
            $userId = null;
            if ($user) {
                $userId = $user->id;
                // Pour les admins, on peut stocker directement l'UUID
                // Pour les users, on stocke l'ID (qui peut être string maintenant)
            }

            DB::table('operation_logs')->insert([
                'user_id' => $userId,
                'operation' => $request->method(),
                'resource' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => json_encode($request->all()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log l'erreur mais ne bloque pas la réponse
            Log::error("Erreur lors du stockage du log d'opération", [
                'error' => $e->getMessage(),
                'user_id' => $user ? $user->id : null,
                'operation' => $request->method(),
                'resource' => $request->path(),
            ]);
        }
    }

    /**
     * Détermine le rôle réel de l'utilisateur en vérifiant les tables User, Admin et Client
     */
    private function getUserRole($user): ?string
    {
        if (!$user) {
            return null;
        }

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
}