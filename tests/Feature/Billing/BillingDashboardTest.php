<?php

namespace Tests\Feature\Billing;

use App\Enums\Role;
use App\Models\Billing;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
        $pagado = Billing::factory()->pagado()->create([
            'monto'      => 2500.50,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);
        // pendiente should NOT appear in cobrado_mes
        Billing::factory()->pendiente()->create(['monto' => 1000.00]);

        $response = $this->actingAs($this->admin)->get('/billing');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Billing/Index')
            ->has('summary')
            ->where('summary.cobrado_mes', (float) $pagado->monto)
        );
    }

    public function test_cobrado_mes_excludes_other_months(): void
    {
        // Last month — should NOT count
        Billing::factory()->create([
            'estado'     => 'pagado',
            'monto'      => 3000.75,
            'fecha_pago' => now()->subMonth()->format('Y-m-d'),
        ]);
        // This month — SHOULD count
        $current = Billing::factory()->create([
            'estado'     => 'pagado',
            'monto'      => 1200.50,
            'fecha_pago' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->admin)->get('/billing');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Billing/Index')
            ->where('summary.cobrado_mes', (float) $current->monto)
        );
    }

    public function test_pendiente_total_sums_correctly(): void
    {
        Billing::factory()->pendiente()->create(['monto' => 1000.25]);
        Billing::factory()->pendiente()->create(['monto' => 500.25]);
        // pagado should NOT be in pendiente_total
        Billing::factory()->pagado()->create(['monto' => 800.00]);

        $response = $this->actingAs($this->admin)->get('/billing');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Billing/Index')
            ->where('summary.pendiente_total', 1500.50)
        );
    }

    public function test_filter_by_estado_returns_correct_billings(): void
    {
        Billing::factory()->pendiente()->create();
        Billing::factory()->pendiente()->create();
        Billing::factory()->pagado()->create();

        $response = $this->actingAs($this->admin)->get('/billing?estado=pendiente');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Billing/Index')
            ->has('billings.data', 2)
        );
    }

    public function test_filter_by_cliente_returns_correct_billings(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();

        Billing::factory()->count(2)->create(['client_id' => $clientA->id]);
        Billing::factory()->count(1)->create(['client_id' => $clientB->id]);

        $response = $this->actingAs($this->admin)->get("/billing?cliente={$clientA->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Billing/Index')
            ->has('billings.data', 2)
        );
    }

    public function test_client_show_includes_billings(): void
    {
        $client = Client::factory()->create();
        Billing::factory()->count(2)->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin)->get("/clients/{$client->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Clients/Show')
            ->has('billings', 2)
        );
    }
}
