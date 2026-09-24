<?php

declare(strict_types=1);

namespace App\Support\Navigation;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Main application menu for the React (Inertia) shell.
 *
 * Groups, links and permission checks for the sidebar (rendered by
 * resources/js/react/components/nav-main.tsx). Items whose route is listed in
 * INERTIA_ROUTES are navigated client-side; any other link is a normal page load.
 */
final class MainNavigation
{
    /**
     * Route names already migrated to React pages. Add a route here when its
     * controller starts returning Inertia::render().
     *
     * @var list<string>
     */
    public const INERTIA_ROUTES = [
        'dashboard.index',
        'chairman.hr-data.index',
        'hr.dashboard',
        'dashboard.itindex',
        'items.dashboard',
        'odometer.index',
        'fleet.buses.index',
        'fleet.for-sale-units.index',
        'fleet.for-sale-units.create',
        'fleet.for-sale-units.edit',
        'auth.change.password.form',
        'payroll-plotting.index',
        'payroll-attendance-adjustments.index',
        'payroll-attendance-adjustments.create',
        'payroll-attendance-adjustments.edit',
        'attendance-summary.index',
        'payroll.index',
        'payroll.create',
        'payroll.show',
        'payroll.items.show',
        'payroll-employee-salaries.index',
        'payroll-employee-salaries.create',
        'payroll-employee-salaries.edit',
        'holidays.index',
        'holidays.create',
        'holidays.edit',
        'benefits-records.index',
        'benefits-records.overall',
        'payroll-audit-logs.index',
        'manual-biometrics.index',
        'mirasol-logs.index',
        'biometrics.employees.index',
        'biometrics.employees.edit',
        'tickets.joborder.index',
        'tickets.createjoborder.index',
        'tickets.joborder.view',
        'concern.cctv.index',
        'concern.bus-status',
        'concern.bus-status.show',
        'it-inventory.index',
        'it-inventory.create',
        'it-inventory.edit',
        'employees.staff.index',
        'employees.staff.show',
        'employees.departments.index',
        'violation.offenses.index',
        'claims.index',
        'employee-leave.employee.index',
        'employee-leave.employee.create',
        'employee-leave.employee.edit',
        'driver-leave.driver.index',
        'driver-leave.driver.create',
        'driver-leave.driver.edit',
        'conductor-leave.conductor.index',
        'conductor-leave.conductor.create',
        'conductor-leave.conductor.edit',
        'parts-out.index',
        'parts-out.create',
        'parts-out.show',
        'maintenance.job-orders.index',
        'maintenance.job-orders.create',
        'maintenance.job-orders.show',
        'maintenance.job-orders.edit-status',
        'maintenance.job-orders.edit-number',
        'buses.index',
        'buses.show',
        'allbus.index',
        'allbus.create',
        'allbus.edit',
        'receivings.index',
        'receivings.create',
        'receivings.show',
        'stock-transfers.index',
        'stock-transfers.create',
        'stock-transfers.show',
        'category.index',
        'items.index',
        'authentication.users.index',
        'roles.index',
    ];

    /**
     * @return list<array{label: string, items: list<array<string, mixed>>}>
     */
    public static function for(?User $user, Request $request): array
    {
        if (! $user) {
            return [];
        }

        $groups = [
            [
                'label' => 'General',
                'items' => [
                    self::link('Dashboard', 'dashboard.index', 'dashboard.view', 'layout-dashboard', ['dashboard.index']),
                ],
            ],
            [
                'label' => 'Fleet',
                'items' => [
                    self::link('Odometer Monitoring', 'odometer.index', 'odometer.view', 'gauge', ['odometer.*']),
                    self::link('Bus Analytics', 'fleet.buses.index', 'fleet.view', 'chart-column', ['fleet.buses.*']),
                ],
            ],
            [
                'label' => 'IT Support',
                'items' => [
                    self::link('Bus Dashboard', 'concern.bus-status', 'cctv.view', 'bus'),
                    self::link('Tickets Job Order', 'tickets.joborder.index', 'tickets.view', 'ticket', ['tickets.*']),
                    self::link('CCTV Concern', 'concern.cctv.index', 'cctv.view', 'cctv', ['concern.cctv.*']),
                    self::link('IT Inventory', 'it-inventory.index', 'it-inventory.view', 'laptop', ['it-inventory.*']),
                ],
            ],
            [
                'label' => 'Human Resources',
                'items' => [
                    self::parent('Employees', 'users', [
                        self::link('Employee List', 'employees.staff.index', 'employees.view', active: ['employees.staff.*']),
                        self::link('Department & Position', 'employees.departments.index', 'departments.view', active: ['employees.departments.*']),
                        self::link('HR Offenses', 'violation.offenses.index', 'violations.view', active: ['violation.*']),
                    ]),
                    self::parent('Benefits', 'hand-heart', [
                        self::link('SSS / Maternity / Paternity', 'claims.index', 'claims.view', active: ['claims.*']),
                    ]),
                    self::parent('Leaves', 'calendar-days', [
                        self::link('Admin', 'employee-leave.employee.index', 'employee-leave.view', active: ['employee-leave.*']),
                        self::link('Driver', 'driver-leave.driver.index', 'driver-leave.view', active: ['driver-leave.*']),
                        self::link('Conductor', 'conductor-leave.conductor.index', 'conductor-leave.view', active: ['conductor-leave.*']),
                    ]),
                ],
            ],
            [
                'label' => 'Biometrics',
                'items' => [
                    self::link('Biometrics Sync', 'mirasol-logs.index', 'mirasol-logs.view', 'fingerprint', ['mirasol-logs.*']),
                    self::link('Manual Biometrics', 'manual-biometrics.index', 'manual-biometrics.view', 'keyboard', ['manual-biometrics.*']),
                ],
            ],
            [
                'label' => 'Scheduling & Rates',
                'items' => [
                    self::link('Employees', 'biometrics.employees.index', 'biometrics.view', 'id-card', ['biometrics.employees.*']),
                    self::link('Work Schedule', 'payroll-plotting.index', 'payroll-plotting.view', 'calendar-clock', ['payroll-plotting.*']),
                    self::link('Employee Rates', 'payroll-employee-salaries.index', 'employee-salaries.view', 'banknote', ['payroll-employee-salaries.*']),
                    self::link('Holiday Calendar', 'holidays.index', 'holidays.view', 'calendar-heart', ['holidays.*']),
                ],
            ],
            [
                'label' => 'Payroll',
                'items' => [
                    self::link('Adjustment', 'payroll-attendance-adjustments.index', 'payroll-attendance-adjustments.view', 'square-pen', ['payroll-attendance-adjustments.*']),
                    self::link('Summary', 'attendance-summary.index', 'attendance-summary.view', 'clipboard-list', ['attendance-summary.*']),
                    self::link('Payroll', 'payroll.index', 'payroll.view', 'wallet', ['payroll.*']),
                    self::link('Benefits Records', 'benefits-records.index', 'benefits-records.view', 'shield-check', ['benefits-records.index', 'benefits-records.show']),
                    self::link('Benefits Overall', 'benefits-records.overall', 'benefits-records.view', 'file-spreadsheet', ['benefits-records.overall']),
                    self::link('Payroll Transaction Logs', 'payroll-audit-logs.index', 'payroll-audit-logs.view', 'history', ['payroll-audit-logs.*']),
                ],
            ],
            [
                'label' => 'Maintenance',
                'items' => [
                    self::link('Parts Issuance', 'parts-out.index', 'parts-out.view', 'wrench', ['parts-out.*']),
                    self::link('Maintenance Job Orders', 'maintenance.job-orders.index', 'buses.view', 'clipboard-check', ['maintenance.job-orders.*']),
                    self::link('Vehicle History', 'buses.index', 'buses.view', 'bus-front', ['buses.*']),
                    self::link('Bus List', 'allbus.index', 'allbus.view', 'list', ['allbus.*']),
                ],
            ],
            [
                'label' => 'Inventory',
                'items' => [
                    self::link('Maintenance Stock', 'items.dashboard', 'items.view', 'warehouse', ['items.dashboard']),
                    self::link('Receiving Area', 'receivings.index', 'receivings.view', 'package-open', ['receivings.*']),
                    self::link('Stock Transfer', 'stock-transfers.index', 'stock-transfers.view', 'arrow-left-right', ['stock-transfers.*']),
                ],
            ],
            [
                'label' => 'Products',
                'items' => [
                    self::link('Categories', 'category.index', 'category.view', 'tags', ['category.*']),
                    self::link('Products', 'items.index', 'items.view', 'package', ['items.index', 'items.create', 'items.edit', 'items.show']),
                ],
            ],
            [
                'label' => 'Security',
                'items' => [
                    self::link('Users', 'authentication.users.index', 'users.view', 'user-cog', ['authentication.users.*']),
                    self::link('Roles', 'roles.index', 'roles.view', 'shield', ['roles.*']),
                ],
            ],
        ];

        return array_values(array_filter(array_map(
            static function (array $group) use ($user, $request): ?array {
                $items = array_values(array_filter(array_map(
                    static fn (array $item): ?array => self::resolve($item, $user, $request),
                    $group['items']
                )));

                return $items === [] ? null : ['label' => $group['label'], 'items' => $items];
            },
            $groups
        )));
    }

    /**
     * @param  list<string>  $active  route-name patterns that mark the link active
     */
    private static function link(
        string $title,
        string $route,
        string $permission,
        ?string $icon = null,
        array $active = [],
    ): array {
        return [
            'title' => $title,
            'route' => $route,
            'permission' => $permission,
            'icon' => $icon,
            'active' => $active === [] ? [$route] : $active,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $children
     */
    private static function parent(string $title, string $icon, array $children): array
    {
        return [
            'title' => $title,
            'icon' => $icon,
            'children' => $children,
        ];
    }

    private static function resolve(array $item, User $user, Request $request): ?array
    {
        if (isset($item['children'])) {
            $children = array_values(array_filter(array_map(
                static fn (array $child): ?array => self::resolve($child, $user, $request),
                $item['children']
            )));

            if ($children === []) {
                return null;
            }

            return [
                'title' => $item['title'],
                'icon' => $item['icon'],
                'isActive' => in_array(true, array_column($children, 'isActive'), true),
                'items' => $children,
            ];
        }

        if (! $user->can($item['permission']) || ! Route::has($item['route'])) {
            return null;
        }

        return [
            'title' => $item['title'],
            'icon' => $item['icon'],
            'url' => route($item['route']),
            'isActive' => $request->routeIs(...$item['active']),
            'inertia' => in_array($item['route'], self::INERTIA_ROUTES, true),
        ];
    }
}
