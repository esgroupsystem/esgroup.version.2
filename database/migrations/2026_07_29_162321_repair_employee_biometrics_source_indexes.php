<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'employee_biometrics';

    private const OLD_INDEX = 'employee_biometrics_source_key_unique';

    private const NEW_INDEX = 'employee_biometrics_account_source_key_unique';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        $indexes = $this->indexes();

        if ($indexes->contains(self::OLD_INDEX)) {
            DB::statement(
                sprintf(
                    'ALTER TABLE `%s` DROP INDEX `%s`',
                    self::TABLE,
                    self::OLD_INDEX
                )
            );
        }

        $indexes = $this->indexes();

        if (! $indexes->contains(self::NEW_INDEX)) {
            DB::statement(
                sprintf(
                    'ALTER TABLE `%s`
                    ADD UNIQUE INDEX `%s`
                    (`source_crosschex_account`, `source_key`)',
                    self::TABLE,
                    self::NEW_INDEX
                )
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        $indexes = $this->indexes();

        if ($indexes->contains(self::NEW_INDEX)) {
            DB::statement(
                sprintf(
                    'ALTER TABLE `%s` DROP INDEX `%s`',
                    self::TABLE,
                    self::NEW_INDEX
                )
            );
        }

        /*
         * Do not restore the old global unique index.
         * Different CrossChex accounts can have the same employee ID.
         */
    }

    private function indexes(): \Illuminate\Support\Collection
    {
        return collect(
            DB::select(
                sprintf('SHOW INDEX FROM `%s`', self::TABLE)
            )
        )
            ->pluck('Key_name')
            ->unique()
            ->values();
    }
};
