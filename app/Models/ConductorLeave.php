<?php

declare(strict_types=1);

namespace App\Models;

/** Conductor leave record; columns and behaviour come from LeaveRecord. */
class ConductorLeave extends LeaveRecord
{
    protected $table = 'conductor_leaves';
}
