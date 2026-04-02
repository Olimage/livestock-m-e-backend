<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['output_indicators', 'outcome_indicators', 'impact_indicators', 'bond_output_indicators', 'pillar_program_output_indicators'] as $table) {
            Schema::table($table, fn(Blueprint $t) => $t->string('code')->nullable()->change());
        }
    }

    public function down(): void
    {
        foreach (['output_indicators', 'outcome_indicators', 'impact_indicators', 'bond_output_indicators', 'pillar_program_output_indicators'] as $table) {
            Schema::table($table, fn(Blueprint $t) => $t->string('code')->nullable(false)->change());
        }
    }
};
