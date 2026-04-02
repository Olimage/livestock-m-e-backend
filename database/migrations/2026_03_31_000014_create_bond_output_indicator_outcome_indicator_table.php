<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bond_output_indicator_outcome_indicator', function (Blueprint $table) {
            $table->foreignId('bond_output_indicator_id')->constrained('bond_output_indicators')->onDelete('cascade');
            $table->foreignId('outcome_indicator_id')->constrained('outcome_indicators')->onDelete('cascade');
            $table->primary(['bond_output_indicator_id', 'outcome_indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bond_output_indicator_outcome_indicator');
    }
};
