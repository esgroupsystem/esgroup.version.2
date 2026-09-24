<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Enums\JobOrderRepairType;
use App\Enums\JobOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreJobOrderMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateJobOrderMaintenanceNumberRequest;
use App\Http\Requests\Maintenance\UpdateJobOrderMaintenanceStatusRequest;
use App\Models\Bus;
use App\Models\JobOrderMaintenance;
use App\Services\Maintenance\JobOrderMaintenanceDirectoryService;
use App\Services\Maintenance\JobOrderMaintenanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final class JobOrderMaintenanceController extends Controller
{
    public function __construct(
        private readonly JobOrderMaintenanceDirectoryService $directoryService,
        private readonly JobOrderMaintenanceService $jobOrderMaintenanceService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $filters = $this->directoryService->filters($request);

        $jobOrders = $this->directoryService->apply(
            query: JobOrderMaintenance::query()->with(['bus', 'creator', 'statusPeriods']),
            filters: $filters,
            includeStatus: true
        )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $buses = Bus::query()
            ->select('id', 'bus_no', 'plate_no', 'company', 'garage')
            ->orderBy('bus_no')
            ->get();

        $groupedStatusCounts = $this->directoryService->apply(
            query: JobOrderMaintenance::query(),
            filters: $filters,
            includeStatus: false
        )
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCards = collect(JobOrderStatus::cases())
            ->map(function (JobOrderStatus $status) use ($groupedStatusCounts) {
                return [
                    'value' => $status->value,
                    'label' => $status->label(),
                    'badge_class' => $status->badgeClass(),
                    'icon' => $status->icon(),
                    'description' => $status->description(),
                    'count' => (int) ($groupedStatusCounts[$status->value] ?? 0),
                ];
            });

        $user = $request->user();

        return Inertia::render('maintenance/job-orders/index', [
            'jobOrders' => $jobOrders->through(fn (JobOrderMaintenance $jobOrder): array => [
                ...$this->summary($jobOrder),
                'work' => $jobOrder->description_of_work,
                'repair_types' => $jobOrder->repair_type_enums->map(fn (JobOrderRepairType $type): array => ['value' => $type->value, 'label' => $type->label()])->values(),
                'mechanics' => $jobOrder->mechanic_names_list !== [] ? $jobOrder->mechanic_names_label : null,
                'odometer' => $jobOrder->odometer_reading !== null ? number_format($jobOrder->odometer_reading).' km' : null,
                'odometer_note' => $jobOrder->odometer_comparison_label,
                'odometer_lower' => (bool) $jobOrder->is_odometer_lower_than_last,
                'downtime' => $jobOrder->total_downtime_label,
                'downtime_running' => (bool) $jobOrder->is_downtime_running,
                'created_date' => $jobOrder->created_at->format('M d, Y'),
                'created_time' => $jobOrder->created_at->format('h:i A'),
                'show_url' => route('maintenance.job-orders.show', $jobOrder),
                'edit_status_url' => route('maintenance.job-orders.edit-status', $jobOrder),
            ]),
            'buses' => $buses->map(fn (Bus $bus): array => ['value' => (string) $bus->id, 'label' => $bus->bus_no.' — '.($bus->plate_no ?? 'No Plate')])->values(),
            'statusCards' => $statusCards->map(fn (array $card): array => collect($card)->except(['badge_class', 'icon'])->all())->values(),
            'statuses' => self::statusOptions(),
            'filters' => [...$filters, 'bus_id' => $filters['bus_id'] ? (string) $filters['bus_id'] : ''],
            'can' => [
                'create' => (bool) $user?->can('job-orders.create'),
                'updateStatus' => (bool) $user?->can('job-orders.update-status'),
            ],
            'urls' => [
                'index' => route('maintenance.job-orders.index'),
                'create' => route('maintenance.job-orders.create'),
                'export' => route('maintenance.job-orders.export'),
            ],
        ]);
    }

    /** @return array<string, mixed> Shared header fields for list rows and detail pages. */
    private function summary(JobOrderMaintenance $jobOrder): array
    {
        return [
            'id' => $jobOrder->id,
            'job_order_no' => $jobOrder->job_order_no,
            'creator' => $jobOrder->creator?->name ?? 'System',
            'bus_no' => $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A'),
            'plate_no' => $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A'),
            'company' => $jobOrder->bus?->company ?? ($jobOrder->company_snapshot ?? 'N/A'),
            'garage' => $jobOrder->bus?->garage ?? ($jobOrder->garage_snapshot ?? 'N/A'),
            'requester' => $jobOrder->full_name ?: 'Not specified',
            'status' => [
                'value' => $jobOrder->status?->value,
                'label' => $jobOrder->status_label,
                'description' => $jobOrder->status_description,
            ],
        ];
    }

    /** @return list<array{value: string, label: string, description: string}> */
    private static function statusOptions(): array
    {
        return collect(JobOrderStatus::cases())->map(fn (JobOrderStatus $status): array => [
            'value' => $status->value,
            'label' => $status->label(),
            'description' => $status->description(),
        ])->values()->all();
    }

    /** @return list<array{value: string, label: string}> */
    private static function repairTypeOptions(): array
    {
        return collect(JobOrderRepairType::cases())->map(fn (JobOrderRepairType $type): array => ['value' => $type->value, 'label' => $type->label()])->values()->all();
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $filters = $this->directoryService->filters($request);

        $exportType = strtolower($request->string('export_type')->toString());

        if (! in_array($exportType, ['csv', 'xls'], true)) {
            $exportType = 'csv';
        }

        $query = $this->directoryService->apply(
            query: JobOrderMaintenance::query()->with(['bus', 'creator', 'statusPeriods']),
            filters: $filters,
            includeStatus: true
        )
            ->latest();

        $fileName = $this->exportFileName($exportType, $filters);

        return match ($exportType) {
            'xls' => $this->exportExcel($query, $fileName),
            default => $this->exportCsv($query, $fileName),
        };
    }

    public function create(): InertiaResponse
    {
        $buses = Bus::query()
            ->with('latestJobOrderMaintenanceWithOdometer')
            ->select([
                'id',
                'bus_no',
                'plate_no',
                'company',
                'garage',
                'operational_status',
                'sale_status',
            ])
            ->orderBy('bus_no')
            ->get();

        return Inertia::render('maintenance/job-orders/create', [
            'buses' => $buses->map(fn (Bus $bus): array => [
                'value' => (string) $bus->id,
                'label' => $bus->bus_no.' — '.($bus->plate_no ?: 'No Plate'),
                'hint' => ($bus->company ?: 'No Company').' — '.($bus->garage ?: 'No Garage'),
                'bus_no' => $bus->bus_no,
                'plate_no' => $bus->plate_no ?: '—',
                'garage' => $bus->garage ?: '—',
                'status' => $bus->operational_status_label,
                'last_odometer' => $bus->latestJobOrderMaintenanceWithOdometer?->odometer_reading,
            ])->values(),
            'repairTypes' => self::repairTypeOptions(),
            'urls' => [
                'index' => route('maintenance.job-orders.index'),
                'store' => route('maintenance.job-orders.store'),
            ],
        ]);
    }

    public function store(
        StoreJobOrderMaintenanceRequest $request,
    ): RedirectResponse {
        try {
            $jobOrderMaintenance = $this->jobOrderMaintenanceService->create(
                data: $request->validated(),
                userId: $request->user()?->id
            );

            return redirect()
                ->route('maintenance.job-orders.show', $jobOrderMaintenance)
                ->with('success', 'Maintenance job order created successfully.');
        } catch (Throwable $e) {
            Log::error('Maintenance job order creation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()?->id,
                'payload' => $request->safe()->except(['_token']),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create maintenance job order. Please check the details and try again.');
        }
    }

    public function show(Request $request, JobOrderMaintenance $jobOrderMaintenance): InertiaResponse
    {
        $jobOrderMaintenance->load([
            'bus',
            'creator',
            'histories.user',
            'statusPeriods.changedBy',
        ]);
        $jobOrder = $jobOrderMaintenance;
        $user = $request->user();
        $breakdown = $jobOrder->downtime_breakdown;
        $km = static fn (?int $value): ?string => $value !== null ? number_format($value).' km' : null;

        return Inertia::render('maintenance/job-orders/show', [
            'jobOrder' => [
                ...$this->summary($jobOrder),
                'created' => $jobOrder->created_at->format('M d, Y h:i A'),
                'created_date' => $jobOrder->created_at->format('M d, Y'),
                'created_time' => $jobOrder->created_at->format('h:i A'),
                'updated' => $jobOrder->updated_at?->format('M d, Y h:i A') ?? 'N/A',
                'work' => $jobOrder->description_of_work,
                'mechanics' => $jobOrder->mechanic_names_list,
                'mechanics_label' => $jobOrder->mechanic_names_label,
                'repair_types' => $jobOrder->repair_type_enums->map(fn (JobOrderRepairType $type): array => ['value' => $type->value, 'label' => $type->label()])->values(),
                'repair_types_label' => $jobOrder->repair_types_label,
                'downtime' => $jobOrder->total_downtime_label,
                'downtime_running' => (bool) $jobOrder->is_downtime_running,
                'downtime_breakdown' => collect(JobOrderStatus::downtimeStatuses())->map(fn (JobOrderStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                    'duration' => $breakdown[$status->value]['label'] ?? '—',
                    'current' => $jobOrder->status === $status,
                ])->values(),
                'periods' => $jobOrder->statusPeriods->map(fn ($period): array => [
                    'id' => $period->id,
                    'status' => $period->status->value,
                    'label' => $period->status->label(),
                    'started' => $period->started_at?->format('M d, Y h:i A') ?? 'N/A',
                    'ended' => $period->ended_at?->format('M d, Y h:i A'),
                    'duration' => $period->duration_label,
                    'by' => $period->changedBy?->name ?? 'System',
                ])->values(),
                'odometer' => $km($jobOrder->odometer_reading),
                'last_odometer' => $km($jobOrder->last_odometer_reading),
                'odometer_difference' => $km($jobOrder->odometer_difference),
                'odometer_lower' => (bool) $jobOrder->is_odometer_lower_than_last,
                'odometer_note' => $jobOrder->odometer_comparison_label,
                'histories' => $jobOrder->histories->map(fn ($history): array => [
                    'id' => $history->id,
                    'action' => $history->action,
                    'at' => $history->created_at?->format('M d, Y h:i A'),
                    'by' => $history->user?->name ?? 'System',
                    'old' => $history->old_value,
                    'new' => $history->new_value,
                    'remarks' => $history->remarks,
                ])->values(),
            ],
            'statuses' => self::statusOptions(),
            'can' => [
                'updateNumber' => (bool) $user?->can('job-orders.update-number'),
                'updateStatus' => (bool) $user?->can('job-orders.update-status'),
            ],
            'urls' => [
                'index' => route('maintenance.job-orders.index'),
                'csv' => route('maintenance.job-orders.export-single', ['jobOrderMaintenance' => $jobOrder, 'export_type' => 'csv']),
                'xls' => route('maintenance.job-orders.export-single', ['jobOrderMaintenance' => $jobOrder, 'export_type' => 'xls']),
                'editNumber' => route('maintenance.job-orders.edit-number', $jobOrder),
                'editStatus' => route('maintenance.job-orders.edit-status', $jobOrder),
            ],
        ]);
    }

    public function editStatus(JobOrderMaintenance $jobOrderMaintenance): InertiaResponse
    {
        $jobOrderMaintenance->load([
            'bus',
            'creator',
            'statusPeriods',
        ]);

        return Inertia::render('maintenance/job-orders/edit-status', [
            'jobOrder' => [
                ...$this->summary($jobOrderMaintenance),
                'work' => Str::limit((string) $jobOrderMaintenance->description_of_work, 220),
            ],
            'values' => [
                'status' => (string) ($jobOrderMaintenance->status?->value ?? ''),
                'mechanic_names' => $jobOrderMaintenance->mechanic_names_list !== [] ? array_values($jobOrderMaintenance->mechanic_names_list) : [''],
                'repair_types' => array_values(array_map('strval', $jobOrderMaintenance->repair_types ?? [])),
                'remarks' => '',
            ],
            'statuses' => self::statusOptions(),
            'repairTypes' => self::repairTypeOptions(),
            'urls' => [
                'show' => route('maintenance.job-orders.show', $jobOrderMaintenance),
                'update' => route('maintenance.job-orders.update-status', $jobOrderMaintenance),
            ],
        ]);
    }

    public function updateStatus(
        UpdateJobOrderMaintenanceStatusRequest $request,
        JobOrderMaintenance $jobOrderMaintenance,
    ): RedirectResponse {
        try {
            $validated = $request->validated();

            $this->jobOrderMaintenanceService->updateStatus(
                jobOrderMaintenance: $jobOrderMaintenance,
                status: JobOrderStatus::from($validated['status']),
                userId: $request->user()?->id,
                remarks: $validated['remarks'] ?? null,
                mechanicNames: $validated['mechanic_names'] ?? [],
                repairTypes: $validated['repair_types'] ?? []
            );

            return redirect()
                ->route('maintenance.job-orders.show', $jobOrderMaintenance)
                ->with('success', 'Maintenance job order status updated successfully.');
        } catch (Throwable $e) {
            Log::error('Maintenance job order status update failed', [
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update maintenance status. Please try again.');
        }
    }

    public function editNumber(JobOrderMaintenance $jobOrderMaintenance): InertiaResponse
    {
        $jobOrderMaintenance->load(['bus', 'creator']);

        return Inertia::render('maintenance/job-orders/edit-number', [
            'jobOrder' => $this->summary($jobOrderMaintenance),
            'urls' => [
                'show' => route('maintenance.job-orders.show', $jobOrderMaintenance),
                'update' => route('maintenance.job-orders.update-number', $jobOrderMaintenance),
            ],
        ]);
    }

    public function updateNumber(
        UpdateJobOrderMaintenanceNumberRequest $request,
        JobOrderMaintenance $jobOrderMaintenance,
    ): RedirectResponse {
        try {
            $validated = $request->validated();

            $this->jobOrderMaintenanceService->updateJobOrderNumber(
                jobOrderMaintenance: $jobOrderMaintenance,
                jobOrderNo: $validated['job_order_no'],
                userId: $request->user()?->id,
                remarks: $validated['remarks'] ?? null
            );

            return redirect()
                ->route('maintenance.job-orders.show', $jobOrderMaintenance)
                ->with('success', 'Job order number updated successfully.');
        } catch (Throwable $e) {
            Log::error('Maintenance job order number update failed', [
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update job order number. Please try again.');
        }
    }

    private function exportCsv(Builder $query, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $this->exportHeadings());

            $query->chunk(500, function ($jobOrders) use ($handle): void {
                /** @var \Illuminate\Database\Eloquent\Collection<int, JobOrderMaintenance> $jobOrders */
                foreach ($jobOrders as $jobOrder) {
                    /** @var JobOrderMaintenance $jobOrder */
                    fputcsv($handle, $this->exportRow($jobOrder));
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportExcel(Builder $query, string $fileName): Response
    {
        $html = '<table border="1">';
        $html .= '<thead><tr>';

        foreach ($this->exportHeadings() as $heading) {
            $html .= '<th>'.e($heading).'</th>';
        }

        $html .= '</tr></thead><tbody>';

        $query->chunk(500, function ($jobOrders) use (&$html): void {
            /** @var \Illuminate\Database\Eloquent\Collection<int, JobOrderMaintenance> $jobOrders */
            foreach ($jobOrders as $jobOrder) {
                /** @var JobOrderMaintenance $jobOrder */
                $html .= '<tr>';

                foreach ($this->exportRow($jobOrder) as $value) {
                    $html .= '<td>'.e((string) $value).'</td>';
                }

                $html .= '</tr>';
            }
        });

        $html .= '</tbody></table>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function exportHeadings(): array
    {
        return [
            'Job Order No.',
            'Bus No.',
            'Plate No.',
            'Company',
            'Garage',
            'Requester',
            'Mechanic(s)',
            'Repair Type(s)',
            'Description of Work',
            'Odometer Reading',
            'Last Odometer Reading',
            'Odometer Difference',
            'Odometer Warning',
            'Standby Downtime',
            'Waiting Parts Downtime',
            'On Going Repair Downtime',
            'Total Downtime',
            'Downtime Counter',
            'Status',
            'Created By',
            'Created Date',
            'Created Time',
        ];
    }

    private function exportRow(JobOrderMaintenance $jobOrder): array
    {
        return [
            $jobOrder->job_order_no,
            $jobOrder->bus->bus_no ?? $jobOrder->bus_no_snapshot ?? 'N/A',
            $jobOrder->bus->plate_no ?? $jobOrder->plate_no_snapshot ?? 'N/A',
            $jobOrder->bus->company ?? $jobOrder->company_snapshot ?? 'N/A',
            $jobOrder->bus->garage ?? $jobOrder->garage_snapshot ?? 'N/A',
            $jobOrder->full_name ?: 'Not specified',
            $jobOrder->mechanic_names_label,
            $jobOrder->repair_types_label,
            $jobOrder->description_of_work,
            $jobOrder->odometer_reading !== null ? $jobOrder->odometer_reading : '',
            $jobOrder->last_odometer_reading !== null ? $jobOrder->last_odometer_reading : '',
            $jobOrder->odometer_difference !== null ? $jobOrder->odometer_difference : '',
            $jobOrder->is_odometer_lower_than_last ? 'Current reading is lower than last reading' : '',
            $jobOrder->downtime_breakdown[JobOrderStatus::Standby->value]['label'],
            $jobOrder->downtime_breakdown[JobOrderStatus::WaitingParts->value]['label'],
            $jobOrder->downtime_breakdown[JobOrderStatus::OnGoingRepair->value]['label'],
            $jobOrder->total_downtime_label,
            $jobOrder->is_downtime_running ? 'Running' : 'Stopped',
            $jobOrder->status_label,
            $jobOrder->creator->name ?? 'System',
            $jobOrder->created_at?->format('Y-m-d'),
            $jobOrder->created_at?->format('h:i A'),
        ];
    }

    private function exportFileName(string $exportType, array $filters): string
    {
        $parts = ['maintenance-job-orders'];

        if (filled($filters['status'])) {
            $parts[] = $filters['status'];
        }

        if ($filters['date_filter'] === 'day' && filled($filters['filter_date'])) {
            $parts[] = $filters['filter_date'];
        }

        if ($filters['date_filter'] === 'month' && filled($filters['filter_month'])) {
            $parts[] = $filters['filter_month'];
        }

        if ($filters['date_filter'] === 'year' && filled($filters['filter_year'])) {
            $parts[] = $filters['filter_year'];
        }

        $parts[] = now()->format('Ymd-His');

        return implode('-', $parts).'.'.$exportType;
    }

    public function exportSingle(Request $request, JobOrderMaintenance $jobOrderMaintenance): StreamedResponse|Response
    {
        $exportType = strtolower($request->string('export_type')->toString());

        if (! in_array($exportType, ['csv', 'xls'], true)) {
            $exportType = 'csv';
        }

        $jobOrderMaintenance->load([
            'bus',
            'creator',
            'histories.user',
            'statusPeriods.changedBy',
        ]);

        $fileName = 'maintenance-job-order-'.$jobOrderMaintenance->job_order_no.'-'.now()->format('Ymd-His').'.'.$exportType;

        return match ($exportType) {
            'xls' => $this->exportSingleExcel($jobOrderMaintenance, $fileName),
            default => $this->exportSingleCsv($jobOrderMaintenance, $fileName),
        };
    }

    private function exportSingleCsv(JobOrderMaintenance $jobOrderMaintenance, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($jobOrderMaintenance) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Maintenance Job Order Details']);
            fputcsv($handle, []);

            fputcsv($handle, ['Field', 'Value']);

            foreach ($this->singleExportDetails($jobOrderMaintenance) as $row) {
                fputcsv($handle, $row);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Update History']);
            fputcsv($handle, ['Date', 'Action', 'Old Value', 'New Value', 'Remarks', 'Updated By']);

            foreach ($jobOrderMaintenance->histories as $history) {
                fputcsv($handle, [
                    $history->created_at?->format('Y-m-d h:i A'),
                    $history->action,
                    $history->old_value,
                    $history->new_value,
                    $history->remarks,
                    $history->user->name ?? 'System',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportSingleExcel(JobOrderMaintenance $jobOrderMaintenance, string $fileName): Response
    {
        $html = '<table border="1">';
        $html .= '<tr><th colspan="2">Maintenance Job Order Details</th></tr>';
        $html .= '<tr><th>Field</th><th>Value</th></tr>';

        foreach ($this->singleExportDetails($jobOrderMaintenance) as $row) {
            $html .= '<tr>';
            $html .= '<td>'.e((string) $row[0]).'</td>';
            $html .= '<td>'.e((string) $row[1]).'</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $html .= '<br>';

        $html .= '<table border="1">';
        $html .= '<tr><th colspan="6">Update History</th></tr>';
        $html .= '<tr>';
        $html .= '<th>Date</th>';
        $html .= '<th>Action</th>';
        $html .= '<th>Old Value</th>';
        $html .= '<th>New Value</th>';
        $html .= '<th>Remarks</th>';
        $html .= '<th>Updated By</th>';
        $html .= '</tr>';

        foreach ($jobOrderMaintenance->histories as $history) {
            $html .= '<tr>';
            $html .= '<td>'.e((string) $history->created_at?->format('Y-m-d h:i A')).'</td>';
            $html .= '<td>'.e((string) $history->action).'</td>';
            $html .= '<td>'.e((string) $history->old_value).'</td>';
            $html .= '<td>'.e((string) $history->new_value).'</td>';
            $html .= '<td>'.e((string) $history->remarks).'</td>';
            $html .= '<td>'.e((string) ($history->user->name ?? 'System')).'</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function singleExportDetails(JobOrderMaintenance $jobOrder): array
    {
        return [
            ['Job Order No.', $jobOrder->job_order_no],
            ['Bus No.', $jobOrder->bus->bus_no ?? $jobOrder->bus_no_snapshot ?? 'N/A'],
            ['Plate No.', $jobOrder->bus->plate_no ?? $jobOrder->plate_no_snapshot ?? 'N/A'],
            ['Company', $jobOrder->bus->company ?? $jobOrder->company_snapshot ?? 'N/A'],
            ['Garage', $jobOrder->bus->garage ?? $jobOrder->garage_snapshot ?? 'N/A'],
            ['Requester', $jobOrder->full_name ?: 'Not specified'],
            ['Mechanic(s)', $jobOrder->mechanic_names_label],
            ['Repair Type(s)', $jobOrder->repair_types_label],
            ['Description of Work', $jobOrder->description_of_work],
            ['Current Odometer', $jobOrder->odometer_reading !== null ? $jobOrder->odometer_reading.' km' : 'Not encoded'],
            ['Previous Odometer', $jobOrder->last_odometer_reading !== null ? $jobOrder->last_odometer_reading.' km' : 'No previous record'],
            ['Odometer Difference', $jobOrder->odometer_difference !== null ? $jobOrder->odometer_difference.' km' : 'N/A'],
            ['Standby Downtime', $jobOrder->downtime_breakdown[JobOrderStatus::Standby->value]['label']],
            ['Waiting Parts Downtime', $jobOrder->downtime_breakdown[JobOrderStatus::WaitingParts->value]['label']],
            ['On Going Repair Downtime', $jobOrder->downtime_breakdown[JobOrderStatus::OnGoingRepair->value]['label']],
            ['Total Downtime', $jobOrder->total_downtime_label],
            ['Downtime Counter', $jobOrder->is_downtime_running ? 'Running' : 'Stopped'],
            ['Status', $jobOrder->status_label],
            ['Created By', $jobOrder->creator->name ?? 'System'],
            ['Created At', $jobOrder->created_at?->format('Y-m-d h:i A')],
            ['Last Updated', $jobOrder->updated_at?->format('Y-m-d h:i A')],
        ];
    }
}
