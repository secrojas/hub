<?php

namespace Tests\Feature\Clients;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_admin_can_create_client(): void
    {
        $response = $this->actingAs($this->admin)->post('/clients', [
            'nombre'  => 'Test Client',
            'email'   => 'test@example.com',
            'empresa' => 'Test Corp',
        ]);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', ['nombre' => 'Test Client']);
    }

    public function test_admin_can_update_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->put("/clients/{$client->id}", [
            'nombre' => 'Updated Name',
            'email'  => $client->email,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', ['nombre' => 'Updated Name']);
    }

    public function test_admin_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/clients/{$client->id}");

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_client_stores_all_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/clients', [
            'nombre'            => 'Full Fields Client',
            'email'             => 'full@example.com',
            'empresa'           => 'Acme Inc',
            'telefono'          => '+34 600 000 000',
            'stack_tecnologico' => 'Laravel, Vue, MySQL',
            'estado'            => 'potencial',
            'notas'             => 'Algunas notas importantes',
            'fecha_inicio'      => '2026-01-15',
        ]);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', [
            'nombre'            => 'Full Fields Client',
            'email'             => 'full@example.com',
            'empresa'           => 'Acme Inc',
            'telefono'          => '+34 600 000 000',
            'stack_tecnologico' => 'Laravel, Vue, MySQL',
            'estado'            => 'potencial',
            'notas'             => 'Algunas notas importantes',
        ]);
    }

    public function test_create_client_requires_nombre_and_email(): void
    {
        $response = $this->actingAs($this->admin)->post('/clients', []);

        $response->assertSessionHasErrors(['nombre', 'email']);
    }

    public function test_create_client_rejects_duplicate_email(): void
    {
        Client::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($this->admin)->post('/clients', [
            'nombre' => 'Another Client',
            'email'  => 'duplicate@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
