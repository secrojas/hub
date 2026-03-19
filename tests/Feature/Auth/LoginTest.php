<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'role'     => Role::Admin,
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_admin_cannot_login_with_wrong_password(): void
    {
        $admin = User::factory()->create([
            'role'     => Role::Admin,
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
