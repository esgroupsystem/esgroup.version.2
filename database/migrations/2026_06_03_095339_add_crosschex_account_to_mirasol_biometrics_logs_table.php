<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mirasol_biometrics_logs', 'crosschex_account')) {
            Schema::table('mirasol_biometrics_logs', function (Blueprint $table): void {
                $table->string('crosschex_account', 50)
                    ->default('main')
                    ->after('id');

                $table->string('crosschex_account_name', 100)
                    ->nullable()
                    ->after('crosschex_account');
            });
        }

        // If you previously made crosschex_id unique, remove it.
        $this->dropIndexIfExists(
            'mirasol_biometrics_logs',
            'mirasol_biometrics_logs_crosschex_id_unique'
        );

        $this->addIndexIfMissing(
            'mirasol_biometrics_logs',
            'mirasol_logs_account_cross_id_unique',
            'CREATE UNIQUE INDEX mirasol_logs_account_cross_id_unique
             ON mirasol_biometrics_logs (crosschex_account, crosschex_id)'
        );

        $this->addIndexIfMissing(
            'mirasol_biometrics_logs',
            'mirasol_logs_account_check_time_index',
            'CREATE INDEX mirasol_logs_account_check_time_index
             ON mirasol_biometrics_logs (crosschex_account, check_time)'
        );
    }

    public function down(): void
    {
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mirasol_logs_account_cross_id_unique');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mirasol_logs_account_check_time_index');

        if (Schema::hasColumn('mirasol_biometrics_logs', 'crosschex_account')) {
            Schema::table('mirasol_biometrics_logs', function (Blueprint $table): void {
                $table->dropColumn(['crosschex_account', 'crosschex_account_name']);
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::select(
            'SELECT INDEX_NAME
             FROM information_schema.statistics
             WHERE table_schema = ?
             AND table_name = ?
             AND index_name = ?
             LIMIT 1',
            [$database, $table, $index]
        );

        return count($result) > 0;
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }

    private function addIndexIfMissing(string $table, string $index, string $sql): void
    {
        if (! $this->indexExists($table, $index)) {
            DB::statement($sql);
        }
    }
};
