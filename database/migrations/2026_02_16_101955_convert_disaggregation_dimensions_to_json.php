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
        // Drop the old column and recreate it as JSON
        DB::statement('ALTER TABLE indicators DROP COLUMN disaggregation_dimensions');
        
        Schema::table('indicators', function (Blueprint $table) {
            $table->json('disaggregation_dimensions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn('disaggregation_dimensions');
            $table->text('disaggregation_dimensions')->nullable();
        });
    }
};
