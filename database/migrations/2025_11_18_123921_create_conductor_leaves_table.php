<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConductorLeavesTable extends Migration
{
    public function up()
    {
        Schema::create('conductor_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('leave_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('days')->default(0);
            $table->text('reason')->nullable();
            $table->tinyInteger('offense_level')->nullable(); // 1,2,3...
            $table->string('status')->nullable(); // e.g. cancelled, terminated, completed
            $table->text('last_action_note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('conductor_leaves');
    }
}
