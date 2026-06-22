<?php

namespace App\Console\Commands;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportForSaleUnits extends Command
{
    protected $signature = 'for-sale:import
                            {file=storage/app/imports/forsale.csv}
                            {--replace : Delete existing For Sale records before importing}
                            {--no-sync : Do not sync imported records to the buses table}';

    protected $description = 'Import For Sale units monitoring records from CSV file';

    public function handle(): int
    {
        $filePath = base_path($this->argument('file'));

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            $this->error('Unable to open CSV file.');

            return self::FAILURE;
        }

        $firstLine = fgets($handle);

        if ($firstLine === false) {
            $this->error('CSV file is empty.');

            fclose($handle);

            return self::FAILURE;
        }

        /*
         | Auto-detect delimiter.
         | Supports comma CSV and tab-separated files copied from Excel/Google Sheets.
         */
        $delimiter = substr_count($firstLine, "\t") > substr_count($firstLine, ',')
            ? "\t"
            : ',';

        $headers = str_getcsv(trim($firstLine), $delimiter);
        $headers = array_map(fn ($header) => $this->normalizeHeader($header), $headers);

        $imported = 0;
        $skipped = 0;
        $syncedBuses = 0;
        $rowNumber = 1;

        try {
            DB::transaction(function () use (
                $handle,
                $headers,
                $delimiter,
                &$imported,
                &$skipped,
                &$syncedBuses,
                &$rowNumber
            ): void {
                if ($this->option('replace')) {
                    $this->resetExistingForSaleRecords();
                }

                while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $rowNumber++;

                    if ($this->isEmptyRow($data)) {
                        $skipped++;

                        continue;
                    }

                    $row = array_combine($headers, array_pad($data, count($headers), null));

                    if ($row === false) {
                        $this->warn("Skipped row {$rowNumber}: invalid column count.");
                        $skipped++;

                        continue;
                    }

                    $busNo = $this->cleanUpper($row['bus_number'] ?? null);

                    if ($busNo === null) {
                        $this->warn("Skipped row {$rowNumber}: missing Bus Number.");
                        $skipped++;

                        continue;
                    }

                    $record = BusForSaleRecord::create([
                        'sort_order' => $rowNumber - 1,
                        'bus_no' => $busNo,
                        'plate_no' => $this->cleanUpper($row['plate_number'] ?? null),
                        'company' => $this->cleanUpper($row['company'] ?? null),
                        'garage' => $this->cleanUpper($row['garage'] ?? null),
                        'status' => $this->normalizeStatus($row['status'] ?? null),
                        'storage_area' => $this->cleanValue($row['storage_area'] ?? null),
                        'breakdown_start_date' => $this->parseDate($row['breakdown_start_date'] ?? null),
                        'breakdown_end_date' => $this->parseDate($row['breakdown_end_date'] ?? null),
                        'column_11' => $this->cleanValue($row['column_11'] ?? null),
                        'days_in_breakdown' => $this->parseInteger($row['days_in_breakdown'] ?? null),
                        'unit_location' => $this->cleanValue($row['unit_location'] ?? null),
                        'progress' => $this->cleanValue($row['progress'] ?? null),
                        'remarks' => $this->cleanValue($row['remarks'] ?? null),
                    ]);

                    /*
                     | If Days in Breakdown is blank but start date exists,
                     | auto-compute days.
                     */
                    if ($record->days_in_breakdown === 0 && $record->breakdown_start_date) {
                        $record->updateQuietly([
                            'days_in_breakdown' => $this->computeDaysInBreakdown(
                                $record->breakdown_start_date,
                                $record->breakdown_end_date
                            ),
                        ]);
                    }

                    if (! $this->option('no-sync')) {
                        $this->syncToBusMaster($record);
                        $syncedBuses++;
                    }

                    $imported++;
                }
            });
        } catch (Throwable $exception) {
            fclose($handle);

            $this->error('For Sale import failed.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        fclose($handle);

        $this->info('For Sale import completed successfully.');
        $this->line("Imported: {$imported}");
        $this->line("Skipped: {$skipped}");
        $this->line("Synced to buses: {$syncedBuses}");

        return self::SUCCESS;
    }

    private function resetExistingForSaleRecords(): void
    {
        $oldBusIds = BusForSaleRecord::query()
            ->whereNotNull('bus_id')
            ->pluck('bus_id')
            ->unique()
            ->values();

        BusForSaleRecord::query()->delete();

        if ($oldBusIds->isEmpty()) {
            return;
        }

        Bus::query()
            ->whereIn('id', $oldBusIds)
            ->update([
                'sale_status' => Bus::SALE_NOT_FOR_SALE,
                'status_updated_at' => now(),
            ]);
    }

    private function syncToBusMaster(BusForSaleRecord $record): void
    {
        $bus = Bus::query()
            ->where('bus_no', $record->bus_no)
            ->first();

        if (! $bus) {
            $bus = new Bus([
                'bus_no' => $record->bus_no,
            ]);
        }

        $bus->plate_no = $record->plate_no ?: $bus->plate_no;
        $bus->company = $record->company ?: $bus->company;
        $bus->garage = $record->garage ?: $bus->garage;

        /*
         | If status is blank in CSV, do not overwrite existing bus condition.
         */
        if ($record->status) {
            $bus->operational_status = $record->status;
        } elseif (! $bus->exists || ! $bus->operational_status) {
            $bus->operational_status = Bus::STATUS_ACTIVE;
        }

        $bus->sale_status = Bus::SALE_FOR_SALE;
        $bus->monitoring_remarks = $record->remarks ?: $bus->monitoring_remarks;
        $bus->status_updated_at = now();

        $bus->save();

        $record->updateQuietly([
            'bus_id' => $bus->id,
        ]);
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace('.', '', $header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim((string) $header, '_');
    }

    private function normalizeStatus(?string $status): ?string
    {
        $status = strtolower(trim((string) $status));

        if ($status === '') {
            return null;
        }

        return match (true) {
            str_contains($status, 'running') => Bus::STATUS_ACTIVE,
            str_contains($status, 'active') => Bus::STATUS_ACTIVE,
            str_contains($status, 'mechanical') => Bus::STATUS_MECHANICAL_BREAKDOWN,
            str_contains($status, 'accident') => Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN,
            str_contains($status, 'hold') => Bus::STATUS_ON_HOLD_PLATE_REGISTRATION,
            default => null,
        };
    }

    private function parseDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $formats = [
            'd-M-y',
            'd-M-Y',
            'm/d/Y',
            'm/d/y',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function parseInteger(mixed $value): int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        return (int) preg_replace('/[^0-9]/', '', $value);
    }

    private function computeDaysInBreakdown(mixed $startDate, mixed $endDate): int
    {
        if (! $startDate) {
            return 0;
        }

        $start = Carbon::parse($startDate)->startOfDay();

        $end = $endDate
            ? Carbon::parse($endDate)->startOfDay()
            : now()->startOfDay();

        if ($end->lessThan($start)) {
            return 0;
        }

        return (int) $start->diffInDays($end);
    }

    private function cleanUpper(mixed $value): ?string
    {
        $value = $this->cleanValue($value);

        return $value === null ? null : strtoupper($value);
    }

    private function cleanValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
