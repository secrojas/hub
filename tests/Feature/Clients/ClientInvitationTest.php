<?php

namespace Tests\Feature\Clients;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => Role::Admin]);
    }

    public function test_invite_button_wires_invitation_with_client_id(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_client_with_active_user_blocks_invite(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_accept_sets_user_client_id(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }
}
