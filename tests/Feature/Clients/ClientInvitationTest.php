<?php

namespace Tests\Feature\Clients;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->post('/invitations', [
            'email'       => $client->email,
            'client_name' => $client->nombre,
            'client_id'   => $client->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invitations', [
            'client_id' => $client->id,
            'email'     => $client->email,
        ]);
    }

    public function test_client_with_active_user_blocks_invite(): void
    {
        $client = Client::factory()->create();
        User::factory()->create([
            'client_id' => $client->id,
            'role'      => Role::Client,
        ]);

        $response = $this->actingAs($this->admin)->get("/clients/{$client->id}");

        $response->assertInertia(fn ($page) => $page->where('hasActiveUser', true));
    }

    public function test_accept_sets_user_client_id(): void
    {
        $client = Client::factory()->create();

        Invitation::create([
            'client_id'   => $client->id,
            'email'       => 'newclient@test.com',
            'client_name' => 'New Client',
            'token'       => 'test-token-abc',
            'expires_at'  => now()->addHours(72),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addHours(72),
            ['token' => 'test-token-abc']
        );

        $response = $this->post($signedUrl, [
            'token'                 => 'test-token-abc',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('portal'));
        $this->assertDatabaseHas('users', [
            'email'     => 'newclient@test.com',
            'client_id' => $client->id,
        ]);
    }
}
