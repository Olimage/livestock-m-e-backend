<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_stage_user', function (Blueprint $table) {
            $table->foreignId('stage_id')->constrained('approval_workflow_stages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['stage_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_stage_user');
    }
};
