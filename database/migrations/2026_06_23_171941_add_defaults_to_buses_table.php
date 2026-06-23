<?php

use App\Models\Bus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table): void {
            $table->string('operational_status')
                ->default(Bus::STATUS_ACTIVE)
                ->change();

            $table->string('sale_status')
                ->default(Bus::SALE_NOT_FOR_SALE)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table): void {
            $table->string('operational_status')
                ->nullable()
                ->change();

            $table->string('sale_status')
                ->nullable()
                ->change();
        });
    }
};
