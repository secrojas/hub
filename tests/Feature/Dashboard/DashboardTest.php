<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Role;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_dashboard_requires_admin(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_shows_en_progreso_tasks(): void
    {
        $client = Client::factory()->create();
        $task = Task::factory()->create([
            'client_id'    => $client->id,
            'estado'       => TaskStatus::EnProgreso,
            'fecha_limite' => null,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('enProgreso', 1)
            ->where('enProgreso.0.id', $task->id)
        );
    }

    public function test_dashboard_shows_vencen_pronto_tasks(): void
    {
        $client = Client::factory()->create();
        $task = Task::factory()->create([
            'client_id'    => $client->id,
            'estado'       => TaskStatus::Backlog,
            'fecha_limite' => today()->addDays(3)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('vencenProonto', 1)
            ->where('vencenProonto.0.id', $task->id)
        );
    }

    public function test_en_progreso_task_due_soon_appears_only_in_vencen_pronto(): void
    {
        $client = Client::factory()->create();
        $task = Task::factory()->create([
            'client_id'    => $client->id,
            'estado'       => TaskStatus::EnProgreso,
            'fecha_limite' => today()->addDays(2)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('vencenProonto', 1)
            ->where('vencenProonto.0.id', $task->id)
            ->where('enProgreso', fn ($val) => collect($val)->pluck('id')->doesntContain($task->id))
        );
    }

    public function test_tasks_with_null_deadline_excluded_from_vencen_pronto(): void
    {
        $client = Client::factory()->create();
        $task = Task::factory()->create([
            'client_id'    => $client->id,
            'estado'       => TaskStatus::EnProgreso,
            'fecha_limite' => null,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('enProgreso', 1)
            ->where('enProgreso.0.id', $task->id)
            ->where('vencenProonto', fn ($val) => collect($val)->pluck('id')->doesntContain($task->id))
        );
    }

    public function test_finalizado_tasks_excluded_from_dashboard(): void
    {
        $client = Client::factory()->create();
        $task = Task::factory()->create([
            'client_id'    => $client->id,
            'estado'       => TaskStatus::Finalizado,
            'fecha_limite' => today()->addDays(2)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('enProgreso', fn ($val) => collect($val)->pluck('id')->doesntContain($task->id))
            ->where('vencenProonto', fn ($val) => collect($val)->pluck('id')->doesntContain($task->id))
        );
    }

    public function test_dashboard_eager_loads_client(): void
    {
        $client = Client::factory()->create(['nombre' => 'Test Client']);
        Task::factory()->create([
            'client_id'    => $client->id,
            'estado'       => TaskStatus::EnProgreso,
            'fecha_limite' => null,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('enProgreso.0.client')
            ->has('enProgreso.0.client.nombre')
        );
    }
}
