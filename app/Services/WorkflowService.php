<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkflowService
{
    public function create(array $data): ApprovalWorkflow
    {
        return DB::transaction(function () use ($data) {
            $wf = ApprovalWorkflow::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'initiator_role_id' => $data['initiator_role_id'] ?? null,
                'resubmit_behavior' => $data['resubmit_behavior'] ?? 'from_start',
            ]);

            $this->syncStages($wf, $data['stages'] ?? []);
            $this->assignDepartments($wf, $data['department_ids'] ?? []);

            return $wf->load('stages', 'departments');
        });
    }

    public function update(ApprovalWorkflow $wf, array $data): ApprovalWorkflow
    {
        return DB::transaction(function () use ($wf, $data) {
            $wf->fill(array_filter([
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'resubmit_behavior' => $data['resubmit_behavior'] ?? null,
                'initiator_role_id' => $data['initiator_role_id'] ?? null,
            ], fn ($v) => $v !== null));

            if (array_key_exists('is_active', $data)) {
                $wf->is_active = (bool) $data['is_active'];
            }
            $wf->save();

            if (array_key_exists('stages', $data)) {
                $this->syncStages($wf, $data['stages']);
            }
            if (array_key_exists('department_ids', $data)) {
                $this->assignDepartments($wf, $data['department_ids']);
            }

            return $wf->load('stages', 'departments');
        });
    }

    public function syncStages(ApprovalWorkflow $wf, array $stages): void
    {
        $wf->stages()->delete();
        $position = 1;
        foreach ($stages as $stage) {
            $wf->stages()->create([
                'name' => $stage['name'],
                'position' => $position++,
                'assignment_type' => $stage['assignment_type'] ?? 'role',
                'role_id' => $stage['role_id'] ?? null,
                'approval_mode' => $stage['approval_mode'] ?? 'any',
                'is_active' => $stage['is_active'] ?? true,
            ])->users()->sync($stage['user_ids'] ?? []);
        }
    }

    public function assignDepartments(ApprovalWorkflow $wf, array $departmentIds): void
    {
        foreach ($departmentIds as $deptId) {
            $conflict = $this->workflowForDepartment((int) $deptId);
            if ($wf->is_active && $conflict && $conflict->id !== $wf->id) {
                throw new \DomainException("Department {$deptId} already has active workflow {$conflict->slug}.");
            }
        }
        $wf->departments()->sync($departmentIds);
    }

    public function workflowForDepartment(int $departmentId): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::where('is_active', true)
            ->whereHas('departments', fn ($q) => $q->where('departments.id', $departmentId))
            ->with('stages')
            ->first();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (ApprovalWorkflow::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
