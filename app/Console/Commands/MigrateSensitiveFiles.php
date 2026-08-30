<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ConductorLeave;
use App\Models\DriverLeave;
use App\Models\EmployeeAsset;
use App\Models\EmployeeLeave;
use App\Models\JobOrderFile;
use App\Models\Receiving;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class MigrateSensitiveFiles extends Command
{
    protected $signature = 'security:migrate-sensitive-files {--dry-run : Report files without moving them}';

    protected $description = 'Move legacy sensitive files from public storage to private local storage.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $moved = 0;
        $missing = 0;
        $failed = 0;

        $targets = [
            [EmployeeAsset::query(), ['profile_picture']],
            [EmployeeLeave::query(), ['first_notice_proof', 'second_notice_proof', 'final_notice_proof']],
            [DriverLeave::query(), ['first_notice_proof', 'second_notice_proof', 'final_notice_proof']],
            [ConductorLeave::query(), ['first_notice_proof', 'second_notice_proof', 'final_notice_proof']],
            [Receiving::query(), ['proof_image']],
            [JobOrderFile::query(), ['file_path']],
        ];

        foreach ($targets as [$query, $fields]) {
            $query->chunkById(100, function ($models) use ($fields, $dryRun, &$moved, &$missing, &$failed): void {
                foreach ($models as $model) {
                    foreach ($fields as $field) {
                        $path = $model->{$field};
                        if (! is_string($path) || $path === '') {
                            continue;
                        }
                        if (! Storage::disk('public')->exists($path)) {
                            continue;
                        }
                        if ($dryRun) {
                            $this->line("WOULD MOVE: {$path}");

                            continue;
                        }
                        $stream = Storage::disk('public')->readStream($path);
                        if (! is_resource($stream)) {
                            $missing++;

                            continue;
                        }
                        $written = false;
                        try {
                            $written = Storage::disk('local')->put($path, $stream);
                        } finally {
                            fclose($stream);
                        }

                        if (! $written) {
                            $failed++;
                            $this->error("FAILED TO WRITE: {$path}");

                            continue;
                        }

                        if (! Storage::disk('local')->exists($path)) {
                            $failed++;
                            $this->error("FAILED TO VERIFY: {$path}");

                            continue;
                        }

                        Storage::disk('public')->delete($path);
                        $model->{$field} = $path;
                        $model->saveQuietly();
                        $moved++;
                    }
                }
            });
        }

        $this->info($dryRun ? "Dry run complete. Existing legacy files: {$moved}. Missing: {$missing}." : "Migration complete. Moved: {$moved}; missing: {$missing}; failures: {$failed}.");

        return self::SUCCESS;
    }
}
