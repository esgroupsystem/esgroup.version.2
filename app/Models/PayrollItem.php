<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\PayrollEmployeeNameFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int|null $employee_biometric_id
 * @property int|null $employee_id
 * @property int|null $payroll_employee_salary_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $company_name_snapshot
 * @property string|null $crosschex_id
 * @property string|null $rate_type
 * @property string|float|int $monthly_rate
 * @property string|float|int $daily_rate
 * @property string|float|int $hourly_rate
 * @property string|float|int $minute_rate
 * @property string|float|int $gross_pay
 * @property string|float|int $net_pay
 * @property string|float|int $other_deductions
 * @property string|float|int $sss_employee
 * @property string|float|int $sss_employer
 * @property string|float|int $sss_ec
 * @property string|float|int $philhealth_employee
 * @property string|float|int $philhealth_employer
 * @property string|float|int $pagibig_employee
 * @property string|float|int $pagibig_employer
 * @property array<string, mixed>|null $meta
 * @property-read string $payroll_display_name
 * @property-read Payroll $payroll
 * @property-read EmployeeBiometric|null $employeeBiometric
 * @property-read Employee|null $employee
 * @property-read PayrollEmployeeSalary|null $salaryProfile
 * @property-read PayrollBenefitSettlement|null $benefitSettlement

 * @property int|null $payroll_id
 * @property string|null $biometric_employee_id
 * @property string|float|int $total_scheduled_days
 * @property string|float|int $total_worked_days
 * @property string|float|int $total_payable_days
 * @property string|float|int $total_payable_hours
 * @property int|null $total_worked_minutes
 * @property int|null $total_late_minutes
 * @property int|null $total_undertime_minutes
 * @property int|null $total_overtime_minutes
 * @property int|null $total_night_differential_minutes
 * @property string|float|int $total_absent_days
 * @property string|float|int $total_rest_day_worked
 * @property string|float|int $total_holiday_worked
 * @property string|float|int $total_leave_days
 * @property string|float|int $regular_pay
 * @property string|float|int $late_deduction
 * @property string|float|int $undertime_deduction
 * @property string|float|int $absence_deduction
 * @property string|float|int $overtime_pay
 * @property string|float|int $night_differential_pay
 * @property string|float|int $holiday_pay
 * @property string|float|int $rest_day_pay
 * @property string|float|int $leave_pay
 * @property string|float|int $taxable_compensation
 * @property string|float|int $withholding_tax
 * @property string|float|int $total_employee_government_deductions
 * @property string|float|int $total_employer_government_contributions
 * @property string|float|int $other_additions
 * @property array<string, mixed>|null $meta
 */
class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_biometric_id',
        'employee_id',
        'payroll_employee_salary_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'company_name_snapshot',
        'crosschex_id',

        'rate_type',
        'monthly_rate',
        'daily_rate',
        'hourly_rate',
        'minute_rate',

        'total_scheduled_days',
        'total_worked_days',
        'total_payable_days',
        'total_payable_hours',
        'total_worked_minutes',
        'total_late_minutes',
        'total_undertime_minutes',
        'total_overtime_minutes',
        'total_night_differential_minutes',
        'total_absent_days',
        'total_rest_day_worked',
        'total_holiday_worked',
        'total_leave_days',

        'regular_pay',
        'gross_pay',

        'late_deduction',
        'undertime_deduction',
        'absence_deduction',

        'overtime_pay',
        'night_differential_pay',
        'holiday_pay',
        'rest_day_pay',
        'leave_pay',

        'taxable_compensation',

        'sss_employee',
        'sss_employer',
        'sss_ec',

        'philhealth_employee',
        'philhealth_employer',

        'pagibig_employee',
        'pagibig_employer',

        'withholding_tax',

        'total_employee_government_deductions',
        'total_employer_government_contributions',

        'other_additions',
        'other_deductions',
        'net_pay',

        'meta',
    ];

    protected $casts = [
        'payroll_id' => 'integer',
        'employee_biometric_id' => 'integer',
        'employee_id' => 'integer',
        'payroll_employee_salary_id' => 'integer',

        'monthly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:4',
        'minute_rate' => 'decimal:4',

        'total_scheduled_days' => 'decimal:2',
        'total_worked_days' => 'decimal:2',
        'total_payable_days' => 'decimal:2',
        'total_payable_hours' => 'decimal:2',

        'total_worked_minutes' => 'integer',
        'total_late_minutes' => 'integer',
        'total_undertime_minutes' => 'integer',
        'total_overtime_minutes' => 'integer',
        'total_night_differential_minutes' => 'integer',

        'total_absent_days' => 'decimal:2',
        'total_rest_day_worked' => 'decimal:2',
        'total_holiday_worked' => 'decimal:2',
        'total_leave_days' => 'decimal:2',

        'regular_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',

        'late_deduction' => 'decimal:2',
        'undertime_deduction' => 'decimal:2',
        'absence_deduction' => 'decimal:2',

        'overtime_pay' => 'decimal:2',
        'night_differential_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'rest_day_pay' => 'decimal:2',
        'leave_pay' => 'decimal:2',

        'taxable_compensation' => 'decimal:2',

        'sss_employee' => 'decimal:2',
        'sss_employer' => 'decimal:2',
        'sss_ec' => 'decimal:2',

        'philhealth_employee' => 'decimal:2',
        'philhealth_employer' => 'decimal:2',

        'pagibig_employee' => 'decimal:2',
        'pagibig_employer' => 'decimal:2',

        'withholding_tax' => 'decimal:2',

        'total_employee_government_deductions' => 'decimal:2',
        'total_employer_government_contributions' => 'decimal:2',

        'other_additions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',

        'meta' => 'array',
    ];

    /** @return BelongsTo<Payroll, $this> */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    /** @return BelongsTo<EmployeeBiometric, $this> */
    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(
            EmployeeBiometric::class,
            'employee_biometric_id'
        );
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<PayrollEmployeeSalary, $this> */
    public function salaryProfile(): BelongsTo
    {
        return $this->belongsTo(
            PayrollEmployeeSalary::class,
            'payroll_employee_salary_id'
        );
    }

    /** @return HasOne<BenefitContributionRecord, $this> */
    public function benefitContributionRecord(): HasOne
    {
        return $this->hasOne(BenefitContributionRecord::class, 'payroll_item_id');
    }

    /** @return HasOne<PayrollBenefitSettlement, $this> */
    public function benefitSettlement(): HasOne
    {
        return $this->hasOne(PayrollBenefitSettlement::class, 'payroll_item_id');
    }

    /** @return HasMany<PaymentLog, $this> */
    public function paymentLogs(): HasMany
    {
        return $this->hasMany(
            PaymentLog::class,
            'payroll_item_id'
        );
    }

    public function getPayrollDisplayNameAttribute(): string
    {
        return PayrollEmployeeNameFormatter::display($this->employee_name ?? null);
    }
}
