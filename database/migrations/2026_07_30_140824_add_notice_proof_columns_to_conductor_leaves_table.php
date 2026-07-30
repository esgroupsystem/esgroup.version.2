<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('conductor_leaves', 'first_notice_proof')) {
            Schema::table('conductor_leaves', function (Blueprint $table): void {
                $table->string('first_notice_proof')
                    ->nullable()
                    ->after('first_notice_sent_at');
            });
        }

        if (! Schema::hasColumn('conductor_leaves', 'second_notice_proof')) {
            Schema::table('conductor_leaves', function (Blueprint $table): void {
                $table->string('second_notice_proof')
                    ->nullable()
                    ->after('second_notice_sent_at');
            });
        }

        if (! Schema::hasColumn('conductor_leaves', 'final_notice_proof')) {
            Schema::table('conductor_leaves', function (Blueprint $table): void {
                $table->string('final_notice_proof')
                    ->nullable()
                    ->after('final_notice_sent_at');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('conductor_leaves', 'first_notice_proof')
                ? 'first_notice_proof'
                : null,
            Schema::hasColumn('conductor_leaves', 'second_notice_proof')
                ? 'second_notice_proof'
                : null,
            Schema::hasColumn('conductor_leaves', 'final_notice_proof')
                ? 'final_notice_proof'
                : null,
        ]));

        if ($columns !== []) {
            Schema::table('conductor_leaves', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
