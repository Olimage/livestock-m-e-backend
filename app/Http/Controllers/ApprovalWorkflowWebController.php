<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\Role;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovalWorkflowWebController extends Controller
{
    public function __construct(private readonly WorkflowService $workflows) {}

    public function index(Request $request)
    {
        $this->guard($request);

        return Inertia::render('IndicatorReporting/Workflows/Index', [
            'workflows' => ApprovalWorkflow::with(['stages:id,workflow_id,name,position,approval_mode', 'departments:id,name'])
                ->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->guard($request);

        return Inertia::render('IndicatorReporting/Workflows/Builder', $this->builderProps(null));
    }

    public function edit(Request $request, ApprovalWorkflow $workflow)
    {
        $this->guard($request);

        return Inertia::render('IndicatorReporting/Workflows/Builder',
            $this->builderProps($workflow->load('stages', 'departments')));
    }

    public function store(Request $request)
    {
        $this->guard($request);
        $data = $this->validated($request);

        try {
            $this->workflows->create($data);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('indicator-reporting.workflows.index')->with('success', 'Workflow created.');
    }

    public function update(Request $request, ApprovalWorkflow $workflow)
    {
        $this->guard($request);
        $data = $this->validated($request);

        try {
            $this->workflows->update($workflow, $data);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('indicator-reporting.workflows.index')->with('success', 'Workflow updated.');
    }

    public function destroy(Request $request, ApprovalWorkflow $workflow)
    {
        $this->guard($request);
        $workflow->delete();

        return back()->with('success', 'Workflow deleted.');
    }

    private function guard(Request $request): void
    {
        abort_unless($request->user()->is_admin || $request->user()->hasPermission('manage-approval-workflows'), 403);
    }

    private function builderProps(?ApprovalWorkflow $workflow): array
    {
        return [
            'workflow' => $workflow,
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
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
