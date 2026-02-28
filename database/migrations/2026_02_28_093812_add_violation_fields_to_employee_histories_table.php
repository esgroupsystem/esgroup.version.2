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
            $table->unsignedBigInteger('offense_id')->nullable()->after('title');
            $table->string('disciplinary_action')->nullable()->after('offense_id');
            $table->foreign('offense_id')->references('id')->on('hr_offenses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->dropForeign(['offense_id']);
            $table->dropColumn(['offense_id', 'disciplinary_action']);
        });
    }
};
