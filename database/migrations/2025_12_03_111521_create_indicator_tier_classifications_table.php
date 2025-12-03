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
        Schema::create('indicator_tier_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('tier');
            $table->string('level');
            $table->string('measurement_frequency');
            $table->string('attribution');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicator_tier_classifications');
    }
};
