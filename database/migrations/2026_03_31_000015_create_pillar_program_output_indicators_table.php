<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pillar_program_output_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PPOI prefix
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pillar_program_output_indicators');
    }
};
