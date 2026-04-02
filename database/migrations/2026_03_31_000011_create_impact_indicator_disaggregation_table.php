<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_indicator_disaggregation', function (Blueprint $table) {
            $table->foreignId('impact_indicator_id')->constrained('impact_indicators')->onDelete('cascade');
            $table->foreignId('disagregation_item_id')->constrained('disagregation_items')->onDelete('cascade');
            $table->primary(['impact_indicator_id', 'disagregation_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_indicator_disaggregation');
    }
};
