<?php

namespace Tests\Feature\Billing;

use App\Enums\Role;
use App\Models\Billing;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_summary_shows_correct_cobrado_mes(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_cobrado_mes_excludes_other_months(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_pendiente_total_sums_correctly(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_filter_by_estado_returns_correct_billings(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_filter_by_cliente_returns_correct_billings(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }

    public function test_client_show_includes_billings(): void
    {
        $this->markTestIncomplete('Pending BillingController');
    }
}
