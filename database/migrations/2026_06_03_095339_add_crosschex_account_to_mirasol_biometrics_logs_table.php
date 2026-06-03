<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add CrossChex account columns safely
        |--------------------------------------------------------------------------
        | This is written safely because the migration already partially ran
        | in production. The columns may already exist even if the migration failed.
        */

        if (! Schema::hasColumn('mirasol_biometrics_logs', 'crosschex_account')) {
            Schema::table('mirasol_biometrics_logs', function (Blueprint $table): void {
                $table->string('crosschex_account', 50)
                    ->default('main')
                    ->after('id');
            });
        }

        if (! Schema::hasColumn('mirasol_biometrics_logs', 'crosschex_account_name')) {
            Schema::table('mirasol_biometrics_logs', function (Blueprint $table): void {
                $table->string('crosschex_account_name', 100)
                    ->nullable()
                    ->after('crosschex_account');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize old records
        |--------------------------------------------------------------------------
        | Old records will receive "main" as default account.
        */

        DB::table('mirasol_biometrics_logs')
            ->whereNull('crosschex_account')
            ->orWhere('crosschex_account', '')
            ->update([
                'crosschex_account' => 'main',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Remove old unique index on crosschex_id only
        |--------------------------------------------------------------------------
        | This allows same CrossChex ID from different accounts.
        */

        $this->dropIndexIfExists(
            'mirasol_biometrics_logs',
            'mirasol_biometrics_logs_crosschex_id_unique'
        );

        /*
        |--------------------------------------------------------------------------
        | Remove duplicate old logs before creating unique index
        |--------------------------------------------------------------------------
        | The failed migration means duplicate crosschex_id records already exist.
        | This keeps the latest row based on highest ID and deletes older duplicates.
        */

        $this->removeDuplicateCrossChexLogs();

        /*
        |--------------------------------------------------------------------------
        | Add new indexes
        |--------------------------------------------------------------------------
        */

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

        if (Schema::hasColumn('mirasol_biometrics_logs', 'crosschex_account_name')) {
            Schema::table('mirasol_biometrics_logs', function (Blueprint $table): void {
                $table->dropColumn('crosschex_account_name');
            });
        }

        if (Schema::hasColumn('mirasol_biometrics_logs', 'crosschex_account')) {
            Schema::table('mirasol_biometrics_logs', function (Blueprint $table): void {
                $table->dropColumn('crosschex_account');
            });
        }
    }

    private function removeDuplicateCrossChexLogs(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Keep latest duplicate record
        |--------------------------------------------------------------------------
        | Example:
        | Same crosschex_account + crosschex_id appears 3 times.
        | This deletes the older rows and keeps the row with the highest ID.
        */

        DB::statement("
            DELETE old_logs
            FROM mirasol_biometrics_logs AS old_logs
            INNER JOIN mirasol_biometrics_logs AS newer_logs
                ON old_logs.crosschex_account = newer_logs.crosschex_account
                AND old_logs.crosschex_id = newer_logs.crosschex_id
                AND old_logs.id < newer_logs.id
            WHERE old_logs.crosschex_id IS NOT NULL
              AND old_logs.crosschex_id <> ''
        ");
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
