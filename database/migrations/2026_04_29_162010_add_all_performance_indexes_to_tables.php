<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bus_details', function (Blueprint $table) {
            $table->index('garage');
            $table->index('name');
            $table->index('plate_number');
            $table->index('created_at');
        });

        Schema::table('job_orders', function (Blueprint $table) {
            $table->index('job_status');
            $table->index('job_type');
            $table->index('job_assign_person');
            $table->index('job_datestart');
            $table->index('job_date_filled');
            $table->index('driver_name');
            $table->index('conductor_name');
            $table->index('direction');
            $table->index('approval_status');
            $table->index('approved_by');
            $table->index('approved_at');
            $table->index('created_at');

            $table->index(['bus_detail_id', 'job_status']);
            $table->index(['job_status', 'created_at']);
            $table->index(['job_type', 'created_at']);
            $table->index(['approval_status', 'created_at']);
        });

        Schema::table('job_order_files', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('job_order_logs', function (Blueprint $table) {
            $table->index('action');
            $table->index('created_at');
            $table->index(['joborder_id', 'created_at']);
        });

        Schema::table('job_order_notes', function (Blueprint $table) {
            $table->index('reason');
            $table->index('created_at');
            $table->index(['joborder_id', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('type');
            $table->index('read_at');
            $table->index('created_at');
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('full_name');
            $table->index('email');
            $table->index('phone_number');
            $table->index('company');
            $table->index('garage');
            $table->index('status');
            $table->index('date_hired');
            $table->index('date_of_birth');
            $table->index('date_resigned');
            $table->index('last_duty');
            $table->index('clearance_date');
            $table->index('last_pay_status');
            $table->index('last_pay_date');
            $table->index('employee_id_permanent');
            $table->index('created_at');

            $table->index(['department_id', 'status']);
            $table->index(['position_id', 'status']);
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->index('title');
            $table->index('created_at');
            $table->index(['department_id', 'title']);
        });

        Schema::table('employee_assets', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('sss_updated_at');
            $table->index('tin_updated_at');
            $table->index('philhealth_updated_at');
            $table->index('pagibig_updated_at');
        });

        Schema::table('employee_histories', function (Blueprint $table) {
            $table->index('title');
            $table->index('offense_id');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('sda_start_date');
            $table->index('sda_end_date');
            $table->index('suspension_start_date');
            $table->index('suspension_end_date');
            $table->index('created_at');

            $table->index(['employee_id', 'start_date']);
        });

        Schema::table('employee_attachments', function (Blueprint $table) {
            $table->index('file_name');
            $table->index('mime_type');
            $table->index('created_at');
        });

        Schema::table('driver_leaves', function (Blueprint $table) {
            $table->index('leave_type');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
            $table->index('offense_level');
            $table->index('created_at');

            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::table('conductor_leaves', function (Blueprint $table) {
            $table->index('leave_type');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
            $table->index('offense_level');
            $table->index('created_at');

            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->index('leave_type');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
            $table->index('offense_level');
            $table->index('created_at');

            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::table('hr_offenses', function (Blueprint $table) {
            $table->index('section');
            $table->index('offense_type');
            $table->index('offense_gravity');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('must_change_password');
            $table->index('location_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('product_name');
            $table->index('supplier_name');
            $table->index('part_number');
            $table->index('stock_qty');
            $table->index('created_at');

            $table->index(['category_id', 'product_name']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('garage');
            $table->index('status');
            $table->index('created_at');

            $table->index('status');
            $table->index('garage');
            $table->index(['status', 'created_at']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->index('removed');
            $table->index('store_name');
            $table->index('created_at');

            $table->index(['purchase_order_id', 'removed']);
            $table->index(['product_id', 'removed']);
        });

        Schema::table('purchase_receives', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['purchase_order_item_id', 'created_at']);
        });

        Schema::table('cashier_remittances', function (Blueprint $table) {
            $table->index('bus_number');
            $table->index('driver_name');
            $table->index('conductor_name');
            $table->index('dispatcher_name');
            $table->index('synced_at');
            $table->index('created_at');

            $table->index(['bus_number', 'created_at']);
        });

        Schema::table('fares', function (Blueprint $table) {
            $table->index('from_location');
            $table->index('to_location');
            $table->index(['from_location', 'to_location']);
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->index('bus_number');
            $table->index('driver_name');
            $table->index('started_at');
            $table->index('ended_at');
            $table->index('created_at');

            $table->index(['bus_number', 'started_at']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('from_location');
            $table->index('to_location');
            $table->index('issued_at');
            $table->index('created_at');

            $table->index(['trip_id', 'issued_at']);
        });

        Schema::table('cctv_job_orders', function (Blueprint $table) {
            $table->index('bus_no');
            $table->index('reported_by');
            $table->index('issue_type');
            $table->index('cctv_part');
            $table->index('status');
            $table->index('fixed_at');
            $table->index('created_at');

            $table->index(['status', 'created_at']);
            $table->index(['bus_no', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::table('employee_logs', function (Blueprint $table) {
            $table->index('action');
            $table->index('created_at');

            $table->index(['employee_id', 'created_at']);
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->index('claim_type');
            $table->index('status');
            $table->index('reference_no');
            $table->index('date_filed');
            $table->index('approval_date');
            $table->index('created_at');

            $table->index(['status', 'created_at']);
        });

        Schema::table('mirasol_biometrics_logs', function (Blueprint $table) {
            $table->index(['employee_no', 'check_time']);
            $table->index(['check_time', 'device_sn']);
            $table->index(['employee_id', 'check_time']);
        });

        Schema::table('receivings', function (Blueprint $table) {
            $table->index('delivered_by');
            $table->index('delivery_date');
            $table->index('created_at');

            $table->index(['location_id', 'delivery_date']);
            $table->index(['received_by', 'created_at']);
        });

        Schema::table('receiving_items', function (Blueprint $table) {
            $table->index('created_at');

            $table->index(['receiving_id', 'product_id']);
            $table->index(['product_id', 'created_at']);
        });

        Schema::table('parts_outs', function (Blueprint $table) {
            $table->index('mechanic_name');
            $table->index('requested_by');
            $table->index('issued_date');
            $table->index('job_order_no');
            $table->index('status');
            $table->index('created_at');

            $table->index(['location_id', 'issued_date']);
            $table->index(['status', 'issued_date']);
        });

        Schema::table('parts_out_items', function (Blueprint $table) {
            $table->index('created_at');

            $table->index(['parts_out_id', 'product_id']);
            $table->index(['product_id', 'created_at']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('reference_type');
            $table->index('reference_id');
            $table->index('movement_type');
            $table->index('transaction_date');
            $table->index('created_at');

            $table->index(['product_id', 'transaction_date']);
            $table->index(['location_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->index('transfer_date');
            $table->index('requested_by');
            $table->index('received_by');
            $table->index('created_at');

            $table->index(['from_location_id', 'transfer_date']);
            $table->index(['to_location_id', 'transfer_date']);
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->index('created_at');

            $table->index(['stock_transfer_id', 'product_id']);
            $table->index(['product_id', 'created_at']);
        });

        Schema::table('payroll_employees', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('source');
            $table->index('department');
            $table->index('position');
            $table->index('is_active');
            $table->index('created_at');
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->index('date_from');
            $table->index('date_to');
            $table->index('status');

            $table->index(['date_from', 'date_to']);
        });

        Schema::table('attendance_daily_summaries', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');

            $table->index(['work_date', 'status']);
        });

        Schema::table('payroll_entries', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });

        Schema::table('payroll_adjustments', function (Blueprint $table) {
            $table->index('type');
            $table->index('created_at');

            $table->index(['payroll_entry_id', 'type']);
        });

        Schema::table('employee_plotting_schedules', function (Blueprint $table) {
            $table->index('is_adjusted');
            $table->index('adjusted_by');
            $table->index('adjusted_at');
        });

        Schema::table('daily_attendance_summaries', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('work_date');
            $table->index('is_absent');
            $table->index('is_holiday');
            $table->index('is_rest_day');
            $table->index('computed_at');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->index('period_start');
            $table->index('period_end');
            $table->index('generated_by');
            $table->index('finalized_by');
            $table->index('created_at');

            $table->index(['period_start', 'period_end']);
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->index('employee_id');
            $table->index('employee_no');
            $table->index('biometric_employee_id');
            $table->index('created_at');
        });

        Schema::table('payroll_employee_salaries', function (Blueprint $table) {
            $table->index('rate_type');
            $table->index('is_active');
            $table->index('created_at');
        });

        Schema::table('it_inventory_items', function (Blueprint $table) {
            $table->index('item_name');
            $table->index('category');
            $table->index('brand');
            $table->index('model');
            $table->index('part_number');
            $table->index('location');
            $table->index('stock_qty');
            $table->index('minimum_stock');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        // Best for production safety:
        // php artisan migrate:rollback
        //
        // If rollback is required, drop indexes manually only if they exist.
    }

    private function indexExists($table, $index)
    {
        $result = DB::selectOne('
        SELECT COUNT(1) as count
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
        AND table_name = ?
        AND index_name = ?
    ', [$table, $index]);

        return $result->count > 0;
    }
};
