<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'developer@esgroup.com.ph'],
            [
                'username'        => 'developer',
                'full_name'       => 'Developer Admin',
                'password'        => Hash::make('M@st3rk3ys123!'),
                'role'            => 'Developer',
                'status'          => 'online',
                'account_status'  => 'active',
                'email_verified_at' => now()
            ]
        );

        User::updateOrCreate(

            ['email' => 'esit.lenberd@gmail.com'],
            [
                'username'        => 'lenberd',
                'full_name'       => 'Lenberd Ilaw',
                'password'        => Hash::make('123123'),
                'role'            => 'IT Officer',
                'status'          => 'online',
                'account_status'  => 'active',
                'email_verified_at' => now()
            ]
        );
    }
}
