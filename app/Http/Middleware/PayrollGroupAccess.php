<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PayrollGroupAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Developer bypass
        |--------------------------------------------------------------------------
        */
        if (
            $user->isDeveloper()
            // can() (unlike hasPermissionTo) returns false instead of throwing
            // when the permission has not been synced into the database yet.
            || $user->can('payroll.all-access')
        ) {
            session([
                'payroll_allowed_groups' => 'all',
            ]);

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | User assigned payroll groups
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | User 1:
        | [1]
        |
        | User 2:
        | [2]
        |
        */

        $allowedGroups = [];

        if ($user->can('payroll.mirasol')) {
            $allowedGroups[] = 1;
        }

        if ($user->can('payroll.gonzales')) {
            $allowedGroups[] = 2;
        }

        /*
        |--------------------------------------------------------------------------
        | No access
        |--------------------------------------------------------------------------
        */

        if (empty($allowedGroups)) {

            session([
                'payroll_allowed_groups' => [],
            ]);

        } else {

            session([
                'payroll_allowed_groups' => $allowedGroups,
            ]);

        }

        return $next($request);
    }
}
