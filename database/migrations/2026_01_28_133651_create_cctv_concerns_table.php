<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cctv_job_orders', function (Blueprint $table) {
            $table->id();

            $table->string('jo_no')->unique();    
            $table->string('bus_no');                  
            $table->string('reported_by')->nullable(); 

            $table->string('issue_type');     
            $table->string('cctv_part')->nullable();  

            $table->text('problem_details');          
            $table->text('action_taken')->nullable();  

            $table->string('status')->default('Open'); 

            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('fixed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cctv_job_orders');
    }
};
