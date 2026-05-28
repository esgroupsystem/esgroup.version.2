<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create permissions first
        $this->call([
            PhilippineHolidaySeeder::class,
            PermissionSeeder::class,
        ]);

        // Create Developer role
        $developerRole = Role::firstOrCreate([
            'name' => 'Developer',
            'guard_name' => 'web',
        ]);

        // Give ALL permissions to Developer
        $developerRole->syncPermissions(
            Permission::all()
        );

        // Create Developer user
        $user = User::updateOrCreate(
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

        // Assign role to user
        $user->assignRole('Developer');
    }
}
