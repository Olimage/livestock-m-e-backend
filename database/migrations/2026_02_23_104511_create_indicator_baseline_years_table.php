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
        Schema::create('indicator_baseline_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indicator_id')->constrained()->onDelete('cascade');
            $table->integer('baseline_year')->nullable();
            $table->integer('target_year')->nullable();
            $table->integer('baseline');
            $table->integer('target');
            $table->integer('actual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_baseline_years');
    }
};
