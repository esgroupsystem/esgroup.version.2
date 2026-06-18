<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',
            'dashboard.analytics',
            'dashboard.crm',
            'dashboard.it',

            // Tickets
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'tickets.export',
            'tickets.approve',

            // IT Inventory
            'it-inventory.view',
            'it-inventory.create',
            'it-inventory.update',
            'it-inventory.delete',

            // CCTV
            'cctv.view',
            'cctv.create',
            'cctv.update',
            'cctv.delete',
            'cctv.export',

            // HR Dashboard
            'hr-dashboard.view',

            // Employees
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',

            // Departments
            'departments.view',
            'departments.create',
            'departments.update',
            'departments.delete',

            // Violations
            'violations.view',
            'violations.create',
            'violations.update',
            'violations.delete',

            // Driver Leave
            'driver-leave.view',
            'driver-leave.create',
            'driver-leave.update',

            // Conductor Leave
            'conductor-leave.view',
            'conductor-leave.create',
            'conductor-leave.update',

            // Employee Leave
            'employee-leave.view',
            'employee-leave.create',
            'employee-leave.update',

            // Holidays
            'holidays.view',
            'holidays.create',
            'holidays.update',
            'holidays.delete',

            // Payroll Plotting
            'payroll-plotting.view',
            'payroll-plotting.update',

            // Manual Biometrics
            'manual-biometrics.view',
            'manual-biometrics.create',

            // Attendance Adjustment
            'payroll-attendance-adjustments.view',
            'payroll-attendance-adjustments.create',
            'payroll-attendance-adjustments.update',
            'payroll-attendance-adjustments.delete',

            // Attendance Summary
            'attendance-summary.view',
            'attendance-summary.create',
            'attendance-summary.export',

            // Employee Salary
            'employee-salaries.view',
            'employee-salaries.create',
            'employee-salaries.update',
            'employee-salaries.delete',

            // Payroll
            'payroll.view',
            'payroll.create',
            'payroll.finalize',
            'payroll.export',
            'payroll.delete',

            // Claims
            'claims.view',
            'claims.create',
            'claims.update',
            'claims.delete',

            // Biometrics
            'mirasol-logs.view',
            'mirasol-logs.sync',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Roles
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            // Maintenance Requests
            'request.view',
            'request.create',
            'request.update',
            'request.delete',

            // Categories
            'category.view',
            'category.create',
            'category.update',
            'category.delete',

            // Items
            'items.view',
            'items.create',
            'items.update',
            'items.delete',

            // Stock Transfers
            'stock-transfers.view',
            'stock-transfers.create',
            'stock-transfers.rollback',

            // Odometer
            'odometer.view',
            'odometer.create',
            'odometer.update',

            // Purchase Orders
            'purchase.view',
            'purchase.update',

            // Parts Out
            'parts-out.view',
            'parts-out.create',
            'parts-out.update',
            'parts-out.cancel',
            'parts-out.rollback',

            // Receivings
            'receivings.view',
            'receivings.create',
            'receivings.update',
            'receivings.rollback',

            // Purchase Receiving
            'received.view',
            'received.receive',
            'received.update',

            // Buses
            'buses.view',
            'buses.create',
            'buses.update',
            'buses.delete',

            // All Bus Resource
            'allbus.view',
            'allbus.create',
            'allbus.update',
            'allbus.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
