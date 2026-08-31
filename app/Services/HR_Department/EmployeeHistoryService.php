<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

final class EmployeeHistoryService
{
    public function __construct(private readonly EmployeeAuditService $audit) {}

    /** @param  array<string, mixed>  $data */
    public function createViolation(Employee $employee, array $data): void
    {
        $actions = $this->actions($data['disciplinary_action'] ?? []);
        $data = $this->clearUnusedDisciplinaryFields($data, $actions);

        DB::transaction(function () use ($employee, $data, $actions): void {
            $this->createViolationRows($employee, $data, $actions);
            $this->audit->log($employee, 'added_violation_history', [
                'ir_number' => $data['ir_number'],
                'offense_count' => count($data['offense_id']),
                'disciplinary_action' => $actions,
            ]);
        });
    }

    /** @param  array<string, mixed>  $data */
    public function updateViolation(Employee $employee, int $historyId, array $data): void
    {
        $original = $employee->histories()->findOrFail($historyId);
        $actions = $this->actions($data['disciplinary_action'] ?? []);
        $data = $this->clearUnusedDisciplinaryFields($data, $actions);

        DB::transaction(function () use ($employee, $original, $data, $actions): void {
            $oldIrNumber = $original->ir_number;
            $employee->histories()->where('title', 'Violations')->where('ir_number', $oldIrNumber)->delete();
            $this->createViolationRows($employee, $data, $actions);
            $this->audit->log($employee, 'updated_violation_history', [
                'old_ir_number' => $oldIrNumber,
                'new_ir_number' => $data['ir_number'],
                'offense_count' => count($data['offense_id']),
                'disciplinary_action' => $actions,
            ]);
        });
    }

    public function delete(Employee $employee, int $historyId): void
    {
        $history = $employee->histories()->findOrFail($historyId);
        DB::transaction(function () use ($employee, $history): void {
            if ($history->title === 'Violations' && filled($history->ir_number)) {
                $count = $employee->histories()->where('title', 'Violations')->where('ir_number', $history->ir_number)->count();
                $employee->histories()->where('title', 'Violations')->where('ir_number', $history->ir_number)->delete();
                $this->audit->log($employee, 'removed_violation_history', ['ir_number' => $history->ir_number, 'deleted_count' => $count]);

                return;
            }

            $this->audit->log($employee, 'removed_history', [
                'title' => $history->title,
                'start_date' => $history->start_date,
                'end_date' => $history->end_date,
            ]);
            $history->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $actions
     */
    private function createViolationRows(Employee $employee, array $data, array $actions): void
    {
        foreach ($data['offense_id'] as $index => $offenseId) {
            $employee->histories()->create([
                'title' => 'Violations',
                'ir_number' => $data['ir_number'],
                'offense_id' => $offenseId,
                'description' => $data['description'][$index] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'disciplinary_action' => $actions,
                'sda_amount' => $data['sda_amount'] ?? null,
                'sda_terms' => $data['sda_terms'] ?? null,
                'sda_start_date' => $data['sda_start_date'] ?? null,
                'sda_end_date' => $data['sda_end_date'] ?? null,
                'suspension_start_date' => $data['suspension_start_date'] ?? null,
                'suspension_end_date' => $data['suspension_end_date'] ?? null,
            ]);
        }
    }

    /** @return list<string> */
    private function actions(mixed $actions): array
    {
        if (! is_array($actions)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('strval', $actions), fn (string $action): bool => in_array($action, [
            'Salary Deduction Authorization', 'Suspension', 'Final Warning',
        ], true))));
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $actions
     * @return array<string, mixed>
     */
    private function clearUnusedDisciplinaryFields(array $data, array $actions): array
    {
        if (! in_array('Salary Deduction Authorization', $actions, true)) {
            foreach (['sda_amount', 'sda_terms', 'sda_start_date', 'sda_end_date'] as $field) {
                $data[$field] = null;
            }
        }

        if (! in_array('Suspension', $actions, true)) {
            $data['suspension_start_date'] = null;
            $data['suspension_end_date'] = null;
        }

        return $data;
    }
}
