<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollBenefitSettlement extends Model
{
    public const MODE_AUTO_CAP = 'auto_cap';
    public const MODE_EMPLOYER_ADVANCE = 'employer_advance';
    public const MODE_COLLECT_FULL = 'collect_full';

    public const MODES = [
        self::MODE_AUTO_CAP,
        self::MODE_EMPLOYER_ADVANCE,
        self::MODE_COLLECT_FULL,
    ];

    protected $fillable = [
        'payroll_id',
        'payroll_item_id',
        'employee_biometric_id',
        'mode',
        'sss_employee_reimbursement',
        'philhealth_employee_reimbursement',
        'pagibig_employee_reimbursement',
        'reason',
        'created_by',
        'updated_by',
        'meta',
    ];

    protected $casts = [
        'payroll_id' => 'integer',
        'payroll_item_id' => 'integer',
        'employee_biometric_id' => 'integer',
        'sss_employee_reimbursement' => 'decimal:2',
        'philhealth_employee_reimbursement' => 'decimal:2',
        'pagibig_employee_reimbursement' => 'decimal:2',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'meta' => 'array',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
