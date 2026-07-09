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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class JobOrderMaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        $jobOrders = $this->applyIndexFilters(
            query: JobOrderMaintenance::query()->with(['bus', 'creator']),
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

        $groupedStatusCounts = $this->applyIndexFilters(
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

        return view('maintenance.job-orders.index', [
            'jobOrders' => $jobOrders,
            'buses' => $buses,
            'statuses' => JobOrderStatus::options(),
            'statusCards' => $statusCards,
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $filters = $this->resolveFilters($request);

        $exportType = strtolower($request->string('export_type')->toString());

        if (! in_array($exportType, ['csv', 'xls'], true)) {
            $exportType = 'csv';
        }

        $query = $this->applyIndexFilters(
            query: JobOrderMaintenance::query()->with(['bus', 'creator']),
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

    private function resolveFilters(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'bus_id' => $request->integer('bus_id') ?: null,
            'date_filter' => $request->string('date_filter')->toString(),
            'filter_date' => $request->string('filter_date')->toString(),
            'filter_month' => $request->string('filter_month')->toString(),
            'filter_year' => $request->string('filter_year')->toString(),
        ];
    }

    private function applyIndexFilters(Builder $query, array $filters, bool $includeStatus = true): Builder
    {
        return $query
            ->search($filters['search'])
            ->when($includeStatus && filled($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(filled($filters['bus_id']), function (Builder $query) use ($filters) {
                $query->where('bus_id', $filters['bus_id']);
            })
            ->tap(function (Builder $query) use ($filters) {
                $this->applyDateFilter($query, $filters);
            });
    }

    private function applyDateFilter(Builder $query, array $filters): void
    {
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
    }

    private function exportCsv(Builder $query, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $this->exportHeadings());

            $query->chunk(500, function ($jobOrders) use ($handle) {
                foreach ($jobOrders as $jobOrder) {
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

        $query->chunk(500, function ($jobOrders) use (&$html) {
            foreach ($jobOrders as $jobOrder) {
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
            'Description of Work',
            'Odometer Reading',
            'Last Odometer Reading',
            'Odometer Difference',
            'Odometer Warning',
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
            $jobOrder->bus?->bus_no ?? $jobOrder->bus_no_snapshot ?? 'N/A',
            $jobOrder->bus?->plate_no ?? $jobOrder->plate_no_snapshot ?? 'N/A',
            $jobOrder->bus?->company ?? $jobOrder->company_snapshot ?? 'N/A',
            $jobOrder->bus?->garage ?? $jobOrder->garage_snapshot ?? 'N/A',
            $jobOrder->full_name ?: 'Not specified',
            $jobOrder->description_of_work,
            $jobOrder->odometer_reading !== null ? $jobOrder->odometer_reading : '',
            $jobOrder->last_odometer_reading !== null ? $jobOrder->last_odometer_reading : '',
            $jobOrder->odometer_difference !== null ? $jobOrder->odometer_difference : '',
            $jobOrder->is_odometer_lower_than_last ? 'Current reading is lower than last reading' : '',
            $jobOrder->status_label,
            $jobOrder->creator?->name ?? 'System',
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
                    $history->user?->name ?? 'System',
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
            $html .= '<td>'.e((string) ($history->user?->name ?? 'System')).'</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
