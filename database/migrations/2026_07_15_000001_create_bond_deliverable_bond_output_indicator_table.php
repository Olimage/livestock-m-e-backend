<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bond_deliverable_bond_output_indicator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bond_deliverable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bond_output_indicator_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['bond_deliverable_id', 'bond_output_indicator_id'], 'bd_boi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bond_deliverable_bond_output_indicator');
    }
};
