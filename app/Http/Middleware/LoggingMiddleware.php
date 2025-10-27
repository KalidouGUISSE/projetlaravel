<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        Log::info("Opération de modification", [
            'date_heure' => $startTime->toISOString(),
            'host' => $host,
            'nom_operation' => $operation,
            'ressource' => $resource,
        ]);

        $response = $next($request);

        $endTime = now();
        Log::info("Opération terminée", [
            'date_heure' => $endTime->toISOString(),
            'host' => $host,
            'nom_operation' => $operation,
            'ressource' => $resource,
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}