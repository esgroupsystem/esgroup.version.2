<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add remarks column to employee histories.
     */
    public function up(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('description');
        });
    }

    /**
     * Remove remarks column from employee histories.
     */
    public function down(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
