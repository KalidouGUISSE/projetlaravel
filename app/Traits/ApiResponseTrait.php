<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a successful response
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Return a paginated response with consistent structure
     */
    protected function paginatedResponse($query, array $filters = [], array $sortOptions = []): JsonResponse
    {
        $page = request('page', 1);
        $limit = min(request('limit', 10), 100); // Max 100 items per page

        // Apply filters
        if (isset($filters['type']) && $filters['type']) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['statut']) && $filters['statut']) {
            $query->where('statut', $filters['statut']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('numeroCompte', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%")
                                  ->orWhere('prenom', 'like', "%{$search}%");
                  });
            });
        }

        // Apply sorting
        $sort = request('sort', 'created_at');
        $order = request('order', 'desc');

        $allowedSorts = ['created_at', 'solde', 'numeroCompte'];
        if (in_array($sort, $allowedSorts)) {
            if ($sort === 'solde') {
                // For solde, we need to calculate from transactions
                $query->withSum(['transactions' => function ($q) {
                    $q->where('statut', 'validee');
                }], 'montant');
                $query->orderBy('transactions_sum_montant', $order);
            } else {
                $query->orderBy($sort, $order);
            }
        }

        // Paginate
        $paginated = $query->paginate($limit, ['*'], 'page', $page);

        // Transform data to include calculated solde
        $data = $paginated->getCollection()->map(function ($item) {
            // Calculate solde from validated transactions
            $debits = $item->transactions()->where('statut', 'validee')
                          ->whereIn('type', ['retrait', 'virement', 'frais'])
                          ->sum('montant');
            $credits = $item->transactions()->where('statut', 'validee')
                           ->where('type', 'depot')
                           ->sum('montant');
            $solde = $credits - $debits;

            return [
                'id' => $item->id,
                'numeroCompte' => $item->numeroCompte,
                'titulaire' => $item->client ? $item->client->nom . ' ' . $item->client->prenom : null,
                'type' => $item->type,
                'solde' => $solde,
                'devise' => 'FCFA', // Assuming XOF is FCFA
                'dateCreation' => $item->created_at->toISOString(),
                'statut' => $item->statut,
                'motifBlocage' => $item->statut === 'bloque' ? 'Inactivité de 30+ jours' : null,
                'metadata' => $item->metadata,
            ];
        });

        // Build pagination links
        $links = [
            'self' => $paginated->url($paginated->currentPage()),
            'first' => $paginated->url(1),
            'last' => $paginated->url($paginated->lastPage()),
        ];

        if ($paginated->hasMorePages()) {
            $links['next'] = $paginated->nextPageUrl();
        }

        if ($paginated->currentPage() > 1) {
            $links['previous'] = $paginated->previousPageUrl();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'currentPage' => $paginated->currentPage(),
                'totalPages' => $paginated->lastPage(),
                'totalItems' => $paginated->total(),
                'hasNext' => $paginated->hasMorePages(),
                'hasPrevious' => $paginated->currentPage() > 1,
            ],
            'links' => $links,
        ], 200);
    }
}
