<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receiving_items', function (Blueprint $table) {
            $table->unsignedInteger('qty_rolled_back')->default(0)->after('qty_delivered');
            $table->timestamp('last_rolled_back_at')->nullable()->after('qty_rolled_back');
        });
    }

    public function down(): void
    {
        Schema::table('receiving_items', function (Blueprint $table) {
            $table->dropColumn(['qty_rolled_back', 'last_rolled_back_at']);
        });
    }
};
