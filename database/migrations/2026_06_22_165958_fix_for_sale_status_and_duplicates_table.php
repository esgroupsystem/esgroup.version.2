<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bus_for_sale_records')) {
            return;
        }

        /*
         | Your Google Sheet has some blank Status rows.
         | Allow NULL so the database can preserve the sheet exactly.
         */
        DB::statement('ALTER TABLE bus_for_sale_records MODIFY status VARCHAR(80) NULL');

        /*
         | Your For Sale sheet has duplicate Bus Numbers.
         | Remove UNIQUE index on bus_no if it exists.
         */
        $uniqueIndexes = DB::select("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'bus_for_sale_records'
              AND COLUMN_NAME = 'bus_no'
              AND NON_UNIQUE = 0
              AND INDEX_NAME <> 'PRIMARY'
        ");

        foreach ($uniqueIndexes as $index) {
            DB::statement("ALTER TABLE bus_for_sale_records DROP INDEX `{$index->INDEX_NAME}`");
        }

        /*
         | Add sort_order if not yet existing.
         | This preserves the same row order from your sheet.
         */
        if (! Schema::hasColumn('bus_for_sale_records', 'sort_order')) {
            DB::statement('ALTER TABLE bus_for_sale_records ADD sort_order INT UNSIGNED NULL AFTER id');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('bus_for_sale_records')) {
            return;
        }

        DB::statement("UPDATE bus_for_sale_records SET status = 'active' WHERE status IS NULL");
        DB::statement("ALTER TABLE bus_for_sale_records MODIFY status VARCHAR(80) NOT NULL DEFAULT 'active'");
    }
};
