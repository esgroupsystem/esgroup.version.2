<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_attendance_adjustments')) {
            /*
             * The original table used an ENUM that no longer contains the
             * current adjustment types (sick_leave, medical_leave, overtime,
             * typhoon_disaster, etc.). Convert it to VARCHAR so the model and
             * database cannot drift apart again when a new adjustment type is
             * added later.
             */
            if (Schema::hasColumn('payroll_attendance_adjustments', 'adjustment_type')) {
                Schema::table('payroll_attendance_adjustments', function (Blueprint $table): void {
                    $table->string('adjustment_type', 50)->change();
                });
            }

            Schema::table('payroll_attendance_adjustments', function (Blueprint $table): void {
                if (! Schema::hasColumn('payroll_attendance_adjustments', 'approved_minutes')) {
                    $table->unsignedInteger('approved_minutes')->nullable()->after('offset_source_logs');
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'defer_to_next_payroll')) {
                    $table->boolean('defer_to_next_payroll')->default(false)->after('approved_minutes');
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'payroll_effective_date')) {
                    $table->date('payroll_effective_date')->nullable()->after('defer_to_next_payroll');
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'status')) {
                    $table->string('status', 20)->default('approved')->after('remarks');
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'paid_payroll_id')) {
                    $table->foreignId('paid_payroll_id')->nullable()->after('payroll_effective_date')->constrained('payrolls')->nullOnDelete();
                }

                if (! Schema::hasColumn('payroll_attendance_adjustments', 'paid_payroll_item_id')) {
                    $table->foreignId('paid_payroll_item_id')->nullable()->after('paid_payroll_id')->constrained('payroll_items')->nullOnDelete();
                }
            });

            Schema::table('payroll_attendance_adjustments', function (Blueprint $table): void {
                $table->index(['status', 'adjustment_type', 'work_date'], 'paa_status_type_date_idx');
                $table->index(['payroll_effective_date', 'status'], 'paa_effective_status_idx');
            });

            DB::table('payroll_attendance_adjustments')
                ->whereNull('status')
                ->update(['status' => 'approved']);
        }

        if (Schema::hasTable('payroll_items')) {
            Schema::table('payroll_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('payroll_items', 'company_name_snapshot')) {
                    $table->string('company_name_snapshot')->nullable()->after('employee_name');
                }

                if (! Schema::hasColumn('payroll_items', 'total_night_differential_minutes')) {
                    $table->unsignedInteger('total_night_differential_minutes')->default(0)->after('total_overtime_minutes');
                }

                if (! Schema::hasColumn('payroll_items', 'night_differential_pay')) {
                    $table->decimal('night_differential_pay', 15, 2)->default(0)->after('overtime_pay');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_items')) {
            Schema::table('payroll_items', function (Blueprint $table): void {
                if (Schema::hasColumn('payroll_items', 'night_differential_pay')) {
                    $table->dropColumn('night_differential_pay');
                }

                if (Schema::hasColumn('payroll_items', 'total_night_differential_minutes')) {
                    $table->dropColumn('total_night_differential_minutes');
                }

                if (Schema::hasColumn('payroll_items', 'company_name_snapshot')) {
                    $table->dropColumn('company_name_snapshot');
                }
            });
        }

        if (Schema::hasTable('payroll_attendance_adjustments')) {
            Schema::table('payroll_attendance_adjustments', function (Blueprint $table): void {
                foreach (['paa_status_type_date_idx', 'paa_effective_status_idx'] as $index) {
                    try {
                        $table->dropIndex($index);
                    } catch (Throwable) {
                        // Index may not exist on older deployments.
                    }
                }
            });

            Schema::table('payroll_attendance_adjustments', function (Blueprint $table): void {
                foreach (['paid_payroll_item_id', 'paid_payroll_id', 'rejected_by', 'approved_by'] as $foreignId) {
                    if (Schema::hasColumn('payroll_attendance_adjustments', $foreignId)) {
                        try {
                            $table->dropConstrainedForeignId($foreignId);
                        } catch (Throwable) {
                            try {
                                $table->dropForeign([$foreignId]);
                            } catch (Throwable) {
                                // Foreign key name may differ.
                            }

                            if (Schema::hasColumn('payroll_attendance_adjustments', $foreignId)) {
                                $table->dropColumn($foreignId);
                            }
                        }
                    }
                }

                foreach ([
                    'approved_minutes',
                    'defer_to_next_payroll',
                    'payroll_effective_date',
                    'status',
                    'approved_at',
                    'rejected_at',
                    'rejection_reason',
                ] as $column) {
                    if (Schema::hasColumn('payroll_attendance_adjustments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
