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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('method')->nullable()->after('action');
            $table->text('url')->nullable()->after('method');
            $table->text('user_agent')->nullable()->after('device');
            $table->text('description')->nullable()->after('action');
            $table->json('properties')->nullable()->after('url');
            $table->integer('status_code')->nullable()->after('properties');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['method', 'url', 'user_agent', 'description', 'properties', 'status_code']);
        });
    }
};
