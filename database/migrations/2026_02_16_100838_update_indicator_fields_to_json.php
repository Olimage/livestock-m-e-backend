<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old columns and recreate them as JSON
        DB::statement('ALTER TABLE indicators DROP COLUMN collection_frequency');
        DB::statement('ALTER TABLE indicators DROP COLUMN reporting_frequency');
        
        Schema::table('indicators', function (Blueprint $table) {
            $table->json('collection_frequency')->nullable();
            $table->json('reporting_frequency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn(['collection_frequency', 'reporting_frequency']);
            $table->string('collection_frequency')->nullable();
            $table->string('reporting_frequency')->nullable();
        });
    }
};
