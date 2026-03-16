<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('indicators')->onDelete('cascade');
            $table->foreignId('linked_indicator_id')->constrained('indicators')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['indicator_id', 'linked_indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_links');
    }
};
