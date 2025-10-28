<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Compte;
use App\Models\Client;

class CompteBlocageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_block_an_active_epargne_account()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif'
        ]);

        $response = $this->postJson("guisse/v1/comptes/{$compte->id}/bloquer", [
            'motif' => 'Activité suspecte détectée',
            'duree' => 30,
            'unite' => 'mois'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Compte bloqué avec succès'
                 ]);

        $this->assertDatabaseHas('comptes', [
            'id' => $compte->id,
            'statut' => 'bloque',
            'motifBlocage' => 'Activité suspecte détectée'
        ]);

        $compte->refresh();
        $this->assertNotNull($compte->date_debut_blocage);
        $this->assertNotNull($compte->date_fin_blocage);
    }

    /** @test */
    public function it_cannot_block_a_non_epargne_account()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'cheque',
            'statut' => 'actif'
        ]);

        $response = $this->postJson("guisse/v1/comptes/{$compte->id}/bloquer", [
            'motif' => 'Activité suspecte détectée',
            'duree' => 30,
            'unite' => 'mois'
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Seul un compte épargne peut être bloqué.'
                 ]);
    }

    /** @test */
    public function it_cannot_block_an_already_blocked_account()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque'
        ]);

        $response = $this->postJson("guisse/v1/comptes/{$compte->id}/bloquer", [
            'motif' => 'Activité suspecte détectée',
            'duree' => 30,
            'unite' => 'mois'
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Seul un compte actif peut être bloqué.'
                 ]);
    }

    /** @test */
    public function it_can_unblock_a_blocked_account()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque',
            'motifBlocage' => 'Activité suspecte',
            'date_debut_blocage' => now(),
            'date_fin_blocage' => now()->addDays(30)
        ]);

        $response = $this->postJson("guisse/v1/comptes/{$compte->id}/debloquer", [
            'motif' => 'Vérification complétée'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Compte débloqué avec succès'
                 ]);

        $this->assertDatabaseHas('comptes', [
            'id' => $compte->id,
            'statut' => 'actif',
            'motifBlocage' => null,
            'date_debut_blocage' => null,
            'date_fin_blocage' => null
        ]);
    }

    /** @test */
    public function it_cannot_unblock_an_active_account()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif'
        ]);

        $response = $this->postJson("guisse/v1/comptes/{$compte->id}/debloquer", [
            'motif' => 'Vérification complétée'
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Seul un compte bloqué peut être débloqué.'
                 ]);
    }

    /** @test */
    public function it_validates_block_request_data()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif'
        ]);

        $response = $this->postJson("guisse/v1/comptes/{$compte->id}/bloquer", [
            'motif' => '',
            'duree' => 0,
            'unite' => 'invalid'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_unblock_request_data()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque'
        ]);

        $response = $this->postJson("guisse/v1/comptes/{$compte->id}/debloquer", [
            'motif' => ''
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_account_on_block()
    {
        $response = $this->postJson('guisse/v1/comptes/' . fake()->uuid() . '/bloquer', [
            'motif' => 'Activité suspecte détectée',
            'duree' => 30,
            'unite' => 'mois'
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Compte non trouvé'
                 ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_account_on_unblock()
    {
        $response = $this->postJson('guisse/v1/comptes/' . fake()->uuid() . '/debloquer', [
            'motif' => 'Vérification complétée'
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Compte non trouvé'
                 ]);
    }
}
