<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'mirasol_biometrics_logs';

    public function up(): void
    {
        if (! Schema::hasTable($this->tableName)) {
            return;
        }

        if (! Schema::hasColumn($this->tableName, 'source_employee_id')) {
            Schema::table($this->tableName, function (Blueprint $table): void {
                $table->string('source_employee_id', 100)
                    ->nullable()
                    ->after('crosschex_id');
            });
        }

        /*
         * Older versions stored the CrossChex employee identifier in the
         * unsigned BIGINT employee_id column. Preserve those numeric values in
         * the new string column before new UUID/string identifiers are synced.
         */
        DB::table($this->tableName)
            ->whereNull('source_employee_id')
            ->whereNotNull('employee_id')
            ->update([
                'source_employee_id' => DB::raw('CAST(employee_id AS CHAR)'),
            ]);

        if (! $this->indexExists('mbl_account_source_employee_idx')) {
            Schema::table($this->tableName, function (Blueprint $table): void {
                $table->index(
                    ['crosschex_account', 'source_employee_id'],
                    'mbl_account_source_employee_idx'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable($this->tableName)) {
            return;
        }

        if ($this->indexExists('mbl_account_source_employee_idx')) {
            Schema::table($this->tableName, function (Blueprint $table): void {
                $table->dropIndex('mbl_account_source_employee_idx');
            });
        }

        if (Schema::hasColumn($this->tableName, 'source_employee_id')) {
            Schema::table($this->tableName, function (Blueprint $table): void {
                $table->dropColumn('source_employee_id');
            });
        }
    }

    private function indexExists(string $indexName): bool
    {
        $result = DB::select(
            'SELECT INDEX_NAME
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [DB::getDatabaseName(), $this->tableName, $indexName]
        );

        return $result !== [];
    }
};
