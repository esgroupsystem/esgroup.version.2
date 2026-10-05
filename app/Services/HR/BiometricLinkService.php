<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\EmployeeBiometric;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manual link between an HR employee (201 file) and a biometric record
 * (employees.employee_biometric_id), for people whose Employee ID is not encoded the
 * same way in both places. One-to-one on both sides. Used by the HR profile and by
 * Scheduling & Rates → Employees.
 *
 * The option lists put name matches first, so "Lenberd Ilaw" on one side suggests
 * "ILAW, LENBERD" on the other.
 */
final class BiometricLinkService
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employees,
        private readonly EmployeeBiometricRepositoryInterface $biometrics,
    ) {}

    /** @return list<array{value: string, label: string, hint: string}> records the employee can link to */
    public function biometricOptions(Employee $employee): array
    {
        return $this->rank(
            $this->biometrics->linkableToEmployee($employee->id)->map(fn (EmployeeBiometric $record): array => [
                'value' => (string) $record->id,
                'label' => $record->payroll_display_name,
                'hint' => collect([
                    $record->display_employee_no ?: $record->source_employee_no,
                    $record->company?->name,
                    $record->employment_status === EmployeeBiometric::STATUS_ACTIVE ? null : 'Inactive',
                ])->filter()->implode(' · '),
            ])->all(),
            (string) $employee->full_name,
        );
    }

    /** @return list<array{value: string, label: string, hint: string}> employees the record can link to */
    public function employeeOptions(EmployeeBiometric $record): array
    {
        return $this->rank(
            $this->employees->biometricLinkCandidates($record->id)->map(fn (Employee $employee): array => [
                'value' => (string) $employee->id,
                'label' => (string) $employee->full_name,
                'hint' => collect([$employee->employee_id_permanent, $employee->position?->title, $employee->status])->filter()->implode(' · '),
            ])->all(),
            $record->payroll_display_name,
        );
    }

    /** Links (or, with null, unlinks) a biometric record to exactly one HR employee. */
    public function assignEmployee(EmployeeBiometric $record, ?int $employeeId): void
    {
        DB::transaction(fn () => $this->employees->moveBiometricLink($record->id, $employeeId));
    }

    /**
     * @param  list<array{value: string, label: string, hint: string}>  $options
     * @return list<array{value: string, label: string, hint: string}>
     */
    private function rank(array $options, string $name): array
    {
        $target = $this->tokens($name);

        return collect($options)
            ->map(function (array $option) use ($target): array {
                $score = count(array_intersect($target, $this->tokens($option['label'])));
                if ($score >= 2 || ($score === 1 && count($target) === 1)) {
                    $option['hint'] = trim('Name match · '.$option['hint'], ' ·');
                }

                return $option + ['score' => $score];
            })
            ->sortBy([['score', 'desc'], ['label', 'asc']])
            ->map(fn (array $option): array => array_diff_key($option, ['score' => true]))
            ->values()
            ->all();
    }

    /** @return list<string> lower-case name words, ignoring punctuation and 1-letter initials */
    private function tokens(string $name): array
    {
        return collect(preg_split('/[^a-z0-9ñ]+/u', Str::lower($name)) ?: [])
            ->filter(fn (string $word): bool => mb_strlen($word) > 1)
            ->unique()
            ->values()
            ->all();
    }
}
