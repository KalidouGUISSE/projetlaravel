<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Compte;
use App\Models\Client;
use App\Jobs\BloquerComptesProgrammes;
use Illuminate\Support\Facades\Queue;

class BloquerComptesProgrammesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_accounts_when_start_date_has_arrived()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte avec une date de début de blocage dans le passé
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif',
            'date_debut_blocage' => now()->subDays(1), // Date dans le passé
            'date_fin_blocage' => now()->addDays(30),
            'motifBlocage' => 'Blocage programmé'
        ]);

        // Créer un autre compte avec date future (ne doit pas être bloqué)
        $compteFuture = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif',
            'date_debut_blocage' => now()->addDays(1), // Date dans le futur
            'date_fin_blocage' => now()->addDays(31),
            'motifBlocage' => 'Blocage futur'
        ]);

        // Exécuter le job
        $job = new BloquerComptesProgrammes();
        $job->handle();

        // Vérifier que le premier compte est bloqué
        $compte->refresh();
        $this->assertEquals('bloque', $compte->statut);
        $this->assertArrayHasKey('blocageAutomatique', $compte->metadata);
        $this->assertTrue($compte->metadata['blocageAutomatique']);

        // Vérifier que le deuxième compte n'est pas bloqué
        $compteFuture->refresh();
        $this->assertEquals('actif', $compteFuture->statut);
    }

    /** @test */
    public function it_only_blocks_epargne_accounts()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte épargne avec date passée
        $compteEpargne = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif',
            'date_debut_blocage' => now()->subDays(1),
            'date_fin_blocage' => now()->addDays(30),
            'motifBlocage' => 'Blocage programmé'
        ]);

        // Créer un compte chèque avec date passée (ne doit pas être bloqué)
        $compteCheque = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'cheque',
            'statut' => 'actif',
            'date_debut_blocage' => now()->subDays(1),
            'date_fin_blocage' => now()->addDays(30),
            'motifBlocage' => 'Blocage programmé'
        ]);

        // Exécuter le job
        $job = new BloquerComptesProgrammes();
        $job->handle();

        // Vérifier que seul le compte épargne est bloqué
        $compteEpargne->refresh();
        $this->assertEquals('bloque', $compteEpargne->statut);

        $compteCheque->refresh();
        $this->assertEquals('actif', $compteCheque->statut);
    }

    /** @test */
    public function it_does_not_block_already_blocked_accounts()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte déjà bloqué avec date passée
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque', // Déjà bloqué
            'date_debut_blocage' => now()->subDays(1),
            'date_fin_blocage' => now()->addDays(30),
            'motifBlocage' => 'Blocage programmé'
        ]);

        // Exécuter le job
        $job = new BloquerComptesProgrammes();
        $job->handle();

        // Vérifier que le compte reste bloqué (pas de double blocage)
        $compte->refresh();
        $this->assertEquals('bloque', $compte->statut);
    }

    /** @test */
    public function it_updates_metadata_on_automatic_block()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte avec date passée
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif',
            'date_debut_blocage' => now()->subDays(1),
            'date_fin_blocage' => now()->addDays(30),
            'motifBlocage' => 'Blocage programmé',
            'metadata' => [
                'version' => 1,
                'derniereModification' => now()->subDays(2)
            ]
        ]);

        // Exécuter le job
        $job = new BloquerComptesProgrammes();
        $job->handle();

        // Vérifier les metadata
        $compte->refresh();
        $this->assertEquals('bloque', $compte->statut);
        $this->assertArrayHasKey('blocageAutomatique', $compte->metadata);
        $this->assertArrayHasKey('dateBlocageAutomatique', $compte->metadata);
        $this->assertTrue($compte->metadata['blocageAutomatique']);
        $this->assertEquals(2, $compte->metadata['version']);
    }
}
