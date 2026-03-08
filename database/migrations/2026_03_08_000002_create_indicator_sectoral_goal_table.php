<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_sectoral_goal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained()->onDelete('cascade');
            $table->foreignId('sectoral_goal_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['indicator_id', 'sectoral_goal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_sectoral_goal');
    }
};
