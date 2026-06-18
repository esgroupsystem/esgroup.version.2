<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('employee_plotting_schedules', 'employee_no', 'eps_employee_no_idx');
        $this->addIndexIfMissing('employee_plotting_schedules', 'biometric_employee_id', 'eps_biometric_employee_id_idx');
        $this->addIndexIfMissing('employee_plotting_schedules', 'work_date', 'eps_work_date_idx');

        $this->addIndexIfMissing('mirasol_biometrics_logs', 'employee_id', 'mbl_employee_id_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'employee_no', 'mbl_employee_no_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'biometric_employee_id', 'mbl_biometric_employee_id_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'crosschex_id', 'mbl_crosschex_id_idx');

        $this->addIndexIfMissing('mirasol_biometrics_logs', 'check_time', 'mbl_check_time_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'date_time', 'mbl_date_time_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'datetime', 'mbl_datetime_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'punch_time', 'mbl_punch_time_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'scan_time', 'mbl_scan_time_idx');
        $this->addIndexIfMissing('mirasol_biometrics_logs', 'log_time', 'mbl_log_time_idx');

        $this->addIndexIfMissing('payroll_attendance_adjustments', 'employee_no', 'paa_employee_no_idx');
        $this->addIndexIfMissing('payroll_attendance_adjustments', 'biometric_employee_id', 'paa_biometric_employee_id_idx');
        $this->addIndexIfMissing('payroll_attendance_adjustments', 'crosschex_id', 'paa_crosschex_id_idx');
        $this->addIndexIfMissing('payroll_attendance_adjustments', 'work_date', 'paa_work_date_idx');

        $this->addIndexIfMissing('daily_attendance_summaries', 'employee_no', 'das_employee_no_idx');
        $this->addIndexIfMissing('daily_attendance_summaries', 'biometric_employee_id', 'das_biometric_employee_id_idx');
        $this->addIndexIfMissing('daily_attendance_summaries', 'work_date', 'das_work_date_idx');
        $this->addIndexIfMissing('daily_attendance_summaries', 'attendance_status', 'das_attendance_status_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('employee_plotting_schedules', 'eps_employee_no_idx');
        $this->dropIndexIfExists('employee_plotting_schedules', 'eps_biometric_employee_id_idx');
        $this->dropIndexIfExists('employee_plotting_schedules', 'eps_work_date_idx');

        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_employee_id_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_employee_no_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_biometric_employee_id_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_crosschex_id_idx');

        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_check_time_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_date_time_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_datetime_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_punch_time_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_scan_time_idx');
        $this->dropIndexIfExists('mirasol_biometrics_logs', 'mbl_log_time_idx');

        $this->dropIndexIfExists('payroll_attendance_adjustments', 'paa_employee_no_idx');
        $this->dropIndexIfExists('payroll_attendance_adjustments', 'paa_biometric_employee_id_idx');
        $this->dropIndexIfExists('payroll_attendance_adjustments', 'paa_crosschex_id_idx');
        $this->dropIndexIfExists('payroll_attendance_adjustments', 'paa_work_date_idx');

        $this->dropIndexIfExists('daily_attendance_summaries', 'das_employee_no_idx');
        $this->dropIndexIfExists('daily_attendance_summaries', 'das_biometric_employee_id_idx');
        $this->dropIndexIfExists('daily_attendance_summaries', 'das_work_date_idx');
        $this->dropIndexIfExists('daily_attendance_summaries', 'das_attendance_status_idx');
    }

    private function addIndexIfMissing(string $table, string $column, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($column, $indexName) {
            $tableBlueprint->index($column, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::select(
            'SELECT COUNT(1) AS index_count
             FROM information_schema.statistics
             WHERE table_schema = ?
             AND table_name = ?
             AND index_name = ?',
            [$database, $table, $indexName]
        );

        return (int) ($result[0]->index_count ?? 0) > 0;
    }
};
