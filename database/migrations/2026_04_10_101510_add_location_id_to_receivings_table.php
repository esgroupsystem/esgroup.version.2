<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add column first (nullable temporarily)
        Schema::table('receivings', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('id');
        });

        // 2. Set all old records to Main Office (id = 1)
        DB::table('receivings')->update([
            'location_id' => 1,
        ]);

        // 3. Make it NOT NULL + default 1
        Schema::table('receivings', function (Blueprint $table) {
            $table->foreignId('location_id')->default(1)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('receivings', function (Blueprint $table) {
            $table->dropColumn('location_id');
        });
    }
};
