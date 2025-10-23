<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    // public function run(): void
    // {
    //     // \App\Models\User::factory(10)->create();

    //     // \App\Models\User::factory()->create([
    //     //     'name' => 'Test User',
    //     //     'email' => 'test@example.com',
    //     // ]);

    //     \App\Models\Client::factory(5)
    //         ->has(\App\Models\Compte::factory(2)
    //             ->has(\App\Models\Transaction::factory(3), 'transactions')
    //         , 'comptes')
    //     ->create();
    

    // }

        /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([
        //     ClientSeeder::class,
        // ]);
        
        // Créer 10 clients avec 2 comptes chacun et 5 transactions par compte
        Client::factory()
            ->count(10)
            ->has(
                Compte::factory()
                    ->count(2)
                    ->has(Transaction::factory()->count(5), 'transactions'),
                'comptes'
            )
            ->create();
    }
}
