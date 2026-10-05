<?php

declare(strict_types=1);

namespace App\Models;

/** Driver leave record; columns and behaviour come from LeaveRecord. */
class DriverLeave extends LeaveRecord
{
    protected $table = 'driver_leaves';
}
