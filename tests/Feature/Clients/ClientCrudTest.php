<?php

namespace Tests\Feature\Clients;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_admin_can_view_clients_list(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/clients');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Clients/Index')
            ->has('clients.data', 3)
        );
    }

    public function test_estado_filter_returns_correct_clients(): void
    {
        Client::factory()->count(2)->create(['estado' => 'activo']);
        Client::factory()->create(['estado' => 'pausado']);

        $response = $this->actingAs($this->admin)->get('/clients?estado=activo');

        $response->assertInertia(fn ($page) => $page
            ->has('clients.data', 2)
            ->where('filtroEstado', 'activo')
        );
    }

    public function test_admin_can_view_client_detail(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->get("/clients/{$client->id}");

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Clients/Show')
            ->where('client.id', $client->id)
            ->has('hasActiveUser')
        );
    }

    public function test_show_page_indicates_if_client_has_active_user(): void
    {
        $client = Client::factory()->create();
        User::factory()->create([
            'role'      => Role::Client,
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/clients/{$client->id}");

        $response->assertInertia(fn ($page) => $page
            ->where('hasActiveUser', true)
        );
    }
}
