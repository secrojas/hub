<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ClientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function createInvitationWithSignedUrl(): array
    {
        $invitation = Invitation::create([
            'token'       => 'valid-token',
            'email'       => 'newclient@example.com',
            'client_name' => 'New Client',
            'expires_at'  => now()->addHours(72),
        ]);

        $url = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addHours(72),
            ['token' => 'valid-token']
        );

        return [$invitation, $url];
    }

    public function test_client_can_accept_valid_invitation(): void
    {
        [$invitation, $url] = $this->createInvitationWithSignedUrl();

        $response = $this->get($url);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Invitation/Accept')
                ->has('email')
                ->has('client_name')
                ->has('token')
        );
    }

    public function test_client_can_set_password_and_register(): void
    {
        [$invitation, $url] = $this->createInvitationWithSignedUrl();

        // Parse the signed URL to get query params for POST
        $response = $this->post($url, [
            'token'                 => 'valid-token',
            'password'              => 'securepass123',
            'password_confirmation' => 'securepass123',
        ]);

        $response->assertRedirect(route('portal'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newclient@example.com',
            'role'  => Role::Client->value,
        ]);
        $this->assertDatabaseHas('invitations', [
            'token' => 'valid-token',
        ]);
        $this->assertNotNull(Invitation::where('token', 'valid-token')->first()->used_at);
    }

    public function test_client_form_has_prefilled_email_and_name(): void
    {
        [$invitation, $url] = $this->createInvitationWithSignedUrl();

        $response = $this->get($url);
        $response->assertInertia(fn ($page) =>
            $page->component('Invitation/Accept')
                ->where('email', 'newclient@example.com')
                ->where('client_name', 'New Client')
        );
    }
}
