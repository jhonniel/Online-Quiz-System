<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure default admin exists
        User::firstOrCreate(
            ['email' => 'admin@quiz.com'],
            [
                'name'        => 'Admin User',
                'password'    => bcrypt('password'),
                'is_approved' => true,
                'is_active'   => true,
                'role'        => 'admin',
            ]
        );

        // Ensure default test user exists
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'        => 'Test User',
                'password'    => bcrypt('password'),
                'is_approved' => true,
                'is_active'   => true,
                'role'        => 'user',
            ]
        );
    }
}
