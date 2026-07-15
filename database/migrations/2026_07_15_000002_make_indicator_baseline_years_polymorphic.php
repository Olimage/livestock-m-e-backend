<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicator_baseline_years', function (Blueprint $table) {
            if (Schema::hasColumn('indicator_baseline_years', 'indicator_id')) {
                $table->dropConstrainedForeignId('indicator_id');
            }
        });

        Schema::table('indicator_baseline_years', function (Blueprint $table) {
            $table->nullableMorphs('indicatorable');
        });
    }

    public function down(): void
    {
        Schema::table('indicator_baseline_years', function (Blueprint $table) {
            $table->dropMorphs('indicatorable');
            $table->foreignId('indicator_id')->nullable();
        });
    }
};
