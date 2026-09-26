<?php

declare(strict_types=1);

namespace App\Support\HR;

use App\Models\Employee;
use App\Models\EmployeeBiometric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manual link between an HR employee (201 file) and a biometric record
 * (employees.employee_biometric_id), for people whose Employee ID is not
 * encoded the same way in both places. One-to-one on both sides.
 *
 * The option lists put name matches first, so "Lenberd Ilaw" on one side
 * suggests "ILAW, LENBERD" on the other.
 */
final class BiometricLink
{
    /** Biometric records an HR employee can link to (unlinked, or already its own). */
    public static function biometricOptions(Employee $employee): array
    {
        $records = EmployeeBiometric::query()
            ->with('company')
            ->where(fn ($query) => $query
                ->whereNotIn('id', Employee::query()->whereNotNull('employee_biometric_id')->where('id', '!=', $employee->id)->select('employee_biometric_id'))
            )
            ->get();

        return self::rank(
            $records->map(fn (EmployeeBiometric $record): array => [
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

    /** HR employees a biometric record can link to (unlinked, or already its own). */
    public static function employeeOptions(EmployeeBiometric $record): array
    {
        $employees = Employee::query()
            ->with('position')
            ->where(fn ($query) => $query->whereNull('employee_biometric_id')->orWhere('employee_biometric_id', $record->id))
            ->get(['id', 'full_name', 'employee_id_permanent', 'position_id', 'status', 'employee_biometric_id']);

        return self::rank(
            $employees->map(fn (Employee $employee): array => [
                'value' => (string) $employee->id,
                'label' => (string) $employee->full_name,
                'hint' => collect([$employee->employee_id_permanent, $employee->position?->title, $employee->status])->filter()->implode(' · '),
            ])->all(),
            $record->payroll_display_name,
        );
    }

    /** The linked biometric record, shown on the HR profile. */
    public static function biometricSummary(?EmployeeBiometric $record): ?array
    {
        if (! $record) {
            return null;
        }

        return [
            'id' => $record->id,
            'name' => $record->payroll_display_name,
            'employee_no' => $record->display_employee_no ?: $record->source_employee_no,
            'company' => $record->company?->name,
            'active' => $record->employment_status === EmployeeBiometric::STATUS_ACTIVE,
            'last_check' => $record->last_check_time?->format('M d, Y h:i A'),
            'total_logs' => (int) ($record->total_logs ?? 0),
            'edit_url' => route('biometrics.employees.edit', $record),
        ];
    }

    /** Links (or, with null, unlinks) a biometric record to exactly one HR employee. */
    public static function assignEmployee(EmployeeBiometric $record, ?int $employeeId): void
    {
        DB::transaction(function () use ($record, $employeeId): void {
            Employee::query()
                ->where('employee_biometric_id', $record->id)
                ->when($employeeId, fn ($query) => $query->where('id', '!=', $employeeId))
                ->update(['employee_biometric_id' => null]);

            if ($employeeId) {
                Employee::query()->whereKey($employeeId)->update(['employee_biometric_id' => $record->id]);
            }
        });
    }

    /** @param list<array{value: string, label: string, hint: string}> $options */
    private static function rank(array $options, string $name): array
    {
        $target = self::tokens($name);

        return collect($options)
            ->map(function (array $option) use ($target): array {
                $score = count(array_intersect($target, self::tokens($option['label'])));
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
    private static function tokens(string $name): array
    {
        return collect(preg_split('/[^a-z0-9ñ]+/u', Str::lower($name)) ?: [])
            ->filter(fn (string $word): bool => mb_strlen($word) > 1)
            ->unique()
            ->values()
            ->all();
    }
}
