<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('remarks');
            $table->timestamp('rolled_back_at')->nullable()->after('status');
            $table->foreignId('rolled_back_by')
                ->nullable()
                ->after('rolled_back_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('rollback_reason')->nullable()->after('rolled_back_by');
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('qty');
            $table->timestamp('rolled_back_at')->nullable()->after('status');
            $table->foreignId('rolled_back_by')
                ->nullable()
                ->after('rolled_back_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropForeign(['rolled_back_by']);
            $table->dropColumn([
                'status',
                'rolled_back_at',
                'rolled_back_by',
            ]);
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['rolled_back_by']);
            $table->dropColumn([
                'status',
                'rolled_back_at',
                'rolled_back_by',
                'rollback_reason',
            ]);
        });
    }
};
