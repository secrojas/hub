<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    // QUOT-01: CRUD
    public function test_admin_can_view_quotes_index(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_admin_can_create_quote_with_items(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_items_are_persisted_in_quote_items_table(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_admin_can_update_quote_in_borrador(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_admin_can_delete_quote_in_borrador(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_requires_at_least_one_item(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_requires_item_descripcion_and_precio(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    // QUOT-02: State management
    public function test_admin_can_change_quote_estado(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_cannot_delete_non_borrador_quote(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_cannot_edit_items_post_borrador(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    // QUOT-03: PDF
    public function test_pdf_downloads_for_enviado_quote(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_pdf_forbidden_for_borrador_quote(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }

    public function test_pdf_response_has_correct_filename(): void
    {
        $this->markTestIncomplete('Pending QuoteController');
    }
}
