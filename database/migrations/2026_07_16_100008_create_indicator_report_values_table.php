<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_report_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('indicator_reports')->cascadeOnDelete();
            $table->foreignId('disagregation_item_id')->nullable()->constrained('disagregation_items')->cascadeOnDelete();
            $table->decimal('value', 20, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_report_values');
    }
};
