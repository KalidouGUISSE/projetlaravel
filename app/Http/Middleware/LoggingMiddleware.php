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
        $user = auth('api')->user();

        // Log de début d'opération
        Log::info("Opération de modification", [
            'date_heure' => $startTime->toISOString(),
            'host' => $host,
            'nom_operation' => $operation,
            'ressource' => $resource,
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
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
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
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
            DB::table('operation_logs')->insert([
                'user_id' => $user ? $user->id : null,
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
}