<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('driver_leaves', function (Blueprint $table) {
            $table->string('status')->default('active')->after('reason');
            $table->text('last_action_note')->nullable()->after('status');
            $table->integer('offense_level')->nullable()->after('last_action_note');
        });
    }

    public function down()
    {
        Schema::table('driver_leaves', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_action_note', 'offense_level']);
        });
    }
};
