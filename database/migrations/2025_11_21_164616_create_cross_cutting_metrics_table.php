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
        Schema::create('cross_cutting_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('measurement_unit')->nullable();
            $table->decimal('baseline_value', 15, 2)->nullable();
            $table->integer('baseline_year')->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->integer('target_year')->nullable();
            $table->string('data_source')->nullable();
            $table->string('collection_frequency')->nullable();
            $table->string('responsible_entity')->nullable();
            $table->enum('metric_category', ['gender', 'youth', 'innovation', 'climate'])->default('gender');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cross_cutting_metrics');
    }
};
