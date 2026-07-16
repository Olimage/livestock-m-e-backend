<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalWorkflow extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'initiator_role_id', 'resubmit_behavior'];

    protected $casts = ['is_active' => 'boolean'];

    public function stages()
    {
        return $this->hasMany(ApprovalWorkflowStage::class, 'workflow_id')->orderBy('position');
    }

    public function activeStages()
    {
        return $this->stages()->where('is_active', true);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'approval_workflow_department', 'workflow_id', 'department_id');
    }

    public function initiatorRole()
    {
        return $this->belongsTo(Role::class, 'initiator_role_id');
    }
}
