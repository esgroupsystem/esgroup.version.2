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

        $this->addIndexIfPossible(['check_time'], 'mbl_check_time_idx');
        $this->addIndexIfPossible(['employee_no', 'check_time'], 'mbl_employee_no_check_time_idx');
        $this->addIndexIfPossible(['employee_id', 'check_time'], 'mbl_employee_id_check_time_idx');
        $this->addIndexIfPossible(['crosschex_id', 'check_time'], 'mbl_crosschex_id_check_time_idx');
    }

    public function down(): void
    {
        if (! Schema::hasTable($this->tableName)) {
            return;
        }

        $this->dropIndexIfExists('mbl_crosschex_id_check_time_idx');
        $this->dropIndexIfExists('mbl_employee_id_check_time_idx');
        $this->dropIndexIfExists('mbl_employee_no_check_time_idx');
        $this->dropIndexIfExists('mbl_check_time_idx');
    }

    private function addIndexIfPossible(array $columns, string $indexName): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($this->tableName, $column)) {
                return;
            }
        }

        if ($this->indexExists($indexName)) {
            return;
        }

        Schema::table($this->tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $indexName): void
    {
        if (! $this->indexExists($indexName)) {
            return;
        }

        Schema::table($this->tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $indexName): bool
    {
        $table = str_replace('`', '``', $this->tableName);

        $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return count($result) > 0;
    }
};
