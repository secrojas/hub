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
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();

        $taskA1 = Task::factory()->create(['client_id' => $clientA->id, 'estado' => 'backlog']);
        $taskA2 = Task::factory()->create(['client_id' => $clientA->id, 'estado' => 'en_progreso']);
        $taskB  = Task::factory()->create(['client_id' => $clientB->id, 'estado' => 'backlog']);

        $response = $this->actingAs($this->admin)->get("/tasks?cliente={$clientA->id}");

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->where('columns', function ($columns) use ($taskA1, $taskA2, $taskB) {
                $allIds = collect($columns)
                    ->flatMap(fn ($col) => collect($col)->pluck('id'))
                    ->all();

                // Client A's 2 tasks present
                if (!in_array($taskA1->id, $allIds)) return false;
                if (!in_array($taskA2->id, $allIds)) return false;
                // Client B's task absent
                if (in_array($taskB->id, $allIds)) return false;

                return true;
            })
        );
    }

    public function test_filter_by_estado(): void
    {
        $taskProgreso  = Task::factory()->create(['estado' => 'en_progreso']);
        $taskFinalizado = Task::factory()->create(['estado' => 'finalizado']);

        $response = $this->actingAs($this->admin)->get('/tasks?estado=en_progreso');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->where('columns', function ($columns) use ($taskProgreso, $taskFinalizado) {
                $allIds = collect($columns)
                    ->flatMap(fn ($col) => collect($col)->pluck('id'))
                    ->all();

                if (!in_array($taskProgreso->id, $allIds)) return false;
                if (in_array($taskFinalizado->id, $allIds)) return false;

                return true;
            })
        );
    }

    public function test_filter_by_titulo(): void
    {
        $taskLanding    = Task::factory()->create(['titulo' => 'Disenar landing page']);
        $taskServidor   = Task::factory()->create(['titulo' => 'Configurar servidor']);

        $response = $this->actingAs($this->admin)->get('/tasks?titulo=landing');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->where('columns', function ($columns) use ($taskLanding, $taskServidor) {
                $allIds = collect($columns)
                    ->flatMap(fn ($col) => collect($col)->pluck('id'))
                    ->all();

                if (!in_array($taskLanding->id, $allIds)) return false;
                if (in_array($taskServidor->id, $allIds)) return false;

                return true;
            })
        );
    }

    public function test_filter_by_prioridad(): void
    {
        $taskAlta = Task::factory()->create(['prioridad' => 'alta']);
        $taskBaja = Task::factory()->create(['prioridad' => 'baja']);

        $response = $this->actingAs($this->admin)->get('/tasks?prioridad=alta');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tasks/Index')
            ->where('columns', function ($columns) use ($taskAlta, $taskBaja) {
                $allIds = collect($columns)
                    ->flatMap(fn ($col) => collect($col)->pluck('id'))
                    ->all();

                if (!in_array($taskAlta->id, $allIds)) return false;
                if (in_array($taskBaja->id, $allIds)) return false;

                return true;
            })
        );
    }
}
