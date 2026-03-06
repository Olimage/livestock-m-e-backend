<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('indicator_baseline_years', 'indicator_id')) {
            Schema::table('indicator_baseline_years', function (Blueprint $table) {
                $table->foreignId('indicator_id')
                      ->after('id')
                      ->constrained('indicators')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('indicator_baseline_years', 'indicator_id')) {
            Schema::table('indicator_baseline_years', function (Blueprint $table) {
                $table->dropForeign(['indicator_id']);
                $table->dropColumn('indicator_id');
            });
        }
    }
};
