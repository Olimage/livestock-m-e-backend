<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enumeration_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('enumerator_name');
            $table->string('form_type'); // household | market | commercial_farm
            $table->string('sync_status')->default('pending'); // pending | synced | failed
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_id')->nullable();
            $table->json('payload');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['form_type', 'sync_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enumeration_records');
    }
};
