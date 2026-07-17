<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('owner')->nullable()->after('title');
            $table->decimal('planned_amount', 20, 2)->nullable()->after('owner');
            $table->decimal('actual_amount', 20, 2)->nullable()->after('planned_amount');
            $table->string('coverage')->nullable()->after('actual_amount');
            $table->json('coverage_states')->nullable()->after('coverage');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['owner', 'planned_amount', 'actual_amount', 'coverage', 'coverage_states']);
        });
    }
};
