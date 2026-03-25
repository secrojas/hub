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
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_fecha_pago_required_when_estado_is_pagado(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_fecha_pago_not_required_when_estado_is_pendiente(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_estado_rejects_invalid_values(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }
}
