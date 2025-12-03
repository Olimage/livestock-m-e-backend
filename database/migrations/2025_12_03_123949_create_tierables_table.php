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
        Schema::create('tierables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tier_id')->constrained()->onDelete('cascade');
            $table->morphs('tierable');
            $table->timestamps();

            $table->unique(['tier_id', 'tierable_id', 'tierable_type'], 'tierables_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tierables');
    }
};
