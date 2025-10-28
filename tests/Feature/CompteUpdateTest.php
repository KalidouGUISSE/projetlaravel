<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Compte;
use App\Models\Client;

class CompteUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_update_compte_type()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'cheque'
        ]);

        $response = $this->putJson("guisse/v1/comptes/{$compte->id}", [
            'type' => 'epargne'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Compte mis à jour avec succès'
                ]);

        $this->assertDatabaseHas('comptes', [
            'id' => $compte->id,
            'type' => 'epargne'
        ]);
    }

    /** @test */
    public function it_can_update_compte_solde()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'solde' => 100000
        ]);

        $response = $this->putJson("guisse/v1/comptes/{$compte->id}", [
            'solde' => 200000
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('comptes', [
            'id' => $compte->id,
            'solde' => 200000
        ]);
    }

    /** @test */
    public function it_can_update_compte_statut()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'statut' => 'actif'
        ]);

        $response = $this->putJson("guisse/v1/comptes/{$compte->id}", [
            'statut' => 'bloque'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('comptes', [
            'id' => $compte->id,
            'statut' => 'bloque'
        ]);
    }

    /** @test */
    public function it_can_update_client_information()
    {
        $client = Client::factory()->create([
            'titulaire' => 'Old Name',
            'email' => 'old@example.com'
        ]);
        $compte = Compte::factory()->create(['client_id' => $client->id]);

        $response = $this->putJson("guisse/v1/comptes/{$compte->id}", [
            'client' => [
                'id' => $client->id,
                'titulaire' => 'New Name',
                'email' => 'new@example.com'
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'titulaire' => 'New Name',
            'email' => 'new@example.com'
        ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_compte()
    {
        $response = $this->putJson('guisse/v1/comptes/' . fake()->uuid(), [
            'type' => 'epargne'
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Compte non trouvé'
                ]);
    }

    /** @test */
    public function it_validates_update_data()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['client_id' => $client->id]);

        $response = $this->putJson("guisse/v1/comptes/{$compte->id}", [
            'type' => 'invalid_type'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_updates_metadata_on_modification()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['client_id' => $client->id]);

        $this->putJson("guisse/v1/comptes/{$compte->id}", [
            'type' => 'epargne'
        ]);

        $compte->refresh();
        $this->assertArrayHasKey('derniereModification', $compte->metadata);
        $this->assertEquals(2, $compte->metadata['version']);
    }
}
