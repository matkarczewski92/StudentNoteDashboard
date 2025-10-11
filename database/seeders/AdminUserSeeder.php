<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Avoid mass-assignment issues for non-fillable attributes like 'role'
        $user = User::updateOrCreate(
            ['album' => '00000'],
            [
                'name' => 'Admin',
                'email' => 'admin@panel.local',
                'password' => Hash::make('root_admin'),
            ]
        );

        // Set attributes that may be guarded explicitly
        $user->forceFill([
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();
    }
}
