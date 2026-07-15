<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indicator_forms', function (Blueprint $table) {
            $table->renameColumn('indicator_slug', 'indicator_code');
        });
    }

    public function down(): void
    {
        Schema::table('indicator_forms', function (Blueprint $table) {
            $table->renameColumn('indicator_code', 'indicator_slug');
        });
    }
};
