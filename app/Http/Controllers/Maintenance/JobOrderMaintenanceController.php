<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Enums\JobOrderRepairType;
use App\Enums\JobOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreJobOrderMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateJobOrderMaintenanceNumberRequest;
use App\Http\Requests\Maintenance\UpdateJobOrderMaintenanceStatusRequest;
use App\Http\Resources\Maintenance\JobOrderMaintenanceDetailResource;
use App\Http\Resources\Maintenance\JobOrderMaintenanceRowResource;
use App\Models\Bus;
use App\Models\JobOrderMaintenance;
use App\Services\Maintenance\JobOrderMaintenanceExportService;
use App\Services\Maintenance\JobOrderMaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/** Maintenance → Maintenance Job Orders. */
final class JobOrderMaintenanceController extends Controller
{
    public function __construct(
        private readonly JobOrderMaintenanceService $jobOrderMaintenanceService,
        private readonly JobOrderMaintenanceExportService $exportService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $filters = JobOrderMaintenanceService::filters($request);
        $user = $request->user();

        return Inertia::render('maintenance/job-orders/index', [
            'jobOrders' => $this->jobOrderMaintenanceService->paginate($filters)
                ->through(fn (JobOrderMaintenance $jobOrder): array => JobOrderMaintenanceRowResource::make($jobOrder)->resolve($request)),
            'buses' => $this->jobOrderMaintenanceService->busOptions()
                ->map(fn (Bus $bus): array => ['value' => (string) $bus->id, 'label' => $bus->bus_no.' — '.($bus->plate_no ?? 'No Plate')])
                ->values(),
            'statusCards' => $this->jobOrderMaintenanceService->statusCards($filters),
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

    public function export(Request $request): StreamedResponse|Response
    {
        return $this->exportService->exportList(
            JobOrderMaintenanceService::filters($request),
            JobOrderMaintenanceExportService::type($request->string('export_type')->toString())
        );
    }

    public function exportSingle(Request $request, JobOrderMaintenance $jobOrderMaintenance): StreamedResponse|Response
    {
        return $this->exportService->exportSingle(
            $this->jobOrderMaintenanceService->load($jobOrderMaintenance),
            JobOrderMaintenanceExportService::type($request->string('export_type')->toString())
        );
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('maintenance/job-orders/create', [
            'buses' => $this->jobOrderMaintenanceService->busOptionsWithLastOdometer()->map(fn (Bus $bus): array => [
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

    public function store(StoreJobOrderMaintenanceRequest $request): RedirectResponse
    {
        try {
            $jobOrder = $this->jobOrderMaintenanceService->create($request->validated(), $request->user()?->id);

            return redirect()->route('maintenance.job-orders.show', $jobOrder)->with('success', 'Maintenance job order created successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logFailure('Maintenance job order creation failed', $e, $request, ['payload' => $request->safe()->except(['_token'])]);

            return back()->withInput()->with('error', 'Failed to create maintenance job order. Please check the details and try again.');
        }
    }

    public function show(Request $request, JobOrderMaintenance $jobOrderMaintenance): InertiaResponse
    {
        $jobOrder = $this->jobOrderMaintenanceService->load($jobOrderMaintenance);
        $user = $request->user();

        return Inertia::render('maintenance/job-orders/show', [
            'jobOrder' => JobOrderMaintenanceDetailResource::make($jobOrder)->resolve($request),
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
        $jobOrder = $this->jobOrderMaintenanceService->load($jobOrderMaintenance, ['bus', 'creator', 'statusPeriods']);

        return Inertia::render('maintenance/job-orders/edit-status', [
            'jobOrder' => [
                ...JobOrderMaintenanceRowResource::summary($jobOrder),
                'work' => Str::limit((string) $jobOrder->description_of_work, 220),
            ],
            'values' => [
                'status' => (string) ($jobOrder->status?->value ?? ''),
                'mechanic_names' => $jobOrder->mechanic_names_list !== [] ? array_values($jobOrder->mechanic_names_list) : [''],
                'repair_types' => array_values(array_map('strval', $jobOrder->repair_types ?? [])),
                'remarks' => '',
            ],
            'statuses' => self::statusOptions(),
            'repairTypes' => self::repairTypeOptions(),
            'urls' => [
                'show' => route('maintenance.job-orders.show', $jobOrder),
                'update' => route('maintenance.job-orders.update-status', $jobOrder),
            ],
        ]);
    }

    public function updateStatus(UpdateJobOrderMaintenanceStatusRequest $request, JobOrderMaintenance $jobOrderMaintenance): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->jobOrderMaintenanceService->updateStatus(
                jobOrderMaintenance: $jobOrderMaintenance,
                status: JobOrderStatus::from($validated['status']),
                userId: $request->user()?->id,
                remarks: $validated['remarks'] ?? null,
                mechanicNames: $validated['mechanic_names'] ?? [],
                repairTypes: $validated['repair_types'] ?? []
            );

            return redirect()->route('maintenance.job-orders.show', $jobOrderMaintenance)->with('success', 'Maintenance job order status updated successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logFailure('Maintenance job order status update failed', $e, $request, ['job_order_maintenance_id' => $jobOrderMaintenance->id]);

            return back()->withInput()->with('error', 'Failed to update maintenance status. Please try again.');
        }
    }

    public function editNumber(JobOrderMaintenance $jobOrderMaintenance): InertiaResponse
    {
        $jobOrder = $this->jobOrderMaintenanceService->load($jobOrderMaintenance, ['bus', 'creator']);

        return Inertia::render('maintenance/job-orders/edit-number', [
            'jobOrder' => JobOrderMaintenanceRowResource::summary($jobOrder),
            'urls' => [
                'show' => route('maintenance.job-orders.show', $jobOrder),
                'update' => route('maintenance.job-orders.update-number', $jobOrder),
            ],
        ]);
    }

    public function updateNumber(UpdateJobOrderMaintenanceNumberRequest $request, JobOrderMaintenance $jobOrderMaintenance): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->jobOrderMaintenanceService->updateJobOrderNumber(
                jobOrderMaintenance: $jobOrderMaintenance,
                jobOrderNo: $validated['job_order_no'],
                userId: $request->user()?->id,
                remarks: $validated['remarks'] ?? null
            );

            return redirect()->route('maintenance.job-orders.show', $jobOrderMaintenance)->with('success', 'Job order number updated successfully.');
        } catch (Throwable $e) {
            $this->logFailure('Maintenance job order number update failed', $e, $request, ['job_order_maintenance_id' => $jobOrderMaintenance->id]);

            return back()->withInput()->with('error', 'Failed to update job order number. Please try again.');
        }
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

    /** @param array<string, mixed> $context */
    private function logFailure(string $message, Throwable $e, Request $request, array $context): void
    {
        Log::error($message, [
            ...$context,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => $request->user()?->id,
        ]);
    }
}
