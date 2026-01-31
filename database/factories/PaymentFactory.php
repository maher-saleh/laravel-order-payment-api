<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'stripe']),
            'status' => fake()->randomElement([
                Payment::STATUS_PENDING,
                Payment::STATUS_SUCCESSFUL,
                Payment::STATUS_FAILED,
            ]),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'transaction_id' => fake()->unique()->uuid(),
            'gateway_response' => [
                'test' => true,
                'timestamp' => now()->timestamp,
            ],
        ];
    }



    /**
     * Indicate that the payment is successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_SUCCESSFUL,
            'transaction_id' => fake()->unique()->uuid(),
        ]);
    }



    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_FAILED,
            'transaction_id' => null,
        ]);
    }



    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_PENDING,
            'transaction_id' => null,
        ]);
    }
}