<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'payroll_attendance_adjustments';

    private const INDEX = 'paa_crosschex_id_idx';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        if (! Schema::hasColumn(self::TABLE, 'crosschex_id')) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->string('crosschex_id')->nullable()->after('employee_name');
            });
        }

        if (! Schema::hasIndex(self::TABLE, self::INDEX)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->index('crosschex_id', self::INDEX);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        if (Schema::hasIndex(self::TABLE, self::INDEX)) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->dropIndex(self::INDEX);
            });
        }

        if (Schema::hasColumn(self::TABLE, 'crosschex_id')) {
            Schema::table(self::TABLE, function (Blueprint $table): void {
                $table->dropColumn('crosschex_id');
            });
        }
    }
};
