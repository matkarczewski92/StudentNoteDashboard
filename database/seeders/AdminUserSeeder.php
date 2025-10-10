<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@panel.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Haslo_Admin_123!'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
