<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // month|quarter|year
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('period_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
            $table->unique(['type', 'year', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_periods');
    }
};
