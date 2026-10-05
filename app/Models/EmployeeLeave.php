<?php

declare(strict_types=1);

namespace App\Models;

/** Admin / office leave record; columns and behaviour come from LeaveRecord. */
class EmployeeLeave extends LeaveRecord
{
    protected $table = 'employee_leaves';
}
