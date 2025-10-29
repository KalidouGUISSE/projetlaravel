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
    public function it_can_delete_an_active_account()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'statut' => 'actif'
        ]);

        $response = $this->deleteJson("guisse/v1/comptes/{$compte->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Compte supprimé avec succès'
                 ]);

        $this->assertDatabaseHas('comptes', [
            'id' => $compte->id,
            'statut' => 'ferme'
        ]);

        $this->assertSoftDeleted('comptes', [
            'id' => $compte->id
        ]);
    }

    /** @test */
    public function it_cannot_delete_a_non_active_account()
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'statut' => 'bloque'
        ]);

        $response = $this->deleteJson("guisse/v1/comptes/{$compte->id}");

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Seul un compte actif peut être supprimé.'
                 ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_account_on_delete()
    {
        $response = $this->deleteJson('guisse/v1/comptes/' . fake()->uuid());

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Compte non trouvé'
                 ]);
    }
}
