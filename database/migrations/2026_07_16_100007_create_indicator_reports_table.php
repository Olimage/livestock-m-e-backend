<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('indicator_type');
            $table->unsignedBigInteger('indicator_id');
            $table->string('indicator_code')->nullable();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('reporting_period_id')->constrained('reporting_periods')->cascadeOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained('approval_workflows')->nullOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('approval_workflow_stages')->nullOnDelete();
            $table->decimal('target_value', 20, 4)->nullable();
            $table->decimal('actual_value', 20, 4)->nullable();
            $table->text('narrative')->nullable();
            $table->string('status')->default('draft'); // draft|pending|returned|approved
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['indicator_type', 'indicator_id']);
            $table->index(['status', 'current_stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_reports');
    }
};
