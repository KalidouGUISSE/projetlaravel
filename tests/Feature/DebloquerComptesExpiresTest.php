<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Compte;
use App\Models\Client;
use App\Jobs\DebloquerComptesExpires;

class DebloquerComptesExpiresTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_unblocks_accounts_when_end_date_has_passed()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte bloqué avec une date de fin de blocage dans le passé
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque',
            'date_debut_blocage' => now()->subDays(30),
            'date_fin_blocage' => now()->subDays(1), // Date dans le passé
            'motifBlocage' => 'Blocage expiré'
        ]);

        // Créer un autre compte bloqué avec date future (ne doit pas être débloqué)
        $compteFuture = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque',
            'date_debut_blocage' => now()->subDays(10),
            'date_fin_blocage' => now()->addDays(20), // Date dans le futur
            'motifBlocage' => 'Blocage actif'
        ]);

        // Exécuter le job
        $job = new DebloquerComptesExpires();
        $job->handle();

        // Vérifier que le premier compte est débloqué
        $compte->refresh();
        $this->assertEquals('actif', $compte->statut);
        $this->assertNull($compte->motifBlocage);
        $this->assertNull($compte->date_debut_blocage);
        $this->assertNull($compte->date_fin_blocage);
        $this->assertArrayHasKey('deblocageAutomatique', $compte->metadata);
        $this->assertTrue($compte->metadata['deblocageAutomatique']);

        // Vérifier que le deuxième compte reste bloqué
        $compteFuture->refresh();
        $this->assertEquals('bloque', $compteFuture->statut);
    }

    /** @test */
    public function it_only_unblocks_blocked_accounts()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte bloqué avec date passée
        $compteBloque = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque',
            'date_debut_blocage' => now()->subDays(30),
            'date_fin_blocage' => now()->subDays(1),
            'motifBlocage' => 'Blocage expiré'
        ]);

        // Créer un compte actif avec date passée (ne doit pas être modifié)
        $compteActif = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'actif',
            'date_debut_blocage' => now()->subDays(30),
            'date_fin_blocage' => now()->subDays(1),
            'motifBlocage' => 'Blocage expiré'
        ]);

        // Exécuter le job
        $job = new DebloquerComptesExpires();
        $job->handle();

        // Vérifier que seul le compte bloqué est débloqué
        $compteBloque->refresh();
        $this->assertEquals('actif', $compteBloque->statut);

        $compteActif->refresh();
        $this->assertEquals('actif', $compteActif->statut); // Reste actif
    }

    /** @test */
    public function it_updates_metadata_on_automatic_unblock()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte bloqué avec date passée
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque',
            'date_debut_blocage' => now()->subDays(30),
            'date_fin_blocage' => now()->subDays(1),
            'motifBlocage' => 'Blocage expiré',
            'metadata' => [
                'version' => 1,
                'derniereModification' => now()->subDays(2)
            ]
        ]);

        // Exécuter le job
        $job = new DebloquerComptesExpires();
        $job->handle();

        // Vérifier les metadata
        $compte->refresh();
        $this->assertEquals('actif', $compte->statut);
        $this->assertArrayHasKey('deblocageAutomatique', $compte->metadata);
        $this->assertArrayHasKey('dateDeblocageAutomatique', $compte->metadata);
        $this->assertTrue($compte->metadata['deblocageAutomatique']);
        $this->assertEquals(2, $compte->metadata['version']);
    }

    /** @test */
    public function it_handles_soft_deleted_accounts()
    {
        // Créer un client
        $client = Client::factory()->create();

        // Créer un compte soft deleted bloqué avec date passée
        $compte = Compte::factory()->create([
            'client_id' => $client->id,
            'type' => 'epargne',
            'statut' => 'bloque',
            'date_debut_blocage' => now()->subDays(30),
            'date_fin_blocage' => now()->subDays(1),
            'motifBlocage' => 'Blocage expiré'
        ]);

        // Soft delete le compte
        $compte->delete();

        // Exécuter le job
        $job = new DebloquerComptesExpires();
        $job->handle();

        // Vérifier que le compte soft deleted reste soft deleted et ferme (comportement du modèle)
        $compte->refresh();
        $this->assertTrue($compte->trashed());
        $this->assertEquals('ferme', $compte->statut); // Le modèle change automatiquement le statut à 'ferme' lors du soft delete
    }
}
