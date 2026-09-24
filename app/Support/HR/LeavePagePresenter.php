<?php

declare(strict_types=1);

namespace App\Support\HR;

use App\Models\ConductorLeave;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Shapes the admin-employee, driver and conductor leave pages. The three
 * modules share one React page set and differ only by route prefix,
 * permission prefix and wording.
 */
final class LeavePagePresenter
{
    public const LEAVE_TYPES = ['Medical Leave', 'Emergency Leave', 'Vacation Leave', 'Others'];

    private const KINDS = [
        'employee' => ['route' => 'employee-leave.employee', 'permission' => 'employee-leave', 'noun' => 'Employee', 'title' => 'Admin Employee Leave'],
        'driver' => ['route' => 'driver-leave.driver', 'permission' => 'driver-leave', 'noun' => 'Driver', 'title' => 'Driver Leave'],
        'conductor' => ['route' => 'conductor-leave.conductor', 'permission' => 'conductor-leave', 'noun' => 'Conductor', 'title' => 'Conductor Leave'],
    ];

    /** @param array{leaves: \Illuminate\Pagination\LengthAwarePaginator, today: Carbon, counts: array<string, int>, garageSummary: \Illuminate\Support\Collection} $data */
    public static function index(string $kind, array $data, Request $request): Response
    {
        $config = self::KINDS[$kind];
        $today = $data['today'];
        $user = $request->user();
        $canUpdate = (bool) $user?->can($config['permission'].'.update');

        return Inertia::render('hr/leaves/index', [
            'kind' => self::kindProps($kind),
            'leaves' => $data['leaves']->through(function (EmployeeLeave|DriverLeave|ConductorLeave $leave) use ($config, $today, $canUpdate): array {
                $status = strtolower((string) ($leave->status ?? ''));
                $locked = in_array($status, ['cancelled', 'terminated', 'completed'], true);
                $end = $leave->end_date ? Carbon::parse($leave->end_date, 'Asia/Manila')->startOfDay() : null;
                $afterLeave = $end && $today->gt($end);
                $employee = $leave->employee;
                $notice = fn (string $type, $sentAt, $proof): array => [
                    'sent_at' => $sentAt?->timezone('Asia/Manila')->format('M d, Y h:i A'),
                    'proof_url' => $proof ? route($config['route'].'.proof', [$leave, $type]) : null,
                ];

                return [
                    'id' => $leave->id,
                    'employee' => $employee ? self::employeeRow($employee) : null,
                    'leave_type' => $leave->leave_type,
                    'reason' => $leave->reason,
                    'start_date' => $leave->start_date?->format('M d, Y'),
                    'end_date' => $leave->end_date?->format('M d, Y'),
                    'days' => (int) ($leave->days ?? 0),
                    'notices' => [
                        'first' => $notice('first', $leave->first_notice_sent_at, $leave->first_notice_proof),
                        'second' => $notice('second', $leave->second_notice_sent_at, $leave->second_notice_proof),
                        'final' => $notice('final', $leave->final_notice_sent_at, $leave->final_notice_proof),
                    ],
                    'status' => ['label' => $leave->status_label, 'tone' => $leave->record_status_tone],
                    'remaining' => $leave->remaining,
                    'ready_at' => $leave->ready_for_duty_notified_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
                    'last_action_note' => $leave->last_action_note,
                    'locked' => $locked,
                    'after_leave' => (bool) $afterLeave,
                    'can_update' => $canUpdate,
                    'edit_url' => route($config['route'].'.edit', $leave),
                    'action_url' => route($config['route'].'.action', $leave),
                ];
            }),
            'counts' => $data['counts'],
            'garageSummary' => $data['garageSummary'],
            'filters' => [
                'search' => trim((string) $request->input('search', '')),
                'status' => strtolower(trim((string) $request->input('status', ''))),
                'leave_type' => trim((string) $request->input('leave_type', '')),
                'garage' => trim((string) $request->input('garage', '')),
            ],
            'leaveTypes' => self::LEAVE_TYPES,
            'garages' => EmployeePagePresenter::GARAGES,
            'can' => [
                'create' => (bool) $user?->can($config['permission'].'.create'),
                'update' => $canUpdate,
            ],
            'urls' => [
                'index' => route($config['route'].'.index'),
                'create' => route($config['route'].'.create'),
            ],
        ]);
    }

    /** @param Collection<int, Employee> $employees */
    public static function form(string $kind, EmployeeLeave|DriverLeave|ConductorLeave|null $leave, Collection $employees): Response
    {
        $config = self::KINDS[$kind];

        return Inertia::render('hr/leaves/form', [
            'kind' => self::kindProps($kind),
            'leave' => $leave ? [
                'id' => $leave->id,
                'status' => ['label' => $leave->status ? ucfirst((string) $leave->status) : 'Active', 'tone' => match (strtolower((string) ($leave->status ?? ''))) {
                    'completed' => 'success',
                    'cancelled' => 'secondary',
                    'terminated' => 'danger',
                    'inactive' => 'warning',
                    default => 'primary',
                }],
                'notices' => [
                    'first' => $leave->first_notice_sent_at?->format('M d, Y h:i A'),
                    'second' => $leave->second_notice_sent_at?->format('M d, Y h:i A'),
                    'final' => $leave->final_notice_sent_at?->format('M d, Y h:i A'),
                ],
                'last_action_note' => $leave->last_action_note,
            ] : null,
            'values' => [
                'employee_id' => $leave ? (string) $leave->employee_id : '',
                'leave_type' => (string) ($leave->leave_type ?? self::LEAVE_TYPES[0]),
                'start_date' => $leave?->start_date?->format('Y-m-d') ?? '',
                'end_date' => $leave?->end_date?->format('Y-m-d') ?? '',
                'reason' => (string) ($leave->reason ?? ''),
            ],
            'employees' => $employees->map(fn (Employee $employee): array => [
                'value' => (string) $employee->id,
                ...self::employeeRow($employee),
            ])->values(),
            'leaveTypes' => self::LEAVE_TYPES,
            'urls' => [
                'index' => route($config['route'].'.index'),
                'submit' => $leave ? route($config['route'].'.update', $leave) : route($config['route'].'.store'),
            ],
        ]);
    }

    /** @return array{key: string, noun: string, title: string} */
    private static function kindProps(string $kind): array
    {
        return ['key' => $kind, 'noun' => self::KINDS[$kind]['noun'], 'title' => self::KINDS[$kind]['title']];
    }

    /** @return array<string, string> */
    private static function employeeRow(Employee $employee): array
    {
        return [
            'name' => (string) ($employee->full_name ?? ''),
            'employee_no' => (string) ($employee->employee_id_permanent ?: ($employee->employee_id ?: 'No Employee ID')),
            'position' => (string) ($employee->position?->title ?? 'No position'),
            'garage' => (string) ($employee->garage ?: 'No Garage Assigned'),
            'company' => (string) ($employee->company ?: 'No Company'),
            'status' => (string) ($employee->status ?? 'No Status'),
        ];
    }
}
