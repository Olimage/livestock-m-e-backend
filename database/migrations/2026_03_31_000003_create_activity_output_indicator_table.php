<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_output_indicator', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->foreignId('output_indicator_id')->constrained('output_indicators')->onDelete('cascade');
            $table->primary(['activity_id', 'output_indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_output_indicator');
    }
};
