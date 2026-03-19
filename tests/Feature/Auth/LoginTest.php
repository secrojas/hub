<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_admin_cannot_login_with_wrong_password(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_users_can_logout(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }
}
