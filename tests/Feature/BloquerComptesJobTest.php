<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\Models\Compte;
use App\Models\Client;
use App\Jobs\BloquerComptesJob;

class BloquerComptesJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_dispatch_block_job_for_multiple_accounts()
    {
        Queue::fake();

        $client = Client::factory()->create();
        $comptes = Compte::factory()->count(3)->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif'
        ]);

        $compteIds = $comptes->pluck('id')->toArray();

        $response = $this->postJson("guisse/v1/comptes/bloquer-job", [
            'compte_ids' => $compteIds,
            'motif' => 'Blocage massif pour maintenance',
            'duree' => 7,
            'unite' => 'jours'
        ]);

        $response->assertStatus(202)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Job de blocage lancé avec succès'
                 ]);

        Queue::assertPushed(BloquerComptesJob::class);
    }

    /** @test */
    public function it_validates_job_request_data()
    {
        $response = $this->postJson("guisse/v1/comptes/bloquer-job", [
            'compte_ids' => [],
            'motif' => '',
            'duree' => 0,
            'unite' => 'invalid'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_executes_block_job_correctly()
    {
        $client = Client::factory()->create();
        $comptes = Compte::factory()->count(2)->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif'
        ]);

        $compteIds = $comptes->pluck('id')->toArray();

        // Exécuter le job directement
        $job = new BloquerComptesJob($compteIds, 'Test job', 5, 'jours');
        $job->handle();

        // Vérifier que les comptes sont bloqués
        foreach ($comptes as $compte) {
            $compte->refresh();
            $this->assertEquals('bloque', $compte->statut);
            $this->assertEquals('Test job', $compte->motifBlocage);
            $this->assertNotNull($compte->date_debut_blocage);
            $this->assertNotNull($compte->date_fin_blocage);
        }
    }

    /** @test */
    public function it_skips_invalid_accounts_in_job()
    {
        $client = Client::factory()->create();

        // Créer un compte valide
        $validCompte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif'
        ]);

        // Créer un compte déjà bloqué
        $blockedCompte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque'
        ]);

        // Créer un compte de type cheque
        $chequeCompte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'cheque',
            'statut' => 'actif'
        ]);

        $compteIds = [$validCompte->id, $blockedCompte->id, $chequeCompte->id, fake()->uuid()];

        // Exécuter le job
        $job = new BloquerComptesJob($compteIds, 'Test job', 5, 'jours');
        $job->handle();

        // Vérifier que seul le compte valide est bloqué
        $validCompte->refresh();
        $this->assertEquals('bloque', $validCompte->statut);

        $blockedCompte->refresh();
        $this->assertEquals('bloque', $blockedCompte->statut); // Déjà bloqué

        $chequeCompte->refresh();
        $this->assertEquals('actif', $chequeCompte->statut); // Non épargne
    }
}
