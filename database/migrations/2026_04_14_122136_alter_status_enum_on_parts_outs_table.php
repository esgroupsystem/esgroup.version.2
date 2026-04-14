<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE parts_outs
            MODIFY status ENUM('posted', 'cancelled', 'rolled_back')
            NOT NULL DEFAULT 'posted'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE parts_outs
            MODIFY status ENUM('posted', 'cancelled')
            NOT NULL DEFAULT 'posted'
        ");
    }
};
