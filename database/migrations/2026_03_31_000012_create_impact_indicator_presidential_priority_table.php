<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_indicator_presidential_priority', function (Blueprint $table) {
            $table->foreignId('impact_indicator_id')->constrained('impact_indicators')->onDelete('cascade');
            $table->foreignId('presidential_priority_id')->constrained('presidential_priorities')->onDelete('cascade');
            $table->primary(['impact_indicator_id', 'presidential_priority_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_indicator_presidential_priority');
    }
};
