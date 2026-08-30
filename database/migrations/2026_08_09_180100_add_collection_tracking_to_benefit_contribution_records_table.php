<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('benefit_contribution_records', function (Blueprint $table): void {
            $table->decimal('sss_employee_collected', 15, 2)->default(0)->after('sss_employee_total');
            $table->decimal('philhealth_employee_collected', 15, 2)->default(0)->after('philhealth_employee');
            $table->decimal('pagibig_employee_collected', 15, 2)->default(0)->after('pagibig_employee');
            $table->decimal('employee_share_unrecovered', 15, 2)->default(0)->after('grand_total');
            $table->string('settlement_status', 40)->default('complete')->after('employee_share_unrecovered');
            $table->json('settlement_meta')->nullable()->after('settlement_status');

            $table->index('settlement_status', 'benefit_records_settlement_status_idx');
        });

        // Existing finalized benefit records predate settlement tracking and
        // historically represented fully collected employee shares. Preserve
        // that meaning instead of making old records appear as zero-collected.
        DB::table('benefit_contribution_records')->update([
            'sss_employee_collected' => DB::raw('COALESCE(sss_employee_total, 0)'),
            'philhealth_employee_collected' => DB::raw('COALESCE(philhealth_employee, 0)'),
            'pagibig_employee_collected' => DB::raw('COALESCE(pagibig_employee, 0)'),
            'employee_share_unrecovered' => 0,
            'settlement_status' => 'complete',
        ]);
    }

    public function down(): void
    {
        Schema::table('benefit_contribution_records', function (Blueprint $table): void {
            $table->dropIndex('benefit_records_settlement_status_idx');
            $table->dropColumn([
                'sss_employee_collected',
                'philhealth_employee_collected',
                'pagibig_employee_collected',
                'employee_share_unrecovered',
                'settlement_status',
                'settlement_meta',
            ]);
        });
    }
};
