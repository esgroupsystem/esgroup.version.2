<?php

namespace App\Services\Biometrics;

use App\Models\EmployeeBiometric;
use Illuminate\Database\Eloquent\Builder;

class EmployeeBiometricIdentityService
{
    public function snapshot(EmployeeBiometric $employeeBiometric): array
    {
        return [
            'employee_biometric_id' => $employeeBiometric->id,

            'biometric_employee_id' => $this->firstFilled([
                $employeeBiometric->legacy_biometric_employee_id ?? null,
                $employeeBiometric->source_employee_id ?? null,
                $employeeBiometric->source_crosschex_id ?? null,
                $employeeBiometric->source_employee_no ?? null,
                $employeeBiometric->display_employee_no ?? null,
                $employeeBiometric->source_key ?? null,
            ]),

            'employee_no' => $this->firstFilled([
                $employeeBiometric->effective_employee_no ?? null,
                $employeeBiometric->display_employee_no ?? null,
                $employeeBiometric->source_employee_no ?? null,
                $employeeBiometric->source_employee_id ?? null,
                $employeeBiometric->source_crosschex_id ?? null,
            ]),

            'employee_name' => $this->firstFilled([
                $employeeBiometric->effective_name ?? null,
                $employeeBiometric->display_name ?? null,
                $employeeBiometric->source_employee_name ?? null,
                $employeeBiometric->source_crosschex_account_name ?? null,
                $employeeBiometric->source_crosschex_account ?? null,
            ]) ?? 'Unknown Employee',

            'crosschex_id' => $this->clean($employeeBiometric->source_crosschex_id ?? null),
        ];
    }

    public function resolveFromModel(
        object $model,
        bool $onlyPayrollActive = false
    ): ?EmployeeBiometric {
        if (
            ! empty($model->employeeBiometric)
            && $model->employeeBiometric instanceof EmployeeBiometric
        ) {
            $employeeBiometric = $model->employeeBiometric;

            if (! $onlyPayrollActive || $this->isPayrollActive($employeeBiometric)) {
                return $employeeBiometric;
            }

            return null;
        }

        if (! empty($model->employee_biometric_id)) {
            $query = EmployeeBiometric::query()
                ->whereKey((int) $model->employee_biometric_id);

            if ($onlyPayrollActive) {
                $query->payrollActive();
            }

            /*
             * A canonical FK is authoritative. If that canonical employee is
             * inactive or payroll-excluded, return null rather than falling
             * back to reused legacy IDs that may belong to another account.
             */
            return $query->first();
        }

        return $this->resolve(
            biometricEmployeeId: $this->clean(
                $model->biometric_employee_id ?? null
            ),
            employeeNo: $this->clean($model->employee_no ?? null),
            employeeName: $this->clean($model->employee_name ?? null),
            crosschexId: $this->clean($model->crosschex_id ?? null),
            onlyPayrollActive: $onlyPayrollActive,
            crosschexAccount: $this->clean(
                $model->source_crosschex_account
                    ?? $model->crosschex_account
                    ?? null
            )
        );
    }

    public function resolve(
        ?string $biometricEmployeeId = null,
        ?string $employeeNo = null,
        ?string $employeeName = null,
        ?string $crosschexId = null,
        bool $onlyPayrollActive = false,
        ?string $crosschexAccount = null
    ): ?EmployeeBiometric {
        $identifierValues = collect([
            $biometricEmployeeId,
            $employeeNo,
            $crosschexId,
        ])
            ->map(fn ($value) => $this->clean($value))
            ->filter()
            ->unique()
            ->values();

        $employeeName = $this->clean($employeeName);
        $crosschexAccount = $this->clean($crosschexAccount);

        if ($identifierValues->isEmpty() && $employeeName === null) {
            return null;
        }

        $query = EmployeeBiometric::query();

        if ($onlyPayrollActive) {
            $query->payrollActive();
        }

        if ($crosschexAccount !== null) {
            $query->where(
                'source_crosschex_account',
                $crosschexAccount
            );
        }

        /*
         * Strong identifiers take priority. Do not include the employee name
         * when an ID or employee number is available because two employees may
         * have identical names.
         */
        if ($identifierValues->isNotEmpty()) {
            $query->where(function (Builder $query) use (
                $identifierValues
            ): void {
                foreach ($identifierValues as $value) {
                    $query
                        ->orWhere('source_key', $value)
                        ->orWhere('source_employee_id', $value)
                        ->orWhere('source_employee_no', $value)
                        ->orWhere('display_employee_no', $value)
                        ->orWhere('source_crosschex_id', $value);
                }
            });
        } else {
            $normalizedName = mb_strtolower($employeeName);

            $query->where(function (Builder $query) use (
                $normalizedName
            ): void {
                $query
                    ->whereRaw(
                        'LOWER(TRIM(display_name)) = ?',
                        [$normalizedName]
                    )
                    ->orWhereRaw(
                        'LOWER(TRIM(source_employee_name)) = ?',
                        [$normalizedName]
                    )
                    ->orWhereRaw(
                        'LOWER(TRIM(source_crosschex_account_name)) = ?',
                        [$normalizedName]
                    );
            });
        }

        return $query
            ->orderByDesc('is_payroll_active')
            ->orderByRaw(
                "CASE WHEN employment_status = 'active' THEN 1 ELSE 0 END DESC"
            )
            ->orderByDesc('last_check_time')
            ->orderBy('id')
            ->first();
    }

    public function applyReferenceMatch(
        Builder $query,
        object $reference,
        string $tableName
    ): void {
        /*
         * The canonical local foreign key is authoritative. Do not OR it with
         * legacy/source identifiers because values such as employee ID "1"
         * may exist in more than one CrossChex account.
         */
        if (! empty($reference->employee_biometric_id)) {
            $query->where(
                $tableName.'.employee_biometric_id',
                (int) $reference->employee_biometric_id
            );

            return;
        }

        $crosschexId = $this->clean($reference->crosschex_id ?? null);
        $employeeNo = $this->clean($reference->employee_no ?? null);
        $biometricEmployeeId = $this->clean(
            $reference->biometric_employee_id ?? null
        );
        $employeeName = $this->clean($reference->employee_name ?? null);

        if ($crosschexId !== null) {
            $query->where($tableName.'.crosschex_id', $crosschexId);

            return;
        }

        if ($employeeNo !== null) {
            $query->where($tableName.'.employee_no', $employeeNo);

            return;
        }

        if ($biometricEmployeeId !== null) {
            $query->where(
                $tableName.'.biometric_employee_id',
                $biometricEmployeeId
            );

            return;
        }

        if ($employeeName !== null) {
            $query->whereRaw(
                "LOWER(TRIM({$tableName}.employee_name)) = ?",
                [mb_strtolower($employeeName)]
            );

            return;
        }

        $query->whereRaw('1 = 0');
    }

    public function identityHash(array $data): ?string
    {
        $account = $this->clean(
            $data['source_crosschex_account'] ?? null
        );

        $companyId = $this->clean(
            $data['biometric_company_id'] ?? null
        );

        /*
         * The CrossChex account is the primary identity boundary.
         * Manually changing the biometric company must not change the employee's
         * source identity hash.
         */
        $scope = match (true) {
            $account !== null => 'crosschex:'.mb_strtolower($account),
            $companyId !== null => 'company:'.$companyId,
            default => 'global',
        };

        foreach ([
            'source_employee_id',
            'source_employee_no',
            'display_employee_no',
            'source_crosschex_id',
            'source_employee_name',
            'display_name',
        ] as $field) {
            $value = $this->clean($data[$field] ?? null);

            if ($value !== null) {
                return hash(
                    'sha256',
                    $scope.'|'.$field.'|'.mb_strtolower($value)
                );
            }
        }

        return null;
    }

    public function employeeGroupKey(object|array $row): string
    {
        $employeeBiometricId = is_array($row)
            ? ($row['employee_biometric_id'] ?? null)
            : ($row->employee_biometric_id ?? null);

        if (! empty($employeeBiometricId)) {
            return 'EMPLOYEE_BIOMETRIC:'.(int) $employeeBiometricId;
        }

        $legacyBiometricId = is_array($row)
            ? ($row['biometric_employee_id'] ?? null)
            : ($row->biometric_employee_id ?? null);

        if (! empty($legacyBiometricId)) {
            return 'LEGACY_BIO:'.trim((string) $legacyBiometricId);
        }

        $employeeNo = is_array($row)
            ? ($row['employee_no'] ?? null)
            : ($row->employee_no ?? null);

        if (! empty($employeeNo)) {
            return 'EMPLOYEE_NO:'.trim((string) $employeeNo);
        }

        $crosschexId = is_array($row)
            ? ($row['crosschex_id'] ?? null)
            : ($row->crosschex_id ?? null);

        if (! empty($crosschexId)) {
            return 'CROSSCHEX:'.trim((string) $crosschexId);
        }

        $employeeName = is_array($row)
            ? ($row['employee_name'] ?? null)
            : ($row->employee_name ?? null);

        return 'NAME:'.mb_strtoupper(trim((string) ($employeeName ?: 'UNKNOWN')));
    }

    private function isPayrollActive(EmployeeBiometric $employeeBiometric): bool
    {
        $employmentStatus = mb_strtolower(trim((string) ($employeeBiometric->employment_status ?? '')));
        $employmentActive = $employmentStatus === '' || $employmentStatus === EmployeeBiometric::STATUS_ACTIVE;
        $payrollIncluded = $employeeBiometric->is_payroll_active !== false;

        return $employmentActive && $payrollIncluded;
    }

    public function clean(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $cleaned = $this->clean($value);

            if ($cleaned !== null) {
                return $cleaned;
            }
        }

        return null;
    }
}
