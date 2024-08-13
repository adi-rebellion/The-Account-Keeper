<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trans_user_id' => User::factory(), // Create a new user for each transaction
            'trans_date' => $this->faker->date(),
            'trans_amount' => $this->faker->randomFloat(2, 1, 1000), // Random amount between 1 and 1000
            'trans_type' => $this->faker->randomElement(['credit', 'debit']),
            'category_id' => null, // You can customize this if you have categories
            'description' => $this->faker->sentence(),
        ];
    }
}
