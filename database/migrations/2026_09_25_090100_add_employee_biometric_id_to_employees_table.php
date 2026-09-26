<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual link between an HR employee (201 file) and its biometric record, for
 * employees whose Employee ID is not encoded consistently in both places.
 * One HR employee <-> one biometric record; removing either side clears it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->foreignId('employee_biometric_id')
                ->nullable()
                ->after('employee_id_permanent')
                ->unique()
                ->constrained('employee_biometrics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('employee_biometric_id');
        });
    }
};
