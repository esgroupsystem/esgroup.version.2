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
        Schema::table('conductor_leaves', function (Blueprint $table) {
            $table->timestamp('first_notice_sent_at')->nullable()->after('reason');
            $table->timestamp('second_notice_sent_at')->nullable()->after('first_notice_sent_at');
            $table->timestamp('final_notice_sent_at')->nullable()->after('second_notice_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_leaves', function (Blueprint $table) {
            $table->dropColumn([
                'first_notice_sent_at',
                'second_notice_sent_at',
                'final_notice_sent_at',
            ]);
        });
    }
};
