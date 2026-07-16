<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_report_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('indicator_reports')->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('approval_workflow_stages')->nullOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // submitted|approved|declined|returned|resubmitted|published
            $table->text('reason')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamps();
            $table->index(['report_id', 'acted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_report_approvals');
    }
};
