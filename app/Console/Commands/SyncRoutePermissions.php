<?php

namespace App\Console\Commands;

use App\Services\Permissions\RoutePermissionSyncService;
use Illuminate\Console\Command;

class SyncRoutePermissions extends Command
{
    protected $signature = 'permissions:sync-routes';

    protected $description = 'Create missing Spatie permissions from route permission middleware.';

    public function handle(RoutePermissionSyncService $service): int
    {
        $result = $service->sync();

        $this->info('Route permission sync completed.');
        $this->line('Total route permissions: '.$result['route_permissions']->count());
        $this->line('Created permissions: '.$result['created_count']);

        if ($result['created_permissions']->isNotEmpty()) {
            $this->newLine();
            $this->info('New permissions created:');

            $result['created_permissions']->each(function (string $permission): void {
                $this->line('- '.$permission);
            });
        }

        return self::SUCCESS;
    }
}
