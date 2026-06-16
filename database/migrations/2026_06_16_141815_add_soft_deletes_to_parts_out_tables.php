<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parts_outs')) {
            Schema::table('parts_outs', function (Blueprint $table) {
                if (! Schema::hasColumn('parts_outs', 'deleted_at')) {
                    $table->softDeletes();
                }

                if (! Schema::hasColumn('parts_outs', 'rolled_back_at')) {
                    $table->timestamp('rolled_back_at')->nullable()->after('status');
                }

                if (! Schema::hasColumn('parts_outs', 'rolled_back_by')) {
                    $table->unsignedBigInteger('rolled_back_by')->nullable()->after('rolled_back_at');
                }

                if (! Schema::hasColumn('parts_outs', 'rollback_reason')) {
                    $table->text('rollback_reason')->nullable()->after('rolled_back_by');
                }
            });
        }

        if (Schema::hasTable('parts_out_items')) {
            Schema::table('parts_out_items', function (Blueprint $table) {
                if (! Schema::hasColumn('parts_out_items', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('parts_outs')) {
            Schema::table('parts_outs', function (Blueprint $table) {
                if (Schema::hasColumn('parts_outs', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }

                if (Schema::hasColumn('parts_outs', 'rolled_back_at')) {
                    $table->dropColumn('rolled_back_at');
                }

                if (Schema::hasColumn('parts_outs', 'rolled_back_by')) {
                    $table->dropColumn('rolled_back_by');
                }

                if (Schema::hasColumn('parts_outs', 'rollback_reason')) {
                    $table->dropColumn('rollback_reason');
                }
            });
        }

        if (Schema::hasTable('parts_out_items')) {
            Schema::table('parts_out_items', function (Blueprint $table) {
                if (Schema::hasColumn('parts_out_items', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
