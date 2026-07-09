<?php

namespace App\Http\Controllers\Maintenance;

use App\Enums\JobOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\StoreJobOrderMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateJobOrderMaintenanceNumberRequest;
use App\Http\Requests\Maintenance\UpdateJobOrderMaintenanceStatusRequest;
use App\Models\Bus;
use App\Models\JobOrderMaintenance;
use App\Services\Maintenance\JobOrderMaintenanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobOrderMaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'bus_id' => $request->integer('bus_id') ?: null,
            'date_filter' => $request->string('date_filter')->toString(),
            'filter_date' => $request->string('filter_date')->toString(),
            'filter_month' => $request->string('filter_month')->toString(),
            'filter_year' => $request->string('filter_year')->toString(),
        ];

        $applyDateFilter = function ($query) use ($filters) {
            if ($filters['date_filter'] === 'day' && filled($filters['filter_date'])) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['filter_date'])) {
                    $query->whereDate('created_at', $filters['filter_date']);
                }
            }

            if ($filters['date_filter'] === 'month' && filled($filters['filter_month'])) {
                if (preg_match('/^\d{4}-\d{2}$/', $filters['filter_month'])) {
                    [$year, $month] = explode('-', $filters['filter_month']);

                    $query->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
                }
            }

            if ($filters['date_filter'] === 'year' && filled($filters['filter_year'])) {
                if (preg_match('/^\d{4}$/', $filters['filter_year'])) {
                    $query->whereYear('created_at', $filters['filter_year']);
                }
            }
        };

        $jobOrders = JobOrderMaintenance::query()
            ->with(['bus', 'creator'])
            ->search($filters['search'])
            ->when(filled($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(filled($filters['bus_id']), function ($query) use ($filters) {
                $query->where('bus_id', $filters['bus_id']);
            })
            ->tap($applyDateFilter)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $buses = Bus::query()
            ->select('id', 'bus_no', 'plate_no', 'company', 'garage')
            ->orderBy('bus_no')
            ->get();

        $groupedStatusCounts = JobOrderMaintenance::query()
            ->search($filters['search'])
            ->when(filled($filters['bus_id']), function ($query) use ($filters) {
                $query->where('bus_id', $filters['bus_id']);
            })
            ->tap($applyDateFilter)
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

        return view('maintenance.job-orders.index', [
            'jobOrders' => $jobOrders,
            'buses' => $buses,
            'statuses' => JobOrderStatus::options(),
            'statusCards' => $statusCards,
            'filters' => $filters,
        ]);
    }

    public function create(): View
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

        return view('maintenance.job-orders.create', [
            'buses' => $buses,
        ]);
    }

    public function store(
        StoreJobOrderMaintenanceRequest $request,
        JobOrderMaintenanceService $jobOrderMaintenanceService
    ): RedirectResponse {
        try {
            $jobOrderMaintenance = $jobOrderMaintenanceService->create(
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

    public function show(JobOrderMaintenance $jobOrderMaintenance): View
    {
        $jobOrderMaintenance->load([
            'bus',
            'creator',
            'histories.user',
        ]);

        return view('maintenance.job-orders.show', [
            'jobOrder' => $jobOrderMaintenance,
        ]);
    }

    public function editStatus(JobOrderMaintenance $jobOrderMaintenance): View
    {
        $jobOrderMaintenance->load(['bus', 'creator']);

        return view('maintenance.job-orders.edit-status', [
            'jobOrder' => $jobOrderMaintenance,
            'statuses' => JobOrderStatus::cases(),
        ]);
    }

    public function updateStatus(
        UpdateJobOrderMaintenanceStatusRequest $request,
        JobOrderMaintenance $jobOrderMaintenance,
        JobOrderMaintenanceService $jobOrderMaintenanceService
    ): RedirectResponse {
        try {
            $validated = $request->validated();

            $jobOrderMaintenanceService->updateStatus(
                jobOrderMaintenance: $jobOrderMaintenance,
                status: JobOrderStatus::from($validated['status']),
                userId: $request->user()?->id,
                remarks: $validated['remarks'] ?? null
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

    public function editNumber(JobOrderMaintenance $jobOrderMaintenance): View
    {
        $jobOrderMaintenance->load(['bus', 'creator']);

        return view('maintenance.job-orders.edit-number', [
            'jobOrder' => $jobOrderMaintenance,
        ]);
    }

    public function updateNumber(
        UpdateJobOrderMaintenanceNumberRequest $request,
        JobOrderMaintenance $jobOrderMaintenance,
        JobOrderMaintenanceService $jobOrderMaintenanceService
    ): RedirectResponse {
        try {
            $validated = $request->validated();

            $jobOrderMaintenanceService->updateJobOrderNumber(
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
}
