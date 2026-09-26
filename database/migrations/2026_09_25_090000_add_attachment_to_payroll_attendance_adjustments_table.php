<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The approved OT form (scan / photo / PDF) that must be uploaded when filing
 * an Overtime adjustment. Stored on the private "local" disk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_attendance_adjustments', function (Blueprint $table): void {
            $table->string('attachment_path')->nullable()->after('remarks');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime', 100)->nullable()->after('attachment_name');
            $table->unsignedInteger('attachment_size')->nullable()->after('attachment_mime');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_attendance_adjustments', function (Blueprint $table): void {
            $table->dropColumn(['attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size']);
        });
    }
};
