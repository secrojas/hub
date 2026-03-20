<?php

namespace Tests\Feature\Clients;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_admin_can_view_clients_list(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_estado_filter_returns_correct_clients(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_admin_can_view_client_detail(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_show_page_indicates_if_client_has_active_user(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }
}
