<?php

namespace Tests\Feature\Tasks;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_admin_can_create_task(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/tasks', [
            'titulo'    => 'Nueva tarea de prueba',
            'client_id' => $client->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'titulo'    => 'Nueva tarea de prueba',
            'client_id' => $client->id,
            'estado'    => 'backlog',
        ]);
    }

    public function test_create_task_validation_requires_titulo_and_client(): void
    {
        $response = $this->actingAs($this->admin)->post('/tasks', []);

        $response->assertSessionHasErrors(['titulo', 'client_id']);
    }

    public function test_admin_can_update_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->actingAs($this->admin)->put("/tasks/{$task->id}", [
            'titulo'    => 'Titulo actualizado',
            'client_id' => $task->client_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'titulo' => 'Titulo actualizado',
        ]);
    }

    public function test_admin_can_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/tasks/{$task->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
