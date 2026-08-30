<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PhilippineHolidaySeeder::class,
            BiometricCompanySeeder::class,
            PermissionSeeder::class,
        ]);

        $developerRole = Role::firstOrCreate([
            'name' => 'Developer',
            'guard_name' => 'web',
        ]);

        $developerRole->syncPermissions(Permission::all());

        // Never create a privileged account automatically in production.
        if (! filter_var(env('SEED_DEVELOPER_USER', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $password = (string) env('SEED_DEVELOPER_PASSWORD', '');
        if (strlen($password) < 16) {
            throw new \RuntimeException('SEED_DEVELOPER_PASSWORD must contain at least 16 characters when SEED_DEVELOPER_USER=true.');
        }

        $email = strtolower(trim((string) env('SEED_DEVELOPER_EMAIL', '')));
        $username = trim((string) env('SEED_DEVELOPER_USERNAME', ''));

        if ($email === '' || $username === '') {
            throw new \RuntimeException('SEED_DEVELOPER_EMAIL and SEED_DEVELOPER_USERNAME are required when SEED_DEVELOPER_USER=true.');
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'username' => $username,
                'full_name' => 'System Developer',
                'password' => Hash::make($password),
                'role' => 'Developer',
                'status' => 'offline',
                'account_status' => 'active',
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('Developer');
    }
}
