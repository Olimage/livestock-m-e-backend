<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Department;

class DepartmentController extends Controller
{
    
    public function getDepartments(Request $request)
    {
        try {
            // Get only top-level departments (parent_id is null) with all descendants
            $departments = Department::whereNull('parent_id')
                ->with('descendants')
                ->get();

            // Recursively hide fields from departments and all descendants
            $departments = $departments->map(function ($department) {
                return $this->hideFieldsRecursively($department);
            });

            return response()->json([
                'status' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recursively hide fields from department and all its descendants
     */
    private function hideFieldsRecursively($department)
    {
        $department->makeHidden(['id', 'created_at', 'updated_at']);

        if ($department->descendants && $department->descendants->count() > 0) {
            $department->descendants = $department->descendants->map(function ($child) {
                return $this->hideFieldsRecursively($child);
            });
        }

        return $department;
    }
}
