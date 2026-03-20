<?php

namespace Tests\Feature\Tasks;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskKanbanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_index_returns_columns_scoped_to_client(): void
    {
        $this->markTestIncomplete('Pending TaskController');

        $client = Client::factory()->create();
        Task::factory()->count(2)->create(['client_id' => $client->id]);
        Task::factory()->create(); // different client

        $response = $this->actingAs($this->admin)->get("/tasks?cliente={$client->id}");

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->has('columns')
        );
    }

    public function test_update_status_changes_task_estado(): void
    {
        $this->markTestIncomplete('Pending TaskController');

        $task = Task::factory()->create(['estado' => 'backlog']);

        $response = $this->actingAs($this->admin)->put("/tasks/{$task->id}/status", [
            'estado' => 'en_progreso',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'estado' => 'en_progreso',
        ]);
    }

    public function test_update_status_rejects_invalid_estado(): void
    {
        $this->markTestIncomplete('Pending TaskController');

        $task = Task::factory()->create();

        $response = $this->actingAs($this->admin)->put("/tasks/{$task->id}/status", [
            'estado' => 'invalid',
        ]);

        $response->assertStatus(422);
    }

    public function test_global_view_returns_all_clients_tasks(): void
    {
        $this->markTestIncomplete('Pending TaskController');

        Task::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/tasks');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->has('columns')
        );
    }
}
