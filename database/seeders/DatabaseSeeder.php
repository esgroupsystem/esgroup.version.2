<?php

namespace Database\Seeders;

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
        User::updateOrCreate(
            ['email' => 'developer@esgroup.com.ph'],
            [
                'username' => 'developer',
                'full_name' => 'System',
                'password' => Hash::make('123123'),
                'role' => 'Developer',
                'status' => 'online',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
