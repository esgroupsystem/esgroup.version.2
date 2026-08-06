<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('daily_attendance_summaries')) {
            return;
        }

        if (Schema::hasColumn('daily_attendance_summaries', 'meta')) {
            return;
        }

        Schema::table('daily_attendance_summaries', function (Blueprint $table): void {
            $table->json('meta')
                ->nullable()
                ->after('remarks');
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('daily_attendance_summaries')
            || ! Schema::hasColumn('daily_attendance_summaries', 'meta')
        ) {
            return;
        }

        Schema::table('daily_attendance_summaries', function (Blueprint $table): void {
            $table->dropColumn('meta');
        });
    }
};
