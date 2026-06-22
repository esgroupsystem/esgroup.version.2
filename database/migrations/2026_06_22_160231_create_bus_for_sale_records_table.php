<?php

use App\Models\Bus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_for_sale_records', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('bus_id')
                ->nullable()
                ->constrained('buses')
                ->nullOnDelete();

            $table->string('bus_no', 50)->unique();
            $table->string('plate_no', 50)->nullable();
            $table->string('company', 100)->nullable();
            $table->string('garage', 100)->nullable();

            $table->string('status', 80)->default(Bus::STATUS_ACTIVE);

            $table->string('storage_area', 150)->nullable();
            $table->date('breakdown_start_date')->nullable();
            $table->date('breakdown_end_date')->nullable();

            /*
             | This keeps your Excel column exactly.
             | You can rename this later once the business meaning is final.
             */
            $table->string('column_11', 150)->nullable();

            /*
             | Stored snapshot. The model also has live_days_in_breakdown
             | so the dashboard can always display updated days.
             */
            $table->unsignedInteger('days_in_breakdown')->default(0);

            $table->string('unit_location', 150)->nullable();
            $table->string('progress', 150)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['company', 'garage']);
            $table->index('status');
            $table->index('breakdown_start_date');
            $table->index('days_in_breakdown');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_for_sale_records');
    }
};
