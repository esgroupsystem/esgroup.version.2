<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\ConductorLeave;
use App\Models\DriverLeave;
use App\Models\EmployeeLeave;
use App\Models\LeaveRecord;

/**
 * The three leave modules (Leaves → Admin / Driver / Conductor). They share one
 * workflow, service and repository; everything that differs lives here.
 */
enum LeaveKind: string
{
    case Employee = 'employee';
    case Driver = 'driver';
    case Conductor = 'conductor';

    /** @return class-string<LeaveRecord> */
    public function modelClass(): string
    {
        return match ($this) {
            self::Employee => EmployeeLeave::class,
            self::Driver => DriverLeave::class,
            self::Conductor => ConductorLeave::class,
        };
    }

    /** Route name prefix, e.g. "driver-leave.driver" (+ ".index", ".edit", ...). */
    public function routeName(): string
    {
        return "{$this->value}-leave.{$this->value}";
    }

    /** Permission prefix, e.g. "driver-leave" (+ ".view", ".create", ...). */
    public function permission(): string
    {
        return "{$this->value}-leave";
    }

    /** "Employee", "Driver" or "Conductor". */
    public function noun(): string
    {
        return ucfirst($this->value);
    }

    public function title(): string
    {
        return $this === self::Employee ? 'Admin Employee Leave' : "{$this->noun()} Leave";
    }

    /** Position the employee must hold; null = anyone except drivers and conductors. */
    public function requiredPosition(): ?HrPositionType
    {
        return match ($this) {
            self::Employee => null,
            self::Driver => HrPositionType::Driver,
            self::Conductor => HrPositionType::Conductor,
        };
    }

    /** Private-disk folder for notice proofs. */
    public function proofDirectory(int $leaveId, string $noticeType): string
    {
        return "{$this->value}-leave/notices/{$leaveId}/{$noticeType}";
    }

    /** Flash shown when a leave action fails unexpectedly. */
    public function actionFailedMessage(): string
    {
        return $this === self::Employee
            ? 'The employee leave action could not be completed. Check the application log for details.'
            : "The {$this->value} leave action could not be completed. Check the log for details.";
    }

    /** Wording used by the ready-for-duty reminder command. */
    public function reminderLabel(): string
    {
        return $this === self::Employee ? 'Leave' : "{$this->noun()} Leave";
    }
}
