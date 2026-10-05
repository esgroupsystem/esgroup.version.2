<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Enums\LeaveKind;

/** Human Resources → Leaves → Conductor. */
final class ConductorLeaveController extends LeaveController
{
    protected function kind(): LeaveKind
    {
        return LeaveKind::Conductor;
    }
}
