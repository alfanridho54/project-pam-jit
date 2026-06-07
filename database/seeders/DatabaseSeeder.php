<?php

namespace Database\Seeders;

use App\Models\TargetServer;
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
        User::query()->updateOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        User::query()->updateOrCreate(
            [
                'email' => 'user@example.com',
            ],
            [
                'name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
        );

        TargetServer::query()->firstOrCreate(
            [
                'name' => 'Demo Localhost',
            ],
            [
                'host' => '127.0.0.1',
                'port' => 22,
                'ssh_username' => null,
                'auth_type' => 'password',
                'ssh_password_encrypted' => null,
                'ssh_private_key_encrypted' => null,
                'description' => 'Inactive placeholder target server for demo data. No real credentials are stored.',
                'is_active' => false,
            ],
        );
    }
}
