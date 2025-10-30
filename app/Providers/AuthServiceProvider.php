<?php

namespace App\Providers;
use Laravel\Passport\Passport;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Configuration Passport
        Passport::tokensExpireIn(now()->addHours(1)); // Tokens expirent en 1 heure
        Passport::refreshTokensExpireIn(now()->addDays(7)); // Refresh tokens expirent en 7 jours

        // Définir les scopes disponibles
        Passport::tokensCan([
            'read' => 'Lire les données',
            'write' => 'Écrire les données',
            'delete' => 'Supprimer les données',
            'manage-users' => 'Gérer les utilisateurs',
            'read-own' => 'Lire ses propres données',
            'write-own' => 'Écrire ses propres données',
            'refresh' => 'Rafraîchir les tokens',
        ]);

        // Utiliser des claims personnalisés pour inclure le rôle
        Passport::setDefaultScope(['read-own']);
    }
}


// Configuration Passport déplacée dans la méthode boot()
