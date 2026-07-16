<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('position');
            $table->string('assignment_type')->default('role'); // role|users
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->string('approval_mode')->default('any'); // any|all
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['workflow_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_stages');
    }
};
