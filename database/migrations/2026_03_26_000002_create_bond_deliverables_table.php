<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bond_deliverables', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('deliverable');
            $table->timestamps();
        });

        Schema::create('bond_deliverable_indicator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bond_deliverable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained()->cascadeOnDelete();
            $table->unique(['bond_deliverable_id', 'indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bond_deliverable_indicator');
        Schema::dropIfExists('bond_deliverables');
    }
};
