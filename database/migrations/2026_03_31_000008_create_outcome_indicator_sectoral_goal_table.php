<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outcome_indicator_sectoral_goal', function (Blueprint $table) {
            $table->foreignId('outcome_indicator_id')->constrained('outcome_indicators')->onDelete('cascade');
            $table->foreignId('sectoral_goal_id')->constrained('sectoral_goals')->onDelete('cascade');
            $table->primary(['outcome_indicator_id', 'sectoral_goal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outcome_indicator_sectoral_goal');
    }
};
