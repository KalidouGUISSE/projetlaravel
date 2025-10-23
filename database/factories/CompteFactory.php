<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Client;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    protected $model = \App\Models\Compte::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'numeroCompte' => $this->faker->unique()->bankAccountNumber,
            'client_id' => \App\Models\Client::factory(),
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 0, 10000),
            'statut' => 'actif',
            'metadata' => [
                'derniereModification' => now(),
                'version' => 1
            ],
        ];
    }
}
