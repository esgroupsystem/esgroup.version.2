<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop old plain text columns if exist
            if (Schema::hasColumn('employees', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('employees', 'position')) {
                $table->dropColumn('position');
            }

            // Add proper relationships
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('position_id');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
        });
    }
};
