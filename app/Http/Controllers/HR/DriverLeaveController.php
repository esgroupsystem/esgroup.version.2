<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Enums\LeaveKind;

/** Human Resources → Leaves → Driver. */
final class DriverLeaveController extends LeaveController
{
    protected function kind(): LeaveKind
    {
        return LeaveKind::Driver;
    }
}
