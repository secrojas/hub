<?php

namespace Tests\Feature\Portal;

use App\Enums\BillingStatus;
use App\Enums\QuoteStatus;
use App\Enums\Role;
use App\Enums\TaskStatus;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected Client $clientA;
    protected Client $clientB;
    protected User $clientAUser;
    protected User $clientBUser;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientA = Client::factory()->create();
        $this->clientB = Client::factory()->create();

        $this->clientAUser = User::factory()->create([
            'role'      => Role::Client,
            'client_id' => $this->clientA->id,
        ]);

        $this->clientBUser = User::factory()->create([
            'role'      => Role::Client,
            'client_id' => $this->clientB->id,
        ]);

        $this->adminUser = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_client_sees_own_tasks(): void
    {
        Task::factory()->count(2)->create(['client_id' => $this->clientA->id]);
        Task::factory()->count(3)->create(['client_id' => $this->clientB->id]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->has('tasks', 2)
            ->has('tasks.0', fn (Assert $task) => $task
                ->has('id')
                ->has('titulo')
                ->has('estado')
                ->has('fecha_limite')
            )
        );
    }

    public function test_client_cannot_see_other_tasks(): void
    {
        Task::factory()->count(2)->create(['client_id' => $this->clientA->id]);
        $clientBTask = Task::factory()->create([
            'client_id' => $this->clientB->id,
            'titulo'    => 'TAREA SECRETA DE CLIENTE B',
        ]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->where('tasks', fn ($tasks) => ! collect($tasks)->contains('titulo', $clientBTask->titulo))
        );
    }

    public function test_client_sees_own_quotes(): void
    {
        $quoteA = Quote::factory()->create(['client_id' => $this->clientA->id]);
        QuoteItem::factory()->create(['quote_id' => $quoteA->id, 'precio' => 500.00]);

        Quote::factory()->create(['client_id' => $this->clientB->id]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->has('quotes', 1)
            ->has('quotes.0', fn (Assert $quote) => $quote
                ->has('id')
                ->has('titulo')
                ->has('estado')
                ->has('total')
            )
        );
    }

    public function test_client_can_download_own_pdf(): void
    {
        $quote = Quote::factory()->enviado()->create(['client_id' => $this->clientA->id]);
        QuoteItem::factory()->create(['quote_id' => $quote->id, 'precio' => 1000.00]);

        $response = $this->actingAs($this->clientAUser)
            ->get(route('portal.quotes.pdf', $quote));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_client_cannot_download_other_pdf(): void
    {
        $quote = Quote::factory()->enviado()->create(['client_id' => $this->clientB->id]);
        QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $response = $this->actingAs($this->clientAUser)
            ->get(route('portal.quotes.pdf', $quote));

        $response->assertStatus(403);
    }

    public function test_client_sees_own_billings(): void
    {
        Billing::factory()->count(2)->create(['client_id' => $this->clientA->id]);
        Billing::factory()->count(1)->create(['client_id' => $this->clientB->id]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->has('billings', 2)
            ->has('billings.0', fn (Assert $billing) => $billing
                ->has('id')
                ->has('concepto')
                ->has('monto')
                ->has('fecha_emision')
                ->has('estado')
            )
        );
    }

    public function test_client_cannot_see_other_billings(): void
    {
        Billing::factory()->create(['client_id' => $this->clientA->id]);
        $clientBBilling = Billing::factory()->create([
            'client_id' => $this->clientB->id,
            'concepto'  => 'FACTURA SECRETA DE CLIENTE B',
        ]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->where('billings', fn ($billings) => ! collect($billings)->contains('concepto', $clientBBilling->concepto))
        );
    }

    public function test_dashboard_has_task_counts(): void
    {
        Task::factory()->count(2)->create([
            'client_id' => $this->clientA->id,
            'estado'    => TaskStatus::Backlog,
        ]);
        Task::factory()->create([
            'client_id' => $this->clientA->id,
            'estado'    => TaskStatus::EnProgreso,
        ]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->where('dashboard.tareas.backlog', 2)
            ->where('dashboard.tareas.en_progreso', 1)
        );
    }

    public function test_dashboard_has_quote_counts(): void
    {
        Quote::factory()->borrador()->create(['client_id' => $this->clientA->id]);
        Quote::factory()->enviado()->count(2)->create(['client_id' => $this->clientA->id]);
        Quote::factory()->aceptado()->create(['client_id' => $this->clientA->id]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->where('dashboard.presupuestos.borrador', 1)
            ->where('dashboard.presupuestos.enviado', 2)
            ->where('dashboard.presupuestos.aceptado', 1)
        );
    }

    public function test_dashboard_has_billing_totals(): void
    {
        Billing::factory()->pendiente()->create(['client_id' => $this->clientA->id, 'monto' => 1000.50]);
        Billing::factory()->pendiente()->create(['client_id' => $this->clientA->id, 'monto' => 500.25]);
        Billing::factory()->pagado()->create(['client_id' => $this->clientA->id, 'monto' => 2000.75]);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Portal/Index')
            ->where('dashboard.facturacion.pendiente', 1500.75)
            ->where('dashboard.facturacion.pagado', 2000.75)
        );
    }

    public function test_admin_cannot_access_portal(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/portal');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_portal(): void
    {
        $response = $this->get('/portal');

        $response->assertRedirect(route('login'));
    }

    public function test_client_notas_not_in_props(): void
    {
        $this->clientA->update(['notas' => 'SECRET INTERNAL NOTE']);

        $response = $this->actingAs($this->clientAUser)->get('/portal');

        $this->assertStringNotContainsString('SECRET INTERNAL NOTE', $response->getContent());
    }
}
