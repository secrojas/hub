<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_invitation_link(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->post('/invitations', [
            'email'       => 'client@example.com',
            'client_name' => 'Test Client',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('invitation_url');
        $this->assertDatabaseHas('invitations', [
            'email'       => 'client@example.com',
            'client_name' => 'Test Client',
        ]);
    }

    public function test_expired_invitation_is_rejected(): void
    {
        Invitation::create([
            'token'       => 'expired-token',
            'email'       => 'client@example.com',
            'client_name' => 'Test Client',
            'expires_at'  => now()->subHour(),
        ]);

        // Create a signed URL that is already expired
        $url = URL::temporarySignedRoute(
            'invitation.accept',
            now()->subMinutes(30),  // expired
            ['token' => 'expired-token']
        );

        $response = $this->get($url);
        $response->assertStatus(403);
    }

    public function test_used_invitation_is_rejected(): void
    {
        Invitation::create([
            'token'       => 'used-token',
            'email'       => 'client@example.com',
            'client_name' => 'Test Client',
            'expires_at'  => now()->addHours(72),
            'used_at'     => now(),  // already used
        ]);

        $url = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addHours(72),
            ['token' => 'used-token']
        );

        $response = $this->get($url);
        $response->assertStatus(403);
    }
}
