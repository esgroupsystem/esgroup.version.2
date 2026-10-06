<?php

declare(strict_types=1);

use App\Support\Payroll\PayrollSettingCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSIONS = ['payroll-settings.view', 'payroll-settings.manage'];

    public function up(): void
    {
        // Payroll Settings: every rate the payroll engine uses, one row per "effective from" date.
        Schema::create('payroll_setting_versions', function (Blueprint $table): void {
            $table->id();
            $table->date('effective_from')->unique();
            $table->string('label', 150);
            $table->json('values');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Payroll Rules: user-made earnings and deductions (fixed, percent, per unit or formula).
        Schema::create('payroll_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 60);
            $table->string('kind', 20);
            $table->string('method', 20);
            $table->decimal('amount', 14, 4)->nullable();
            $table->string('base', 40)->nullable();
            $table->string('unit', 40)->nullable();
            $table->text('formula')->nullable();
            $table->string('cutoff', 20)->default('every');
            $table->string('rate_type', 20)->default('all');
            $table->json('payroll_groups')->nullable();
            $table->json('employee_biometric_ids')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kind', 'is_active']);
            $table->index('code');
        });

        // The first version holds today's values, so payroll keeps computing exactly as before.
        DB::table('payroll_setting_versions')->insert([
            'effective_from' => '2000-01-01',
            'label' => 'Starting rules',
            'values' => json_encode(PayrollSettingCatalog::defaults()),
            'notes' => 'Copied from the rates the payroll used before Payroll Settings existed.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_rules');
        Schema::dropIfExists('payroll_setting_versions');

        Permission::query()->whereIn('name', self::PERMISSIONS)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
