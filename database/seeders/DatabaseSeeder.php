<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'name'              => 'Admin',
                'password'          => Hash::make(env('ADMIN_PASSWORD')),
                'role'              => Role::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
