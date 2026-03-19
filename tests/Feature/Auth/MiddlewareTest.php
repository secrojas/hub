<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_client_cannot_access_dashboard(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_client_can_access_portal(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_admin_cannot_access_portal(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }
}
