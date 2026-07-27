<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicator_reports', function (Blueprint $table) {
            $table->decimal('baseline', 20, 4)->nullable()->after('reporting_period_id');
            $table->unsignedSmallInteger('baseline_year')->nullable()->after('baseline');
        });
    }

    public function down(): void
    {
        Schema::table('indicator_reports', function (Blueprint $table) {
            $table->dropColumn(['baseline', 'baseline_year']);
        });
    }
};
