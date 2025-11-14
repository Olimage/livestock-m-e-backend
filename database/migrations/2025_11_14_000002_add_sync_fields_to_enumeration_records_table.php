<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enumeration_records', function (Blueprint $table) {
            $table->unsignedInteger('sync_attempts')->default(0)->after('sync_status');
            $table->timestamp('last_sync_at')->nullable()->after('sync_attempts');
            $table->text('sync_error')->nullable()->after('last_sync_at');
        });
    }

    public function down(): void
    {
        Schema::table('enumeration_records', function (Blueprint $table) {
            $table->dropColumn(['sync_attempts','last_sync_at','sync_error']);
        });
    }
};
