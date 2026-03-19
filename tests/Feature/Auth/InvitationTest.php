<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_invitation_link(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_expired_invitation_is_rejected(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_used_invitation_is_rejected(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }
}
