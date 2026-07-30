<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_leaves', function (Blueprint $table) {

            $table->string('first_notice_proof')
                ->nullable()
                ->after('first_notice_sent_at');

            $table->string('second_notice_proof')
                ->nullable()
                ->after('second_notice_sent_at');

            $table->string('final_notice_proof')
                ->nullable()
                ->after('final_notice_sent_at');

        });
    }

    public function down(): void
    {
        Schema::table('employee_leaves', function (Blueprint $table) {

            $table->dropColumn([
                'first_notice_proof',
                'second_notice_proof',
                'final_notice_proof',
            ]);

        });
    }
};
