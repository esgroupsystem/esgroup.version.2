<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Http\Resources\Concerns\FormatsDates;
use App\Models\EmployeeHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * One IR case on the 201 profile: the violation rows that share an IR number
 * (`irGroups` on `hr/employees/show`). The first row carries the shared fields.
 * Build with `new IrCaseResource(['ir_number' => ..., 'records' => Collection<EmployeeHistory>])`.
 */
final class IrCaseResource extends JsonResource
{
    use FormatsDates;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Collection<int, EmployeeHistory> $records */
        $records = collect($this->resource['records'])->values();
        /** @var EmployeeHistory $first */
        $first = $records->first();
        $actions = $this->actions($first->disciplinary_action);
        if ($actions === []) {
            $actions = $records->flatMap(fn (EmployeeHistory $record): array => $this->actions($record->disciplinary_action))->unique()->values()->all();
        }
        $remarks = $records->pluck('remarks')->filter(fn ($value): bool => filled($value))->unique()->values()->all();

        return [
            'id' => $first->id,
            'ir_number' => (string) ($this->resource['ir_number'] ?: 'NO-IR'),
            'count' => $records->count(),
            'actions' => $actions,
            'remarks' => $remarks,
            'recorded' => $this->formatDate($first->created_at, 'M d, Y h:i A') ?? '—',
            'updated' => $this->formatDate($first->updated_at, 'M d, Y h:i A') ?? '—',
            'records' => $records->map(fn (EmployeeHistory $record): array => [
                'offense_id' => $record->offense_id ? (string) $record->offense_id : '',
                'section' => $record->offense?->section ?? '—',
                'type' => $record->offense?->offense_type,
                'description' => (string) ($record->description ?: ($record->offense?->offense_description ?? '')),
            ])->all(),
            'sda_amount' => $first->sda_amount !== null ? (float) $first->sda_amount : null,
            'sda_terms' => $first->sda_terms !== null ? (float) $first->sda_terms : null,
            'sda_start' => $this->formatDate($first->sda_start_date, 'M d, Y') ?? '—',
            'sda_end' => $this->formatDate($first->sda_end_date, 'M d, Y') ?? 'Ongoing',
            'suspension_start' => $this->formatDate($first->suspension_start_date, 'M d, Y') ?? '—',
            'suspension_end' => $this->formatDate($first->suspension_end_date, 'M d, Y') ?? 'Ongoing',
            'values' => [
                'title' => 'Violations',
                'ir_number' => (string) ($first->ir_number ?? ''),
                'offense_id' => $records->map(fn (EmployeeHistory $record): string => $record->offense_id ? (string) $record->offense_id : '')->all(),
                'description' => $records->map(fn (EmployeeHistory $record): string => (string) ($record->description ?? ''))->all(),
                'remarks' => (string) ($remarks[0] ?? ''),
                'disciplinary_action' => $actions,
                'sda_amount' => $first->sda_amount !== null ? (string) $first->sda_amount : '',
                'sda_terms' => $first->sda_terms !== null ? (string) $first->sda_terms : '',
                'sda_start_date' => $this->formatDate($first->sda_start_date, 'Y-m-d') ?? '',
                'sda_end_date' => $this->formatDate($first->sda_end_date, 'Y-m-d') ?? '',
                'suspension_start_date' => $this->formatDate($first->suspension_start_date, 'Y-m-d') ?? '',
                'suspension_end_date' => $this->formatDate($first->suspension_end_date, 'Y-m-d') ?? '',
            ],
            'update_url' => route('employees.staff.history.update', [$first->employee_id, $first->id]),
            'destroy_url' => route('employees.staff.history.destroy', [$first->employee_id, $first->id]),
        ];
    }

    /** @return list<string> stored actions (array or JSON text), trimmed and unique */
    private function actions(mixed $actions): array
    {
        if (is_string($actions)) {
            $decoded = json_decode($actions, true);
            $actions = is_array($decoded) ? $decoded : [$actions];
        }

        return collect(is_array($actions) ? $actions : [])->filter()->map(fn ($action): string => trim((string) $action))->unique()->values()->all();
    }
}
