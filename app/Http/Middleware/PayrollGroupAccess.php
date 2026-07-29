<?php

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
            $user->hasRole('developer')
            || $user->hasPermissionTo('payroll.all-access')
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

        if ($user->hasPermissionTo('payroll.mirasol')) {
            $allowedGroups[] = 1;
        }

        if ($user->hasPermissionTo('payroll.gonzales')) {
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
