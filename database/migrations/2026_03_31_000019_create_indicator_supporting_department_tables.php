<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('output_indicator_supporting_department', function (Blueprint $table) {
            $table->foreignId('output_indicator_id')->constrained('output_indicators')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->primary(['output_indicator_id', 'department_id']);
        });

        Schema::create('outcome_indicator_supporting_department', function (Blueprint $table) {
            $table->foreignId('outcome_indicator_id')->constrained('outcome_indicators')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->primary(['outcome_indicator_id', 'department_id']);
        });

        Schema::create('impact_indicator_supporting_department', function (Blueprint $table) {
            $table->foreignId('impact_indicator_id')->constrained('impact_indicators')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->primary(['impact_indicator_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('output_indicator_supporting_department');
        Schema::dropIfExists('outcome_indicator_supporting_department');
        Schema::dropIfExists('impact_indicator_supporting_department');
    }
};
