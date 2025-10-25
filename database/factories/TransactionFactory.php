<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = \App\Models\Transaction::class;
    
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'compte_id' => \App\Models\Compte::factory(),
            'type' => $this->faker->randomElement(['depot','retrait','virement','frais']),
            'montant' => $this->faker->randomFloat(2, 10, 5000),
            'devise' => 'XOF',
            'description' => $this->faker->sentence(),
            'dateTransaction' => $this->faker->dateTimeThisYear(),
            'statut' => $this->faker->randomElement(['en_attente','validee','annulee']),
        ];
    }
}
