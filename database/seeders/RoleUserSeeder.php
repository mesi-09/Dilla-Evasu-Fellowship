<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@evasu.test'],
            [
                'name' => 'Main Admin',
                'password' => Hash::make('password'),
                'role' => 'main_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'lovesharing@evasu.test'],
            [
                'name' => 'Love Sharing Leader',
                'password' => Hash::make('password'),
                'role' => 'love_sharing_leader',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'counseling@evasu.test'],
            [
                'name' => 'Counseling Leader',
                'password' => Hash::make('password'),
                'role' => 'counseling_leader',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'member@evasu.test'],
            [
                'name' => 'Test Member',
                'password' => Hash::make('password'),
                'role' => 'member',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}