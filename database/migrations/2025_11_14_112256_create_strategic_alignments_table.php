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
        Schema::create('strategic_alignments', function (Blueprint $table) {
            $table->id();
                        $table->foreignId('presidential_priority_id')->constrained()->onDelete('cascade');
            $table->foreignId('sectoral_goal_id')->constrained()->onDelete('cascade');
            $table->foreignId('bond_outcome_id')->constrained()->onDelete('cascade');
            $table->foreignId('nlgas_pillar_id')->constrained()->onDelete('cascade');
            $table->text('alignment_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategic_alignments');
    }
};
