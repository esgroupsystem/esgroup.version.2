<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\EmployeeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * One audit-trail entry on the 201 profile. Department / position ids in the changes are
 * shown as names, so pass the id => name maps. Load `user` first.
 *
 * @mixin EmployeeLog
 */
final class EmployeeLogResource extends JsonResource
{
    /** action => [label, tone] */
    private const ACTIONS = [
        'created' => ['Created Employee', 'success'],
        'updated_201_file' => ['Updated 201 File', 'warning'],
        'updated_profile' => ['Updated Profile', 'primary'],
        'updated_status_details' => ['Updated Status Details', 'warning'],
        'uploaded_attachment' => ['Uploaded Attachment', 'info'],
        'deleted_attachment' => ['Deleted Attachment', 'danger'],
        'added_history' => ['Added History', 'info'],
        'removed_history' => ['Removed History', 'danger'],
        'deleted_employee' => ['Deleted Employee', 'danger'],
    ];

    /**
     * @param  Collection<int, string>  $departmentNames
     * @param  Collection<int, string>  $positionTitles
     */
    public function __construct(
        EmployeeLog $log,
        private readonly Collection $departmentNames,
        private readonly Collection $positionTitles,
    ) {
        parent::__construct($log);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        [$label, $tone] = self::ACTIONS[$this->action] ?? [ucwords(str_replace('_', ' ', (string) $this->action)), 'secondary'];
        $meta = is_array($this->meta) ? $this->meta : (json_decode((string) ($this->meta ?? '[]'), true) ?: []);

        $changes = [];
        foreach ((array) ($meta['changed'] ?? []) as $field => $change) {
            [$from, $to] = [$change['from'] ?? null, $change['to'] ?? null];
            $names = match ($field) {
                'department_id' => $this->departmentNames,
                'position_id' => $this->positionTitles,
                default => null,
            };
            if ($names !== null) {
                [$from, $to] = [$names->get((int) $from) ?? $from, $names->get((int) $to) ?? $to];
            }
            $changes[] = [
                'field' => ucwords(str_replace('_', ' ', str_replace('_id', '', (string) $field))),
                'from' => $this->text($from),
                'to' => $this->text($to),
            ];
        }

        return [
            'id' => $this->id,
            'label' => $label,
            'tone' => $tone,
            'actor' => $this->user->full_name ?? ($this->user->name ?? 'System'),
            'date' => $this->created_at?->format('M d, Y'),
            'time' => $this->created_at?->format('h:i A'),
            'changes' => $changes,
        ];
    }

    private function text(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::parse($value)->format('M d, Y');
        }

        return is_scalar($value) ? (string) $value : (string) json_encode($value);
    }
}
