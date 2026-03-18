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
        Schema::table('driver_leaves', function (Blueprint $table) {
            $table->timestamp('ready_for_duty_notified_at')->nullable()->after('last_action_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_leaves', function (Blueprint $table) {
            $table->dropColumn('ready_for_duty_notified_at',);
        });
    }
};
