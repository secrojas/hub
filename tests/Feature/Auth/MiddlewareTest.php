<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
    }

    public function test_client_cannot_access_dashboard(): void
    {
        $client = User::factory()->create(['role' => Role::Client]);
        $this->actingAs($client)->get('/dashboard')->assertStatus(403);
    }

    public function test_client_can_access_portal(): void
    {
        $client = User::factory()->create(['role' => Role::Client]);
        $this->actingAs($client)->get('/portal')->assertStatus(200);
    }

    public function test_admin_cannot_access_portal(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $this->actingAs($admin)->get('/portal')->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
