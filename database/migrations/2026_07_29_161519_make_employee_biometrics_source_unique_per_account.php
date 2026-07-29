<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE_NAME = 'employee_biometrics';

    private const OLD_UNIQUE_INDEX = 'employee_biometrics_source_key_unique';

    private const NEW_UNIQUE_INDEX = 'employee_biometrics_account_source_key_unique';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        if ($this->indexExists(self::OLD_UNIQUE_INDEX)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
                $table->dropUnique(self::OLD_UNIQUE_INDEX);
            });
        }

        if (! $this->indexExists(self::NEW_UNIQUE_INDEX)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
                $table->unique(
                    [
                        'source_crosschex_account',
                        'source_key',
                    ],
                    self::NEW_UNIQUE_INDEX
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        /*
         * After multiple CrossChex accounts are synchronized, the same
         * source_key can legally exist under different accounts.
         *
         * Prevent rollback from recreating an invalid global unique index.
         */
        $hasCrossAccountDuplicates = DB::table(self::TABLE_NAME)
            ->whereNotNull('source_key')
            ->where('source_key', '!=', '')
            ->select('source_key')
            ->groupBy('source_key')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasCrossAccountDuplicates) {
            throw new RuntimeException(
                'Cannot restore the global source_key unique index because source keys are already shared across multiple CrossChex accounts.'
            );
        }

        if ($this->indexExists(self::NEW_UNIQUE_INDEX)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
                $table->dropUnique(self::NEW_UNIQUE_INDEX);
            });
        }

        if (! $this->indexExists(self::OLD_UNIQUE_INDEX)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
                $table->unique('source_key', self::OLD_UNIQUE_INDEX);
            });
        }
    }

    private function indexExists(string $indexName): bool
    {
        return collect(Schema::getIndexes(self::TABLE_NAME))
            ->contains(function (array $index) use ($indexName): bool {
                return ($index['name'] ?? null) === $indexName;
            });
    }
};
