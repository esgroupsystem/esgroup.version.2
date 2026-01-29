<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_assets', function (Blueprint $table) {
            // numbers
            $table->timestamp('sss_updated_at')->nullable()->after('sss_number');
            $table->timestamp('tin_updated_at')->nullable()->after('tin_number');
            $table->timestamp('philhealth_updated_at')->nullable()->after('philhealth_number');
            $table->timestamp('pagibig_updated_at')->nullable()->after('pagibig_number');

            // files
            $table->timestamp('profile_picture_updated_at')->nullable()->after('profile_picture');
            $table->timestamp('birth_certificate_updated_at')->nullable()->after('birth_certificate');
            $table->timestamp('resume_updated_at')->nullable()->after('resume');
            $table->timestamp('contract_updated_at')->nullable()->after('contract');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_assets', function (Blueprint $table) {
            $table->dropColumn([
                'sss_updated_at', 'tin_updated_at', 'philhealth_updated_at', 'pagibig_updated_at',
                'profile_picture_updated_at', 'birth_certificate_updated_at', 'resume_updated_at', 'contract_updated_at',
            ]);
        });
    }
};
