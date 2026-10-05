<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $full_name
 * @property-read EmployeeAsset|null $asset
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmployeeHistory> $histories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmployeeAttachment> $attachments
 * @property-read EmployeeHistory|null $latestHistory

 * @property string|null $employee_id_permanent
 * @property string|null $employee_id
 * @property string|null $department_id
 * @property string|null $position_id
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $company
 * @property string|null $status
 * @property \Carbon\CarbonInterface|null $date_hired
 * @property string|null $garage
 * @property \Carbon\CarbonInterface|null $date_of_birth
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $emergency_name
 * @property string|null $emergency_contact
 * @property \Carbon\CarbonInterface|null $date_resigned
 * @property string|null $type_of_status
 * @property \Carbon\CarbonInterface|null $last_duty
 * @property \Carbon\CarbonInterface|null $clearance_date
 * @property string|null $last_pay_status
 * @property \Carbon\CarbonInterface|null $last_pay_date
 */
class Employee extends Model
{
    use HasFactory;

    public const COMPANIES = ['Jell Transport', 'ES Transport', 'Kellen Transport', 'Earthstar Transport'];

    public const GARAGES = ['Mirasol', 'Balintawak', 'Gonzales'];

    /** Separation types on the Status Details card. */
    public const STATUS_TYPES = ['Resigned', 'Terminated', 'Terminated due to AWOL', 'Retrenched'];

    public const LAST_PAY_STATUSES = ['Not released', 'Released'];

    /** Disciplinary actions an IR case can carry. */
    public const DISCIPLINARY_ACTIONS = ['Salary Deduction Authorization', 'Suspension', 'Final Warning'];

    /** 201 file documents (asset columns) kept on the private disk. */
    public const DOCUMENTS = ['birth_certificate' => 'Birth Certificate', 'resume' => 'Resume', 'contract' => 'Contract'];

    /** Government ID numbers on the 201 file: key => label (columns <key>_number, <key>_updated_at). */
    public const GOVERNMENT_IDS = ['sss' => 'SSS Number', 'tin' => 'TIN Number', 'philhealth' => 'PhilHealth', 'pagibig' => 'Pag-IBIG'];

    protected $fillable = [
        'employee_id_permanent',
        'employee_biometric_id',
        'employee_id',
        'full_name',
        'department_id',
        'position_id',
        'email',
        'phone_number',
        'company',
        'status',
        'date_hired',
        'garage',
        'date_of_birth',
        'address_1',
        'address_2',
        'emergency_name',
        'emergency_contact',
        'date_resigned',
        'type_of_status',
        'last_duty',
        'clearance_date',
        'last_pay_status',
        'last_pay_date',
    ];

    protected $casts = [
        'date_hired' => 'date',
        'date_of_birth' => 'date',
        'date_resigned' => 'date',
        'last_duty' => 'date',
        'clearance_date' => 'date',
        'last_pay_date' => 'date',
    ];

    /** Manually linked biometric record (see App\Services\HR\BiometricLinkService). */
    /** @return BelongsTo<EmployeeBiometric, $this> */
    public function biometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return HasOne<EmployeeAsset, $this> */
    public function asset(): HasOne
    {
        return $this->hasOne(EmployeeAsset::class);
    }

    /** @return HasMany<EmployeeHistory, $this> */
    public function histories(): HasMany
    {
        return $this->hasMany(EmployeeHistory::class);
    }

    /** @return HasMany<EmployeeAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(EmployeeAttachment::class);
    }

    /** @return HasMany<DriverLeave, $this> */
    public function driverLeaves(): HasMany
    {
        return $this->hasMany(DriverLeave::class, 'employee_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(\App\Models\EmployeeLog::class)->latest();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(\App\Models\Claim::class);
    }

    /** @return HasMany<EmployeeLeave, $this> */
    public function employeeLeaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    /** @return HasOne<EmployeeHistory, $this> */
    public function latestHistory(): HasOne
    {
        return $this->hasOne(EmployeeHistory::class)->latestOfMany();
    }

    /** Age in years, or null when the birth date is unknown. */
    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /** "2 yrs 3 mos", "5 days", "Not started" or "—". */
    public function tenure(): string
    {
        if ($this->date_hired === null) {
            return '—';
        }

        $hired = $this->date_hired->copy()->startOfDay();
        $today = now()->startOfDay();
        if ($hired->gt($today)) {
            return 'Not started';
        }

        $diff = $hired->diff($today);
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y.' yr'.($diff->y > 1 ? 's' : '');
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m.' mo'.($diff->m > 1 ? 's' : '');
        }
        if ($diff->y === 0 && $diff->m === 0) {
            $parts[] = $diff->d.' day'.($diff->d > 1 ? 's' : '');
        }

        return implode(' ', $parts);
    }
}
