<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'titulo'    => fake()->sentence(3),
            'notas'     => fake()->optional()->paragraph(),
            'estado'    => QuoteStatus::Borrador,
        ];
    }

    public function borrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => QuoteStatus::Borrador,
        ]);
    }

    public function enviado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => QuoteStatus::Enviado,
        ]);
    }

    public function aceptado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => QuoteStatus::Aceptado,
        ]);
    }

    public function rechazado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => QuoteStatus::Rechazado,
        ]);
    }
}
