<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Client;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les clients existants
        $clients = Client::all();

        if ($clients->isEmpty()) {
            // Si aucun client n'existe, créer quelques clients par défaut
            $clients = collect([
                Client::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'nom' => 'Diop',
                    'prenom' => 'Amadou',
                    'email' => 'amadou.diop@example.com',
                    'telephone' => '771234567',
                    'adresse' => 'Dakar, Sénégal',
                    'nci' => '1234567890123'
                ]),
                Client::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'nom' => 'Sarr',
                    'prenom' => 'Fatou',
                    'email' => 'fatou.sarr@example.com',
                    'telephone' => '772345678',
                    'adresse' => 'Thiès, Sénégal',
                    'nci' => '1234567890124'
                ]),
                Client::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'nom' => 'Ndiaye',
                    'prenom' => 'Moussa',
                    'email' => 'moussa.ndiaye@example.com',
                    'telephone' => '773456789',
                    'adresse' => 'Saint-Louis, Sénégal',
                    'nci' => '1234567890125'
                ])
            ]);
        }

        // Créer des comptes pour chaque client
        foreach ($clients as $client) {
            // Créer 1-3 comptes par client
            $nombreComptes = rand(1, 3);

            for ($i = 0; $i < $nombreComptes; $i++) {
                $type = $i % 2 === 0 ? 'epargne' : 'cheque';
                $solde = rand(10000, 1000000); // Solde entre 10000 et 1000000 FCFA

                // Créer quelques comptes avec différents statuts pour les tests
                $statuts = ['actif', 'actif', 'actif', 'bloque', 'ferme']; // Plus de comptes actifs
                $statut = $statuts[array_rand($statuts)];

                $compteData = [
                    'client_id' => $client->id,
                    'type' => $type,
                    'solde' => $solde,
                    'statut' => $statut,
                    'metadata' => [
                        'dateCreation' => now(),
                        'source' => 'seeder',
                        'version' => 1
                    ]
                ];

                // Ajouter les informations de blocage pour les comptes bloqués
                if ($statut === 'bloque') {
                    $compteData['motifBlocage'] = 'Blocage automatique pour test';
                    $compteData['date_debut_blocage'] = now()->subDays(rand(1, 30));
                    $compteData['date_fin_blocage'] = now()->addDays(rand(1, 60));
                }

                Compte::create($compteData);
            }
        }

        $this->command->info('Comptes créés avec succès pour ' . $clients->count() . ' clients');
    }
}
