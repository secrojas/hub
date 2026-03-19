<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_accept_valid_invitation(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_client_can_set_password_and_register(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }

    public function test_client_form_has_prefilled_email_and_name(): void
    {
        $this->markTestIncomplete('Pending implementation in Plan 02/03');
    }
}
