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
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('indicator_type', ['outcome', 'output', 'impact'])->default('output');
            $table->string('measurement_unit')->nullable();
            $table->decimal('baseline_value', 15, 2)->nullable();
            $table->integer('baseline_year')->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->integer('target_year')->nullable();
            $table->string('data_source')->nullable();
            $table->string('collection_frequency')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};
