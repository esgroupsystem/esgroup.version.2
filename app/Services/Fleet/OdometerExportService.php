<?php

declare(strict_types=1);

namespace App\Services\Fleet;

use App\Models\DieselStock;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Odometer Monitoring download (CSV with BOM, or an HTML table served as .xls): the summary,
 * the diesel movements and every reading of the period.
 */
final class OdometerExportService
{
    private const MOVEMENT_HEADINGS = ['Date', 'Movement', 'Reference No.', 'Bus / Unit', 'Liters', 'Unit Cost', 'Total Cost', 'Remarks', 'Encoded By'];

    private const RECORD_HEADINGS = ['Date', 'Bus', 'Plate No.', 'Garage', 'Time', 'Driver', 'Previous Odometer', 'New Odometer', 'KM Run', 'Diesel Used', 'KM/L', 'Remaining Change Oil KM'];

    public static function type(?string $type): string
    {
        $type = strtolower((string) $type);

        return in_array($type, ['csv', 'xls'], true) ? $type : 'csv';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $records
     * @param  Collection<int, DieselStock>  $movements
     * @param  array<string, mixed>  $summary  OdometerService::summary() + period_label, selected_bus, last_change_oil_km
     */
    public function download(string $type, OdometerPeriod $period, Collection $records, Collection $movements, array $summary): StreamedResponse|Response
    {
        $summaryRows = $this->summaryRows($summary);
        $movementRows = $movements->map(fn (DieselStock $stock): array => $this->movementRow($stock));
        $recordRows = $records->map(fn (array $row): array => $this->recordRow($row));
        $fileName = $this->fileName($type, $period);

        if ($type === 'xls') {
            $html = '<table border="1"><tr><th colspan="2">Diesel Stock and Odometer Monitoring Report</th></tr><tr><th>Field</th><th>Value</th></tr>'
                .$this->rows($summaryRows).'</table><br>'
                .'<table border="1"><tr><th colspan="9">Diesel Stock Movement Details</th></tr><tr>'.$this->cells(self::MOVEMENT_HEADINGS, 'th').'</tr>'
                .$this->rows($movementRows).'</table><br>'
                .'<table border="1"><tr><th colspan="12">Odometer Encoding Details</th></tr><tr>'.$this->cells(self::RECORD_HEADINGS, 'th').'</tr>'
                .$this->rows($recordRows).'</table>';

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        return response()->streamDownload(function () use ($summaryRows, $movementRows, $recordRows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Diesel Stock and Odometer Monitoring Report']);
            fputcsv($handle, []);
            fputcsv($handle, ['Report Summary']);
            fputcsv($handle, ['Field', 'Value']);
            foreach ($summaryRows as $row) {
                fputcsv($handle, $row);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Diesel Stock Movement Details']);
            fputcsv($handle, self::MOVEMENT_HEADINGS);
            foreach ($movementRows as $row) {
                fputcsv($handle, $row);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['Odometer Encoding Details']);
            fputcsv($handle, self::RECORD_HEADINGS);
            foreach ($recordRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return list<array{string, string}>
     */
    private function summaryRows(array $summary): array
    {
        return [
            ['Period', $summary['period_label']],
            ['Bus Unit', $summary['selected_bus']],
            ['Current Diesel Stock', number_format((float) $summary['current_stock'], 2).' L'],
            ['Diesel IN', number_format((float) $summary['period_in'], 2).' L'],
            ['Diesel OUT / Used', number_format((float) $summary['period_out'], 2).' L'],
            ['Adjustment', number_format((float) $summary['period_adjustment'], 2).' L'],
            ['Total KM Run', number_format((float) $summary['total_km'], 0)],
            ['Total Diesel Used', number_format((float) $summary['total_liters'], 2).' L'],
            ['Average KM/L', number_format((float) $summary['average_km_per_liter'], 2)],
            ['Last Change Oil KM', $summary['last_change_oil_km'] ? number_format((int) $summary['last_change_oil_km']) : 'Not encoded'],
        ];
    }

    /** @return list<string> */
    private function movementRow(DieselStock $stock): array
    {
        return [
            $stock->date ? Carbon::parse($stock->date)->format('Y-m-d') : '',
            match ($stock->type) {
                'in' => 'Diesel IN',
                'out' => 'Diesel OUT',
                'adjustment' => 'Adjustment',
                default => strtoupper((string) $stock->type),
            },
            $stock->reference_no ?? '',
            $stock->bus ? trim(($stock->bus->body_number ?? '').' - '.($stock->bus->name ?? '').' - '.($stock->bus->garage ?? '')) : 'Stock Only',
            number_format((float) $stock->liters, 2, '.', ''),
            number_format((float) $stock->unit_cost, 2, '.', ''),
            number_format((float) $stock->total_cost, 2, '.', ''),
            $stock->remarks ?? '',
            // A movement without an encoder (e.g. from the mobile app) used to crash the export.
            $stock->encoder?->full_name ?: 'System',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<mixed>
     */
    private function recordRow(array $row): array
    {
        return [
            $row['date'] ? Carbon::parse($row['date'])->format('Y-m-d') : '',
            trim(($row['body_number'] ?? 'N/A').' - '.($row['bus_name'] ?? 'No Bus Name')),
            $row['plate_number'] ?? '',
            $row['garage'] ?? '',
            $row['time'] ? Carbon::parse($row['time'])->format('g:i A') : '',
            strtoupper($row['driver_name'] ?? 'N/A'),
            $row['previous_odometer'] !== null ? (int) $row['previous_odometer'] : '',
            (int) $row['new_odometer'],
            (float) $row['total_km_run'],
            number_format((float) $row['diesel_consumption'], 2, '.', ''),
            number_format((float) $row['km_per_liter'], 2, '.', ''),
            (float) $row['remaining_change_oil'],
        ];
    }

    private function fileName(string $type, OdometerPeriod $period): string
    {
        $label = trim((string) preg_replace('/[^a-z0-9\-]+/i', '-', strtolower(trim($period->label))), '-');

        return 'odometer-report-'.$period->type.'-'.$label.'-'.now()->format('Ymd-His').'.'.$type;
    }

    /** @param iterable<iterable<mixed>> $rows */
    private function rows(iterable $rows): string
    {
        $html = '';
        foreach ($rows as $row) {
            $html .= '<tr>'.$this->cells($row).'</tr>';
        }

        return $html;
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
}
