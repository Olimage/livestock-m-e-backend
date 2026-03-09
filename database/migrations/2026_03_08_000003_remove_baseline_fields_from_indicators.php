<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn([ 'target_value', 'target_year']);
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->decimal('target_value', 10, 2)->nullable()->after('baseline_year');
            $table->integer('target_year')->nullable()->after('target_value');
        });
    }
};
