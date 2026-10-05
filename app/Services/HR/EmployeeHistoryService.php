<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\Employee;
use App\Repositories\Contracts\HR\EmployeeHistoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Violation history on the 201 profile. One IR case = one row per offense, all sharing
 * the IR number, remarks and disciplinary actions; editing replaces the whole case.
 */
final class EmployeeHistoryService
{
    private const SDA = 'Salary Deduction Authorization';

    private const SUSPENSION = 'Suspension';

    public function __construct(
        private readonly EmployeeHistoryRepositoryInterface $histories,
        private readonly EmployeeAuditService $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function createViolation(Employee $employee, array $data): void
    {
        $actions = $this->actions($data['disciplinary_action'] ?? []);
        $data = $this->clearUnusedDisciplinaryFields($data, $actions);

        DB::transaction(function () use ($employee, $data, $actions): void {
            $this->createRows($employee, $data, $actions);
            $this->audit->log($employee, 'added_violation_history', [
                'ir_number' => $data['ir_number'],
                'offense_count' => count($data['offense_id']),
                'disciplinary_action' => $actions,
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    public function updateViolation(Employee $employee, int $historyId, array $data): void
    {
        $original = $this->histories->findOrFail($employee, $historyId);
        $actions = $this->actions($data['disciplinary_action'] ?? []);
        $data = $this->clearUnusedDisciplinaryFields($data, $actions);

        DB::transaction(function () use ($employee, $original, $data, $actions): void {
            $this->histories->deleteIrCase($employee, $original->ir_number);
            $this->createRows($employee, $data, $actions);
            $this->audit->log($employee, 'updated_violation_history', [
                'old_ir_number' => $original->ir_number,
                'new_ir_number' => $data['ir_number'],
                'offense_count' => count($data['offense_id']),
                'disciplinary_action' => $actions,
            ]);
        });
    }

    /** Removes the whole IR case (violations) or the single history row. */
    public function delete(Employee $employee, int $historyId): void
    {
        $history = $this->histories->findOrFail($employee, $historyId);

        DB::transaction(function () use ($employee, $history): void {
            if ($history->title === 'Violations' && filled($history->ir_number)) {
                $count = $this->histories->countIrCase($employee, $history->ir_number);
                $this->histories->deleteIrCase($employee, $history->ir_number);
                $this->audit->log($employee, 'removed_violation_history', ['ir_number' => $history->ir_number, 'deleted_count' => $count]);

                return;
            }

            $this->audit->log($employee, 'removed_history', [
                'title' => $history->title,
                'start_date' => $history->start_date,
                'end_date' => $history->end_date,
            ]);
            $this->histories->delete($history);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $actions
     */
    private function createRows(Employee $employee, array $data, array $actions): void
    {
        foreach ($data['offense_id'] as $index => $offenseId) {
            $this->histories->create($employee, [
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

    /** @return list<string> known actions only, without duplicates */
    private function actions(mixed $actions): array
    {
        if (! is_array($actions)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('strval', $actions),
            fn (string $action): bool => in_array($action, Employee::DISCIPLINARY_ACTIONS, true),
        )));
    }

    /**
     * Drops SDA / suspension fields when that action is not selected.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $actions
     * @return array<string, mixed>
     */
    private function clearUnusedDisciplinaryFields(array $data, array $actions): array
    {
        if (! in_array(self::SDA, $actions, true)) {
            foreach (['sda_amount', 'sda_terms', 'sda_start_date', 'sda_end_date'] as $field) {
                $data[$field] = null;
            }
        }

        if (! in_array(self::SUSPENSION, $actions, true)) {
            $data['suspension_start_date'] = null;
            $data['suspension_end_date'] = null;
        }

        return $data;
    }
}
