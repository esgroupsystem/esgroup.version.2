<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add monitoring fields for bus analytics.
     */
    public function up(): void
    {
        if (! Schema::hasTable('buses')) {
            return;
        }

        Schema::table('buses', function (Blueprint $table): void {
            if (! Schema::hasColumn('buses', 'operational_status')) {
                $table->string('operational_status', 50)
                    ->default('active')
                    ->after('case_number')
                    ->index();
            }

            if (! Schema::hasColumn('buses', 'sale_status')) {
                $table->string('sale_status', 50)
                    ->default('not_for_sale')
                    ->after('operational_status')
                    ->index();
            }

            if (! Schema::hasColumn('buses', 'monitoring_remarks')) {
                $table->text('monitoring_remarks')
                    ->nullable()
                    ->after('sale_status');
            }

            if (! Schema::hasColumn('buses', 'status_updated_at')) {
                $table->timestamp('status_updated_at')
                    ->nullable()
                    ->after('monitoring_remarks');
            }
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        if (! Schema::hasTable('buses')) {
            return;
        }

        $columns = [];

        foreach ([
            'operational_status',
            'sale_status',
            'monitoring_remarks',
            'status_updated_at',
        ] as $column) {
            if (Schema::hasColumn('buses', $column)) {
                $columns[] = $column;
            }
        }

        if ($columns !== []) {
            Schema::table('buses', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
