<?php

namespace Tests\Feature\Billing;

use App\Enums\Role;
use App\Models\Billing;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_store_requires_concepto_monto_fecha_emision_estado(): void
    {
        $response = $this->actingAs($this->admin)->post('/billing', []);

        $response->assertSessionHasErrors(['concepto', 'monto', 'fecha_emision', 'estado', 'client_id']);
    }

    public function test_fecha_pago_required_when_estado_is_pagado(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/billing', [
            'client_id'     => $client->id,
            'concepto'      => 'Servicio',
            'monto'         => '1000.00',
            'fecha_emision' => '2026-03-01',
            'estado'        => 'pagado',
            // fecha_pago omitted intentionally
        ]);

        $response->assertSessionHasErrors('fecha_pago');
    }

    public function test_fecha_pago_not_required_when_estado_is_pendiente(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/billing', [
            'client_id'     => $client->id,
            'concepto'      => 'Servicio',
            'monto'         => '1000.00',
            'fecha_emision' => '2026-03-01',
            'fecha_pago'    => null,
            'estado'        => 'pendiente',
        ]);

        $response->assertSessionDoesntHaveErrors('fecha_pago');
    }

    public function test_estado_rejects_invalid_values(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/billing', [
            'client_id'     => $client->id,
            'concepto'      => 'Servicio',
            'monto'         => '1000.00',
            'fecha_emision' => '2026-03-01',
            'estado'        => 'invalido',
        ]);

        $response->assertSessionHasErrors('estado');
    }
}
