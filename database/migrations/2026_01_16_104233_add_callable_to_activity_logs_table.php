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
            $table->string('callable_type')->nullable()->after('user_id');
            $table->unsignedBigInteger('callable_id')->nullable()->after('callable_type');
            $table->string('db_action')->nullable()->after('callable_id'); // created, updated, deleted, etc.
            
            $table->index(['callable_type', 'callable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['callable_type', 'callable_id']);
            $table->dropColumn(['callable_type', 'callable_id', 'db_action']);
        });
    }
};
