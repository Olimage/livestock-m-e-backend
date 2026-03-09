<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->decimal('baseline_value', 10, 2)->nullable()->after('measurement_unit');
            $table->integer('baseline_year')->nullable()->after('baseline_value');
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn(['baseline_value', 'baseline_year']);
        });
    }
};
