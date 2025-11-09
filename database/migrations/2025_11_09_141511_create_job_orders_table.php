<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bus_detail_id')->constrained('bus_details')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // From your 3rd image / form
            $table->string('job_name')->nullable();                     // e.g. "Ticketing Issue"
            $table->string('job_type')->nullable();                     // the “Issue Type” select
            $table->string('job_datestart')->nullable();                // kept as varchar to match your table
            $table->string('job_time_start')->nullable();
            $table->string('job_time_end')->nullable();
            $table->string('job_sitNumber')->nullable();                // seat number
            $table->text('job_remarks')->nullable();
            $table->string('job_status')->default('Pending');
            $table->string('job_assign_person')->nullable();
            $table->string('job_date_filled')->nullable();
            $table->string('job_creator')->nullable();                  // you’re also storing text name

            // Optional extras that are useful for buses
            $table->string('driver_name')->nullable();
            $table->string('conductor_name')->nullable();
            $table->string('direction')->nullable();                    // South/North Bound

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
