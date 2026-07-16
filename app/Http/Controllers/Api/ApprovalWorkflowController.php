<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class ApprovalWorkflowController extends Controller
{
    public function __construct(private readonly WorkflowService $workflows) {}

    public function index(Request $request)
    {
        $this->authorizeManage($request);

        return response()->json([
            'success' => true,
            'data' => ApprovalWorkflow::with('stages', 'departments')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage($request);
        $data = $this->validated($request);

        try {
            $wf = $this->workflows->create($data);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Workflow created.', 'data' => $wf], 201);
    }

    public function show(Request $request, ApprovalWorkflow $workflow)
    {
        $this->authorizeManage($request);

        return response()->json(['success' => true, 'data' => $workflow->load('stages.users', 'departments')]);
    }

    public function update(Request $request, ApprovalWorkflow $workflow)
    {
        $this->authorizeManage($request);
        try {
            $wf = $this->workflows->update($workflow, $this->validated($request, false));
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Workflow updated.', 'data' => $wf]);
    }

    public function destroy(Request $request, ApprovalWorkflow $workflow)
    {
        $this->authorizeManage($request);
        $workflow->delete();

        return response()->json(['success' => true, 'message' => 'Workflow deleted.']);
    }

    public function assignDepartments(Request $request, ApprovalWorkflow $workflow)
    {
        $this->authorizeManage($request);
        $data = $request->validate(['department_ids' => 'array', 'department_ids.*' => 'integer|exists:departments,id']);
        try {
            $this->workflows->assignDepartments($workflow, $data['department_ids'] ?? []);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Departments assigned.', 'data' => $workflow->load('departments')]);
    }

    private function authorizeManage(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && $user->hasPermission('manage-approval-workflows'), 403, 'Forbidden.');
    }

    private function validated(Request $request, bool $requireName = true): array
    {
        return $request->validate([
            'name' => ($requireName ? 'required' : 'sometimes').'|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'initiator_role_id' => 'nullable|integer|exists:roles,id',
            'resubmit_behavior' => 'in:from_start,from_declined_stage',
            'stages' => 'array',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.assignment_type' => 'in:role,users',
            'stages.*.role_id' => 'nullable|integer|exists:roles,id',
            'stages.*.approval_mode' => 'in:any,all',
            'stages.*.user_ids' => 'array',
            'stages.*.user_ids.*' => 'integer|exists:users,id',
            'department_ids' => 'array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);
    }
}
