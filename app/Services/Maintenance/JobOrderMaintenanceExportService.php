<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\JobOrderStatus;
use App\Models\JobOrderMaintenance;
use App\Models\JobOrderMaintenanceHistory;
use App\Repositories\Contracts\Maintenance\JobOrderMaintenanceRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV and Excel downloads of Maintenance Job Orders: the filtered list, or one job order with its
 * update history. "Excel" is an HTML table served as application/vnd.ms-excel (opens in Excel).
 */
final class JobOrderMaintenanceExportService
{
    public const TYPES = ['csv', 'xls'];

    private const LIST_HEADINGS = [
        'Job Order No.', 'Bus No.', 'Plate No.', 'Company', 'Garage', 'Requester', 'Mechanic(s)', 'Repair Type(s)',
        'Description of Work', 'Odometer Reading', 'Last Odometer Reading', 'Odometer Difference', 'Odometer Warning',
        'Standby Downtime', 'Waiting Parts Downtime', 'On Going Repair Downtime', 'Total Downtime', 'Downtime Counter',
        'Status', 'Created By', 'Created Date', 'Created Time',
    ];

    private const HISTORY_HEADINGS = ['Date', 'Action', 'Old Value', 'New Value', 'Remarks', 'Updated By'];

    public function __construct(private readonly JobOrderMaintenanceRepositoryInterface $jobOrders) {}

    public static function type(?string $type): string
    {
        $type = strtolower((string) $type);

        return in_array($type, self::TYPES, true) ? $type : 'csv';
    }

    /** @param array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string} $filters */
    public function exportList(array $filters, string $type): StreamedResponse|Response
    {
        $fileName = $this->listFileName($type, $filters);

        if ($type === 'xls') {
            $html = '<table border="1"><thead><tr>'.$this->cells(self::LIST_HEADINGS, 'th').'</tr></thead><tbody>';
            $this->jobOrders->eachForExport($filters, function (Collection $jobOrders) use (&$html): void {
                foreach ($jobOrders as $jobOrder) {
                    $html .= '<tr>'.$this->cells($this->listRow($jobOrder)).'</tr>';
                }
            });

            return $this->excel($html.'</tbody></table>', $fileName);
        }

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::LIST_HEADINGS);
            $this->jobOrders->eachForExport($filters, function (Collection $jobOrders) use ($handle): void {
                foreach ($jobOrders as $jobOrder) {
                    fputcsv($handle, $this->listRow($jobOrder));
                }
            });
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** One job order: its details, then its update history. Relations: JobOrderMaintenanceService::SHOW_RELATIONS. */
    public function exportSingle(JobOrderMaintenance $jobOrder, string $type): StreamedResponse|Response
    {
        $fileName = 'maintenance-job-order-'.$jobOrder->job_order_no.'-'.now()->format('Ymd-His').'.'.$type;
        $details = $this->details($jobOrder);
        $history = $jobOrder->histories->map(fn (JobOrderMaintenanceHistory $row): array => [
            $row->created_at?->format('Y-m-d h:i A'),
            $row->action,
            $row->old_value,
            $row->new_value,
            $row->remarks,
            $row->user->name ?? 'System',
        ]);

        if ($type === 'xls') {
            $html = '<table border="1"><tr><th colspan="2">Maintenance Job Order Details</th></tr><tr><th>Field</th><th>Value</th></tr>';
            foreach ($details as $row) {
                $html .= '<tr>'.$this->cells($row).'</tr>';
            }
            $html .= '</table><br><table border="1"><tr><th colspan="6">Update History</th></tr><tr>'.$this->cells(self::HISTORY_HEADINGS, 'th').'</tr>';
            foreach ($history as $row) {
                $html .= '<tr>'.$this->cells($row).'</tr>';
            }

            return $this->excel($html.'</table>', $fileName);
        }

        return response()->streamDownload(function () use ($details, $history): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Maintenance Job Order Details']);
            fputcsv($handle, []);
            fputcsv($handle, ['Field', 'Value']);
            foreach ($details as $row) {
                fputcsv($handle, $row);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Update History']);
            fputcsv($handle, self::HISTORY_HEADINGS);
            foreach ($history as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return list<mixed> */
    private function listRow(JobOrderMaintenance $jobOrder): array
    {
        $breakdown = $jobOrder->downtime_breakdown;

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
            $jobOrder->odometer_reading ?? '',
            $jobOrder->last_odometer_reading ?? '',
            $jobOrder->odometer_difference ?? '',
            $jobOrder->is_odometer_lower_than_last ? 'Current reading is lower than last reading' : '',
            $breakdown[JobOrderStatus::Standby->value]['label'],
            $breakdown[JobOrderStatus::WaitingParts->value]['label'],
            $breakdown[JobOrderStatus::OnGoingRepair->value]['label'],
            $jobOrder->total_downtime_label,
            $jobOrder->is_downtime_running ? 'Running' : 'Stopped',
            $jobOrder->status_label,
            $jobOrder->creator->name ?? 'System',
            $jobOrder->created_at?->format('Y-m-d'),
            $jobOrder->created_at?->format('h:i A'),
        ];
    }

    /** @return list<array{string, mixed}> */
    private function details(JobOrderMaintenance $jobOrder): array
    {
        $breakdown = $jobOrder->downtime_breakdown;

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
            ['Standby Downtime', $breakdown[JobOrderStatus::Standby->value]['label']],
            ['Waiting Parts Downtime', $breakdown[JobOrderStatus::WaitingParts->value]['label']],
            ['On Going Repair Downtime', $breakdown[JobOrderStatus::OnGoingRepair->value]['label']],
            ['Total Downtime', $jobOrder->total_downtime_label],
            ['Downtime Counter', $jobOrder->is_downtime_running ? 'Running' : 'Stopped'],
            ['Status', $jobOrder->status_label],
            ['Created By', $jobOrder->creator->name ?? 'System'],
            ['Created At', $jobOrder->created_at?->format('Y-m-d h:i A')],
            ['Last Updated', $jobOrder->updated_at?->format('Y-m-d h:i A')],
        ];
    }

    /** @param array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string} $filters */
    private function listFileName(string $type, array $filters): string
    {
        $parts = ['maintenance-job-orders'];

        if (filled($filters['status'])) {
            $parts[] = $filters['status'];
        }
        $period = match ($filters['date_filter']) {
            'day' => $filters['filter_date'],
            'month' => $filters['filter_month'],
            'year' => $filters['filter_year'],
            default => '',
        };
        if (filled($period)) {
            $parts[] = $period;
        }
        $parts[] = now()->format('Ymd-His');

        return implode('-', $parts).'.'.$type;
    }

    /** @param iterable<mixed> $values */
    private function cells(iterable $values, string $tag = 'td'): string
    {
        $html = '';
        foreach ($values as $value) {
            $html .= "<{$tag}>".e((string) $value)."</{$tag}>";
        }

        return $html;
    }

    private function excel(string $html, string $fileName): Response
    {
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
