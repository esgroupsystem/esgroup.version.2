<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('job_orders_maintenance')
            ->whereIn('status', ['open', 'in_progress', 'completed', 'cancelled'])
            ->update(['status' => 'standby']);

        DB::statement("
            ALTER TABLE job_orders_maintenance
            MODIFY status VARCHAR(255) NOT NULL DEFAULT 'standby'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE job_orders_maintenance
            MODIFY status VARCHAR(255) NOT NULL DEFAULT 'open'
        ");
    }
};
