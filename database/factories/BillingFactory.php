<?php

namespace Database\Factories;

use App\Enums\BillingStatus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Billing>
 */
class BillingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id'     => Client::factory(),
            'concepto'      => fake()->sentence(3),
            'monto'         => fake()->randomFloat(2, 100, 50000),
            'fecha_emision' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'fecha_pago'    => null,
            'estado'        => 'pendiente',
        ];
    }

    public function pagado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado'     => 'pagado',
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
    }

    public function vencido(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado'     => 'vencido',
            'fecha_pago' => null,
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado'     => 'pendiente',
            'fecha_pago' => null,
        ]);
    }
}
