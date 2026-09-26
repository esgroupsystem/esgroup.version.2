<?php

declare(strict_types=1);

namespace App\Support\HR;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\HrOffense;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

/** Props for the Employee List and Employee 201 profile pages. */
final class EmployeePagePresenter
{
    public const COMPANIES = ['Jell Transport', 'ES Transport', 'Kellen Transport', 'Earthstar Transport'];

    public const GARAGES = ['Mirasol', 'Balintawak', 'Gonzales'];

    public const STATUS_TYPES = ['Resigned', 'Terminated', 'Terminated due to AWOL', 'Retrenched'];

    public const ACTIONS = ['Salary Deduction Authorization', 'Suspension', 'Final Warning'];

    private const LOG_ACTIONS = [
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

    /** @param array<string, mixed> $data EmployeeDirectoryService::indexData() */
    public static function index(array $data, Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('hr/employees/index', [
            'employees' => $data['employees']->through(fn (Employee $employee): array => [
                'id' => $employee->id,
                'name' => trim((string) $employee->full_name) ?: 'Unnamed Employee',
                'employee_no' => $employee->employee_id_permanent ?: $employee->employee_id,
                'age' => self::age($employee->date_of_birth),
                'position' => $employee->position?->title ?? '—',
                'department' => $employee->department?->name ?? '—',
                'company' => $employee->company ?: '—',
                'garage' => $employee->garage ?: '—',
                'email' => $employee->email,
                'phone' => $employee->phone_number,
                'hired' => self::date($employee->date_hired),
                'tenure' => self::tenure($employee->date_hired),
                'status' => $employee->status ?: 'Active',
                'show_url' => route('employees.staff.show', $employee->id),
                'destroy_url' => route('employees.staff.destroy', $employee->id),
            ]),
            'stats' => $data['employeeStats'],
            'departments' => self::departments($data['departments']),
            'companies' => collect($data['companies'])->values(),
            'garages' => collect($data['garages'])->values(),
            'statusOptions' => $data['statusOptions'],
            'companyOptions' => self::COMPANIES,
            'garageOptions' => self::GARAGES,
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'status' => (string) $request->query('status', ''),
                'company' => (string) $request->query('company', ''),
                'garage' => (string) $request->query('garage', ''),
                'per_page' => (string) $data['employees']->perPage(),
            ],
            'can' => [
                'create' => (bool) $user?->can('employees.create'),
                'delete' => (bool) $user?->can('employees.delete'),
            ],
            'urls' => [
                'index' => route('employees.staff.index'),
                'store' => route('employees.staff.store'),
                'checkPermanentId' => route('employees.staff.checkPermanentId'),
            ],
        ]);
    }

    /** @param array<string, mixed> $data EmployeeProfileService::data() */
    public static function profile(array $data, Request $request): Response
    {
        /** @var Employee $employee */
        $employee = $data['employee'];
        $asset = $employee->asset;
        $user = $request->user();
        $groups = collect($data['groupedIrHistories'])->values()->map(fn (array $group): array => self::irGroup($group));

        return Inertia::render('hr/employees/show', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'position' => $employee->position?->title ?? 'No Position',
                'department' => $employee->department?->name ?? 'No Department',
                'status' => $employee->status ?? 'Active',
                'employee_id_permanent' => $employee->employee_id_permanent,
                'photo_url' => $asset?->profile_picture ? route('employees.staff.profile-picture', $employee) : null,
                'qr_svg' => filled($employee->employee_id_permanent) ? self::qr((string) $employee->employee_id_permanent) : null,
                'hired' => self::date($employee->date_hired),
                'tenure' => $data['tenure'],
                'age' => $data['age'],
                'address_1' => $employee->address_1,
                'address_2' => $employee->address_2,
                'emergency_name' => $employee->emergency_name,
                'emergency_contact' => $employee->emergency_contact,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'company' => $employee->company,
                'garage' => $employee->garage,
            ],
            'biometric' => BiometricLink::biometricSummary($employee->biometric()->with('company')->first()),
            'biometricOptions' => $user?->can('employees.update') ? BiometricLink::biometricOptions($employee) : [],
            'profileValues' => [
                'employee_id_permanent' => (string) ($employee->employee_id_permanent ?? ''),
                'full_name' => (string) ($employee->full_name ?? ''),
                'date_of_birth' => self::input($employee->date_of_birth),
                'status' => (string) ($employee->status ?: 'Active'),
                'date_hired' => self::input($employee->date_hired),
                'company' => (string) ($employee->company ?? ''),
                'department_id' => $employee->department_id ? (string) $employee->department_id : '',
                'position_id' => $employee->position_id ? (string) $employee->position_id : '',
                'garage' => (string) ($employee->garage ?: 'Mirasol'),
                'email' => (string) ($employee->email ?? ''),
                'phone_number' => (string) ($employee->phone_number ?? ''),
                'address_1' => (string) ($employee->address_1 ?? ''),
                'address_2' => (string) ($employee->address_2 ?? ''),
                'emergency_name' => (string) ($employee->emergency_name ?? ''),
                'emergency_contact' => (string) ($employee->emergency_contact ?? ''),
            ],
            'assets' => [
                'updated' => $asset?->updated_at?->diffForHumans(),
                'numbers' => collect([
                    'sss' => 'SSS Number',
                    'tin' => 'TIN Number',
                    'philhealth' => 'PhilHealth',
                    'pagibig' => 'Pag-IBIG',
                ])->map(fn (string $label, string $key): array => [
                    'key' => $key,
                    'label' => $label,
                    'value' => $asset?->{$key.'_number'},
                    'date' => self::date($asset?->{$key.'_updated_at'}, null),
                    'date_input' => self::input($asset?->{$key.'_updated_at'}),
                ])->values(),
                'files' => collect([
                    'birth_certificate' => 'Birth Certificate',
                    'resume' => 'Resume',
                    'contract' => 'Contract',
                ])->map(fn (string $label, string $key): array => [
                    'key' => $key,
                    'label' => $label,
                    'url' => $asset?->{$key} ? route('employees.staff.asset-file', [$employee, $key]) : null,
                    'name' => $asset?->{$key} ? basename((string) $asset->{$key}) : null,
                    'date' => self::date($asset?->{$key.'_updated_at'}, null),
                ])->values(),
            ],
            'statusDetails' => [
                'date_resigned' => self::input($employee->date_resigned),
                'type_of_status' => (string) ($employee->type_of_status ?? ''),
                'last_duty' => self::input($employee->last_duty),
                'clearance_date' => self::input($employee->clearance_date),
                'last_pay_status' => (string) ($employee->last_pay_status ?? ''),
                'last_pay_date' => self::input($employee->last_pay_date),
            ],
            'attachments' => $employee->attachments->map(fn ($attachment): array => [
                'id' => $attachment->id,
                'name' => $attachment->file_name,
                'meta' => strtoupper((string) $attachment->mime_type).' • '.round(((int) $attachment->size) / 1024, 1).' KB',
                'download_url' => route('employees.staff.attachments.download', [$employee->id, $attachment->id]),
                'destroy_url' => route('employees.staff.attachments.destroy', [$employee->id, $attachment->id]),
            ])->values(),
            'irGroups' => $groups,
            'irStats' => [
                'irs' => $groups->count(),
                'violations' => $groups->sum('count'),
                'sda' => $groups->filter(fn (array $group): bool => in_array('Salary Deduction Authorization', $group['actions'], true))->count(),
                'suspension' => $groups->filter(fn (array $group): bool => in_array('Suspension', $group['actions'], true))->count(),
                'final_warning' => $groups->filter(fn (array $group): bool => in_array('Final Warning', $group['actions'], true))->count(),
                'remarks' => $groups->filter(fn (array $group): bool => $group['remarks'] !== [])->count(),
            ],
            'logs' => $data['logs']->through(fn ($log): array => self::log($log, $data['deptMap'], $data['posMap'])),
            'departments' => self::departments($data['departments']),
            'offenses' => $data['offenses']->map(fn (HrOffense $offense): array => [
                'value' => (string) $offense->id,
                'label' => (string) $offense->section,
                'hint' => Str::limit((string) $offense->offense_description, 90),
                'description' => (string) $offense->offense_description,
            ])->values(),
            'options' => [
                'statuses' => \App\Enums\EmployeeStatus::values(),
                'companies' => self::COMPANIES,
                'garages' => self::GARAGES,
                'statusTypes' => self::STATUS_TYPES,
                'actions' => self::ACTIONS,
            ],
            'can' => ['update' => (bool) $user?->can('employees.update')],
            'urls' => [
                'back' => session('employees_back_url', route('employees.staff.index')),
                'show' => route('employees.staff.show', $employee->id),
                'print' => route('employees.staff.print', $employee->id),
                'update' => route('employees.update', $employee->id),
                'assets' => route('employees.assets.update', $employee->id),
                'statusDetails' => route('employees.status-details.update', $employee->id),
                'attachments' => route('employees.staff.attachments.store', $employee->id),
                'historyStore' => route('employees.staff.history.store', $employee->id),
                'checkPermanentId' => route('employees.staff.checkPermanentId'),
                'biometricLink' => route('employees.biometric-link.update', $employee->id),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private static function irGroup(array $group): array
    {
        /** @var Collection<int, EmployeeHistory> $records */
        $records = collect($group['records']);
        $first = $group['first_record'];
        $actions = self::actions($group['actions'] ?? []);
        if ($actions === []) {
            $actions = $records->flatMap(fn ($record) => self::actions($record->disciplinary_action))->unique()->values()->all();
        }
        $remarks = $records->pluck('remarks')->filter(fn ($value): bool => filled($value))->unique()->values()->all();

        return [
            'id' => $first->id,
            'ir_number' => $group['ir_number'] ?: 'NO-IR',
            'count' => (int) $group['count'],
            'actions' => $actions,
            'remarks' => $remarks,
            'recorded' => self::dateTime($first->created_at),
            'updated' => self::dateTime($first->updated_at),
            'records' => $records->values()->map(fn ($record): array => [
                'offense_id' => $record->offense_id ? (string) $record->offense_id : '',
                'section' => $record->offense?->section ?? '—',
                'type' => $record->offense?->offense_type,
                'description' => (string) ($record->description ?: ($record->offense?->offense_description ?? '')),
            ])->all(),
            'sda_amount' => $first->sda_amount !== null ? (float) $first->sda_amount : null,
            'sda_terms' => $first->sda_terms !== null ? (float) $first->sda_terms : null,
            'sda_start' => self::date($first->sda_start_date),
            'sda_end' => self::date($first->sda_end_date, 'Ongoing'),
            'suspension_start' => self::date($first->suspension_start_date),
            'suspension_end' => self::date($first->suspension_end_date, 'Ongoing'),
            'values' => [
                'title' => 'Violations',
                'ir_number' => (string) ($first->ir_number ?? ''),
                'offense_id' => $records->map(fn ($record): string => $record->offense_id ? (string) $record->offense_id : '')->values()->all(),
                'description' => $records->map(fn ($record): string => (string) ($record->description ?? ''))->values()->all(),
                'remarks' => (string) ($remarks[0] ?? ''),
                'disciplinary_action' => $actions,
                'sda_amount' => $first->sda_amount !== null ? (string) $first->sda_amount : '',
                'sda_terms' => $first->sda_terms !== null ? (string) $first->sda_terms : '',
                'sda_start_date' => self::input($first->sda_start_date),
                'sda_end_date' => self::input($first->sda_end_date),
                'suspension_start_date' => self::input($first->suspension_start_date),
                'suspension_end_date' => self::input($first->suspension_end_date),
            ],
            'update_url' => route('employees.staff.history.update', [$first->employee_id, $first->id]),
            'destroy_url' => route('employees.staff.history.destroy', [$first->employee_id, $first->id]),
        ];
    }

    /** @return array<string, mixed> */
    private static function log($log, Collection $deptMap, Collection $posMap): array
    {
        [$label, $tone] = self::LOG_ACTIONS[$log->action] ?? [ucwords(str_replace('_', ' ', (string) $log->action)), 'secondary'];
        $meta = is_array($log->meta) ? $log->meta : (json_decode((string) ($log->meta ?? '[]'), true) ?: []);
        $format = static function ($value): string {
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
        };

        $changes = [];
        foreach ((array) ($meta['changed'] ?? []) as $field => $change) {
            $from = $change['from'] ?? null;
            $to = $change['to'] ?? null;
            if ($field === 'department_id') {
                [$from, $to] = [$deptMap->get((int) $from) ?? $from, $deptMap->get((int) $to) ?? $to];
            }
            if ($field === 'position_id') {
                [$from, $to] = [$posMap->get((int) $from) ?? $from, $posMap->get((int) $to) ?? $to];
            }
            $changes[] = [
                'field' => ucwords(str_replace('_', ' ', str_replace('_id', '', (string) $field))),
                'from' => $format($from),
                'to' => $format($to),
            ];
        }

        return [
            'id' => $log->id,
            'label' => $label,
            'tone' => $tone,
            'actor' => $log->user->full_name ?? ($log->user->name ?? 'System'),
            'date' => $log->created_at?->format('M d, Y'),
            'time' => $log->created_at?->format('h:i A'),
            'changes' => $changes,
        ];
    }

    /** @return list<array{id: string, name: string, positions: list<array{id: string, title: string}>}> */
    private static function departments($departments): array
    {
        return collect($departments)->map(fn (Department $department): array => [
            'id' => (string) $department->id,
            'name' => (string) $department->name,
            'positions' => $department->positions->map(fn (Position $position): array => ['id' => (string) $position->id, 'title' => (string) $position->title])->values()->all(),
        ])->values()->all();
    }

    /** @return list<string> */
    private static function actions($actions): array
    {
        if (is_string($actions)) {
            $decoded = json_decode($actions, true);
            $actions = is_array($decoded) ? $decoded : [$actions];
        }

        return collect(is_array($actions) ? $actions : [])->filter()->map(fn ($action): string => trim((string) $action))->unique()->values()->all();
    }

    private static function qr(string $value): ?string
    {
        try {
            return (string) QrCode::size(82)->style('round')->margin(0)->backgroundColor(255, 255, 255)->generate($value);
        } catch (Throwable) {
            return null;
        }
    }

    private static function age($value): ?int
    {
        try {
            return filled($value) ? Carbon::parse($value)->age : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function tenure($hired): string
    {
        try {
            if (blank($hired)) {
                return '—';
            }
            $hired = Carbon::parse($hired)->startOfDay();
        } catch (Throwable) {
            return '—';
        }

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

    private static function date($value, ?string $fallback = '—'): ?string
    {
        try {
            return filled($value) ? Carbon::parse($value)->format('M d, Y') : $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    private static function dateTime($value): string
    {
        try {
            return filled($value) ? Carbon::parse($value)->format('M d, Y h:i A') : '—';
        } catch (Throwable) {
            return '—';
        }
    }

    private static function input($value): string
    {
        try {
            return filled($value) ? Carbon::parse($value)->format('Y-m-d') : '';
        } catch (Throwable) {
            return '';
        }
    }
}
