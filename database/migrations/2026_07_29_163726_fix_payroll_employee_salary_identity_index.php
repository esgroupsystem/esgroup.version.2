<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'payroll_employee_salaries';

    private const INVALID_UNIQUE_INDEX = 'pes_bio_emp_unique';

    private const LEGACY_LOOKUP_INDEX = 'pes_bio_emp_index';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        /*
         * biometric_employee_id belongs to an external CrossChex account.
         * Different accounts can legitimately contain the same employee ID.
         */
        if ($this->indexExists(self::INVALID_UNIQUE_INDEX)) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP INDEX `%s`',
                self::TABLE,
                self::INVALID_UNIQUE_INDEX
            ));
        }

        /*
         * Keep a normal index for searching, but do not make it unique.
         */
        if (! $this->indexExists(self::LEGACY_LOOKUP_INDEX)) {
            DB::statement(sprintf(
                'CREATE INDEX `%s` ON `%s` (`biometric_employee_id`)',
                self::LEGACY_LOOKUP_INDEX,
                self::TABLE
            ));
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        if ($this->indexExists(self::LEGACY_LOOKUP_INDEX)) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP INDEX `%s`',
                self::TABLE,
                self::LEGACY_LOOKUP_INDEX
            ));
        }

        /*
         * Do not restore pes_bio_emp_unique because that would restore
         * the original multi-account CrossChex conflict.
         */
    }

    private function indexExists(string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', self::TABLE)
            ->where('index_name', $indexName)
            ->exists();
    }
};
