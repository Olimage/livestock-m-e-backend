<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflowStage extends Model
{
    protected $fillable = ['workflow_id', 'name', 'position', 'assignment_type', 'role_id', 'approval_mode', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'position' => 'integer'];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'approval_workflow_stage_user', 'stage_id', 'user_id');
    }
}
