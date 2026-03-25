<?php

namespace Tests\Feature\Billing;

use App\Enums\Role;
use App\Models\Billing;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_admin_can_view_billing_index(): void
    {
        $client = Client::factory()->create();
        Billing::factory()->count(3)->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin)->get('/billing');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Billing/Index')
            ->has('billings.data', 3)
        );
    }

    public function test_admin_can_create_billing(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/billing', [
            'client_id'     => $client->id,
            'concepto'      => 'Desarrollo web',
            'monto'         => '1500.00',
            'fecha_emision' => '2026-03-01',
            'fecha_pago'    => null,
            'estado'        => 'pendiente',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('billings', [
            'client_id' => $client->id,
            'concepto'  => 'Desarrollo web',
            'estado'    => 'pendiente',
        ]);
    }

    public function test_admin_can_update_billing(): void
    {
        $billing = Billing::factory()->create();

        $response = $this->actingAs($this->admin)->put("/billing/{$billing->id}", [
            'client_id'     => $billing->client_id,
            'concepto'      => 'Concepto actualizado',
            'monto'         => $billing->monto,
            'fecha_emision' => $billing->fecha_emision->format('Y-m-d'),
            'fecha_pago'    => null,
            'estado'        => 'pendiente',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('billings', [
            'id'      => $billing->id,
            'concepto' => 'Concepto actualizado',
        ]);
    }

    public function test_admin_can_delete_billing(): void
    {
        $billing = Billing::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/billing/{$billing->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('billings', ['id' => $billing->id]);
    }

    public function test_guest_cannot_access_billing(): void
    {
        $response = $this->get('/billing');

        $response->assertRedirect('/login');
    }
}
