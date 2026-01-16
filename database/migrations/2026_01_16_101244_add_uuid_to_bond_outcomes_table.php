<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bond_outcomes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Generate UUIDs for existing records
        DB::table('bond_outcomes')->whereNull('uuid')->get()->each(function ($bondOutcome) {
            DB::table('bond_outcomes')
                ->where('id', $bondOutcome->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('bond_outcomes', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bond_outcomes', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
