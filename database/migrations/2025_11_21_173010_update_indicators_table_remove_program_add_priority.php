<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            // Remove program_id foreign key and column if it exists
            if (Schema::hasColumn('indicators', 'program_id')) {
                $table->dropForeign(['program_id']);
                $table->dropColumn('program_id');
            }
            
            // Add presidential_priority_id foreign key
            $table->foreignId('presidential_priority_id')->nullable()->after('id')->constrained('presidential_priorities')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            // Drop presidential_priority_id
            $table->dropForeign(['presidential_priority_id']);
            $table->dropColumn('presidential_priority_id');
            
            // Restore program_id if needed
            $table->foreignId('program_id')->nullable()->after('id')->constrained('programs')->nullOnDelete();
        });
    }
};
