<?php

namespace Tests\Feature\Clients;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_admin_can_create_client(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_admin_can_update_client(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_admin_can_delete_client(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_client_stores_all_fields(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_create_client_requires_nombre_and_email(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_create_client_rejects_duplicate_email(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }
}
