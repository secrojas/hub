<?php

namespace Tests\Feature\Tasks;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_filter_by_cliente(): void
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

    public function test_filter_by_estado(): void
    {
        $this->markTestIncomplete('Pending TaskController');

        Task::factory()->count(2)->create(['estado' => 'en_progreso']);
        Task::factory()->create(['estado' => 'finalizado']);

        $response = $this->actingAs($this->admin)->get('/tasks?estado=en_progreso');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->has('columns')
        );
    }

    public function test_filter_by_titulo(): void
    {
        $this->markTestIncomplete('Pending TaskController');

        Task::factory()->create(['titulo' => 'Diseño de logo']);
        Task::factory()->create(['titulo' => 'Reunión de kick-off']);

        $response = $this->actingAs($this->admin)->get('/tasks?titulo=logo');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->has('columns')
        );
    }

    public function test_filter_by_prioridad(): void
    {
        $this->markTestIncomplete('Pending TaskController');

        Task::factory()->count(2)->create(['prioridad' => 'alta']);
        Task::factory()->create(['prioridad' => 'baja']);

        $response = $this->actingAs($this->admin)->get('/tasks?prioridad=alta');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->has('columns')
        );
    }
}
