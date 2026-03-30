<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    // QUOT-01: CRUD

    public function test_admin_can_view_quotes_index(): void
    {
        $client = Client::factory()->create();
        Quote::factory()->count(2)->create(['client_id' => $client->id])
            ->each(fn ($q) => QuoteItem::factory()->create(['quote_id' => $q->id]));

        $response = $this->actingAs($this->admin)->get('/quotes');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Quotes/Index')
            ->has('quotes', 2)
        );
    }

    public function test_admin_can_create_quote_with_items(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/quotes', [
            'client_id' => $client->id,
            'titulo'    => 'Presupuesto de prueba',
            'notas'     => 'Algunas notas',
            'items'     => [
                ['descripcion' => 'Item uno', 'precio' => '1000.00'],
                ['descripcion' => 'Item dos', 'precio' => '2000.00'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quotes', ['titulo' => 'Presupuesto de prueba']);
        $this->assertDatabaseCount('quote_items', 2);
    }

    public function test_items_are_persisted_in_quote_items_table(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin)->post('/quotes', [
            'client_id' => $client->id,
            'titulo'    => 'Multi items',
            'notas'     => null,
            'items'     => [
                ['descripcion' => 'Servicio A', 'precio' => '500.00'],
                ['descripcion' => 'Servicio B', 'precio' => '750.50'],
                ['descripcion' => 'Servicio C', 'precio' => '1200.00'],
            ],
        ]);

        $quote = Quote::where('titulo', 'Multi items')->first();

        $this->assertDatabaseHas('quote_items', ['quote_id' => $quote->id, 'descripcion' => 'Servicio A', 'precio' => (float) '500.00']);
        $this->assertDatabaseHas('quote_items', ['quote_id' => $quote->id, 'descripcion' => 'Servicio B', 'precio' => (float) '750.50']);
        $this->assertDatabaseHas('quote_items', ['quote_id' => $quote->id, 'descripcion' => 'Servicio C', 'precio' => (float) '1200.00']);
    }

    public function test_admin_can_update_quote_in_borrador(): void
    {
        $quote = Quote::factory()->borrador()->create();
        QuoteItem::factory()->count(2)->create(['quote_id' => $quote->id]);

        $response = $this->actingAs($this->admin)->put("/quotes/{$quote->id}", [
            'client_id' => $quote->client_id,
            'titulo'    => 'Titulo actualizado',
            'notas'     => '',
            'items'     => [
                ['descripcion' => 'Nuevo item 1', 'precio' => '300.00'],
                ['descripcion' => 'Nuevo item 2', 'precio' => '600.00'],
                ['descripcion' => 'Nuevo item 3', 'precio' => '900.00'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'titulo' => 'Titulo actualizado']);
        $this->assertDatabaseCount('quote_items', 3);
    }

    public function test_admin_can_delete_quote_in_borrador(): void
    {
        $quote = Quote::factory()->borrador()->create();
        QuoteItem::factory()->count(2)->create(['quote_id' => $quote->id]);

        $response = $this->actingAs($this->admin)->delete("/quotes/{$quote->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
        $this->assertDatabaseMissing('quote_items', ['quote_id' => $quote->id]);
    }

    public function test_requires_at_least_one_item(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/quotes', [
            'client_id' => $client->id,
            'titulo'    => 'Sin items',
            'notas'     => null,
            'items'     => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_requires_item_descripcion_and_precio(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/quotes', [
            'client_id' => $client->id,
            'titulo'    => 'Items vacios',
            'notas'     => null,
            'items'     => [
                ['descripcion' => '', 'precio' => ''],
            ],
        ]);

        $response->assertSessionHasErrors(['items.0.descripcion', 'items.0.precio']);
    }

    // QUOT-02: State management

    public function test_admin_can_change_quote_estado(): void
    {
        $quote = Quote::factory()->borrador()->create();

        $response = $this->actingAs($this->admin)->patch("/quotes/{$quote->id}/estado", [
            'estado' => 'enviado',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'estado' => 'enviado']);
    }

    public function test_cannot_delete_non_borrador_quote(): void
    {
        $quote = Quote::factory()->enviado()->create();

        $response = $this->actingAs($this->admin)->delete("/quotes/{$quote->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    public function test_cannot_edit_items_post_borrador(): void
    {
        $quote = Quote::factory()->enviado()->create();
        QuoteItem::factory()->create(['quote_id' => $quote->id]);

        $response = $this->actingAs($this->admin)->put("/quotes/{$quote->id}", [
            'client_id' => $quote->client_id,
            'titulo'    => 'Intento modificar',
            'notas'     => '',
            'items'     => [
                ['descripcion' => 'Item modificado', 'precio' => '9999.00'],
            ],
        ]);

        $response->assertStatus(403);
    }

    // QUOT-03: PDF
    public function test_pdf_downloads_for_enviado_quote(): void
    {
        $quote = Quote::factory()->enviado()->create();
        QuoteItem::factory()->for($quote)->count(2)->create();

        $response = $this->actingAs($this->admin)->get("/quotes/{$quote->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_forbidden_for_borrador_quote(): void
    {
        $quote = Quote::factory()->borrador()->create();

        $response = $this->actingAs($this->admin)->get("/quotes/{$quote->id}/pdf");

        $response->assertStatus(403);
    }

    public function test_pdf_response_has_correct_filename(): void
    {
        $quote = Quote::factory()->enviado()->create(['titulo' => 'Landing Page Empresa XYZ']);
        QuoteItem::factory()->for($quote)->count(2)->create();

        $response = $this->actingAs($this->admin)->get("/quotes/{$quote->id}/pdf");

        $response->assertStatus(200);
        $this->assertStringContainsString(
            "presupuesto-{$quote->id}-landing-page-empresa-xyz.pdf",
            $response->headers->get('Content-Disposition')
        );
    }
}
