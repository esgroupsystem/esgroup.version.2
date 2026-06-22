<?php

namespace App\Console\Commands;

use App\Models\Bus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportBuses extends Command
{
    protected $signature = 'buses:import {file=storage/app/imports/buses.csv}';

    protected $description = 'Import bus master list from CSV file';

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
         | This supports both comma CSV and tab-separated Excel copy-paste files.
         */
        $delimiter = substr_count($firstLine, "\t") > substr_count($firstLine, ',')
            ? "\t"
            : ',';

        $headers = str_getcsv(trim($firstLine), $delimiter);
        $headers = array_map(fn ($header) => $this->normalizeHeader($header), $headers);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rowNumber = 1;

        try {
            DB::transaction(function () use ($handle, $headers, $delimiter, &$created, &$updated, &$skipped, &$rowNumber): void {
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

                    $busNo = $this->cleanValue($row['bus_no'] ?? null);
                    $plateNo = $this->cleanValue($row['plate_no'] ?? null);

                    if ($busNo === null || $busNo === '') {
                        $this->warn("Skipped row {$rowNumber}: missing BUS NO.");
                        $skipped++;
                        continue;
                    }

                    $bus = Bus::updateOrCreate(
                        [
                            'bus_no' => $busNo,
                            'plate_no' => $plateNo,
                        ],
                        [
                            'company' => $this->cleanValue($row['company'] ?? null),
                            'garage' => $this->cleanValue($row['garage'] ?? null),
                            'chassis_number' => $this->cleanValue($row['chassis_number'] ?? null),
                            'engine_number' => $this->cleanValue($row['engine_number'] ?? null),
                            'case_number' => $this->cleanValue($row['case_number'] ?? null),
                        ]
                    );

                    if ($bus->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            });
        } catch (Throwable $exception) {
            fclose($handle);

            $this->error('Import failed.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        fclose($handle);

        $this->info('Bus import completed successfully.');
        $this->line("Created: {$created}");
        $this->line("Updated: {$updated}");
        $this->line("Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace('.', '', $header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim((string) $header, '_');
    }

    private function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : strtoupper($value);
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
