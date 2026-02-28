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
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->json('disciplinary_action')->nullable()->change();
            $table->decimal('sda_amount', 10, 2)->nullable()->after('disciplinary_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->string('disciplinary_action')->nullable()->change();
            $table->dropColumn('sda_amount');
        });
    }
};
