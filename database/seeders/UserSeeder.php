<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's users.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ],
        );

        User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Produk',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
            ],
        );
    }
}
