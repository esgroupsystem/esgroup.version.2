<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('job_orders_maintenance', 'mechanic_names')) {
            Schema::table('job_orders_maintenance', function (Blueprint $table): void {
                $table->json('mechanic_names')
                    ->nullable()
                    ->after('full_name');
            });
        }

        if (! Schema::hasColumn('job_orders_maintenance', 'repair_types')) {
            Schema::table('job_orders_maintenance', function (Blueprint $table): void {
                $table->json('repair_types')
                    ->nullable()
                    ->after('mechanic_names');
            });
        }

        if (! Schema::hasTable('job_order_maintenance_status_periods')) {
            Schema::create('job_order_maintenance_status_periods', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('job_order_maintenance_id');
                $table->string('status', 50);
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->timestamps();

                $table->foreign(
                    'job_order_maintenance_id',
                    'jom_period_job_order_fk'
                )
                    ->references('id')
                    ->on('job_orders_maintenance')
                    ->cascadeOnDelete();

                $table->foreign(
                    'changed_by',
                    'jom_period_changed_by_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    ['job_order_maintenance_id', 'status'],
                    'jom_period_job_status_idx'
                );

                $table->index(
                    ['job_order_maintenance_id', 'ended_at'],
                    'jom_period_open_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_maintenance_status_periods');

        Schema::table('job_orders_maintenance', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('job_orders_maintenance', 'mechanic_names')) {
                $columns[] = 'mechanic_names';
            }

            if (Schema::hasColumn('job_orders_maintenance', 'repair_types')) {
                $columns[] = 'repair_types';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
