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
        // Add department_id to sectoral_goals
        Schema::table('sectoral_goals', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('responsible_entity')->constrained()->nullOnDelete();
        });

        // Add department_id to bond_outcomes
        Schema::table('bond_outcomes', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('responsible_entity')->constrained()->nullOnDelete();
        });

        // Add department_id to nlgas_pillars
        Schema::table('nlgas_pillars', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('responsible_entity')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectoral_goals', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('bond_outcomes', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('nlgas_pillars', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
