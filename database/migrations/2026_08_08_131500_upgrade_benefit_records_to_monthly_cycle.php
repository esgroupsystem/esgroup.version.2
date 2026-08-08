<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('benefit_contribution_records', function (Blueprint $table): void {
            $table->string('monthly_key', 191)
                ->nullable()
                ->after('id');

            $table->decimal('business_first_cutoff_gross', 15, 2)
                ->default(0)
                ->after('gross_compensation');

            $table->decimal('business_second_cutoff_gross', 15, 2)
                ->default(0)
                ->after('business_first_cutoff_gross');

            $table->unique('monthly_key', 'benefit_records_monthly_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('benefit_contribution_records', function (Blueprint $table): void {
            $table->dropUnique('benefit_records_monthly_key_unique');
            $table->dropColumn([
                'monthly_key',
                'business_first_cutoff_gross',
                'business_second_cutoff_gross',
            ]);
        });
    }
};
