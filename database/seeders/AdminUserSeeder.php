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
            ['album' => '00000'],
            [
                'name' => 'Admin',
                'email' => 'admin@panel.local',
                'password' => Hash::make('root_admin'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
