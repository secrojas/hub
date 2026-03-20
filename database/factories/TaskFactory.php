<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo'       => fake()->sentence(3),
            'client_id'    => Client::factory(),
            'descripcion'  => fake()->optional()->paragraph(),
            'prioridad'    => fake()->randomElement(['baja', 'media', 'alta']),
            'estado'       => 'backlog',
            'fecha_limite' => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
        ];
    }
}
