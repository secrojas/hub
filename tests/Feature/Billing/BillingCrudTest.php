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
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_admin_can_create_billing(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_admin_can_update_billing(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_admin_can_delete_billing(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_guest_cannot_access_billing(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }
}
