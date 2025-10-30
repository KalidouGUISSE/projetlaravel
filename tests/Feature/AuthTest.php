<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Client;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un client OAuth pour les tests
        $this->client = Client::factory()->create([
            'password_client' => true,
            'personal_access_client' => false,
            'revoked' => false,
        ]);

        // Créer des utilisateurs de test
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'email' => 'client@example.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/guisse/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user' => ['id', 'name', 'email', 'role'],
                        'permissions',
                        'token_type',
                        'expires_in'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'user' => ['role' => 'admin'],
                        'token_type' => 'Bearer'
                    ]
                ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/guisse/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'error' => 'Unauthorized',
                    'message' => 'Identifiants invalides'
                ]);
    }

    /** @test */
    public function login_sets_cookies()
    {
        $response = $this->postJson('/guisse/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertCookie('access_token')
                ->assertCookie('refresh_token');
    }

    /** @test */
    public function authenticated_user_can_access_protected_routes()
    {
        // Login d'abord
        $loginResponse = $this->postJson('/guisse/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        // Tenter d'accéder à une route protégée
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/clients');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/clients');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_with_wrong_role_cannot_access_admin_routes()
    {
        // Login avec un utilisateur client
        $loginResponse = $this->postJson('/guisse/v1/auth/login', [
            'email' => 'client@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        // Tenter d'accéder à une route admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/clients');

        $response->assertStatus(403)
                ->assertJson([
                    'error' => 'Forbidden',
                    'message' => 'Permissions insuffisantes pour cette opération'
                ]);
    }

    /** @test */
    public function user_can_logout()
    {
        // Login d'abord
        $this->postJson('/guisse/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        // Logout
        $response = $this->postJson('/guisse/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'data' => null,
                    'message' => 'Déconnexion réussie'
                ])
                ->assertCookieExpired('access_token')
                ->assertCookieExpired('refresh_token');
    }

    /** @test */
    public function refresh_token_works()
    {
        // Login d'abord pour obtenir les cookies
        $this->postJson('/guisse/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        // Utiliser le refresh token
        $response = $this->postJson('/guisse/v1/auth/refresh');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user' => ['id', 'name', 'email', 'role'],
                        'permissions',
                        'token_type',
                        'expires_in'
                    ]
                ]);
    }
}
