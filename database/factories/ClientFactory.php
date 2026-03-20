<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre'            => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'empresa'           => fake()->company(),
            'telefono'          => fake()->phoneNumber(),
            'stack_tecnologico' => fake()->sentence(),
            'estado'            => fake()->randomElement(['activo', 'potencial', 'pausado']),
            'notas'             => fake()->paragraph(),
            'fecha_inicio'      => fake()->date(),
        ];
    }
}
