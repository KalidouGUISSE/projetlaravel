<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si les clients existent déjà
        if (DB::table('oauth_clients')->where('id', 1)->exists()) {
            return; // Les clients sont déjà créés par passport:install
        }

        // Créer un client OAuth pour l'application
        DB::table('oauth_clients')->insert([
            'id' => 1,
            'user_id' => null,
            'name' => 'Laravel Password Grant Client',
            'secret' => Hash::make('secret'),
            'provider' => null,
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un client personnel pour les tests
        DB::table('oauth_clients')->insert([
            'id' => 2,
            'user_id' => null,
            'name' => 'Laravel Personal Access Client',
            'secret' => Hash::make('secret'),
            'provider' => null,
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un utilisateur de test
        DB::table('users')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un admin de test
        DB::table('admins')->insert([
            'id' => (string) Str::uuid(),
            'nom' => 'Admin',
            'prenom' => 'Test',
            'email' => 'admin@test.com',
            'telephone' => '+221771234567',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un client de test
        DB::table('clients')->insert([
            'id' => (string) Str::uuid(),
            'nom' => 'Client',
            'prenom' => 'Test',
            'titulaire' => 'Client Test',
            'email' => 'client@test.com',
            'telephone' => '+221771234568',
            'nci' => '1234567890123',
            'adresse' => 'Dakar, Sénégal',
            'password' => Hash::make('password'),
            'code' => '123456',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}