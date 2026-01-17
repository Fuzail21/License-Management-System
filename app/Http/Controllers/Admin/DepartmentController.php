<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Division;
use App\Models\Department;
use App\Services\AuditLogService;
use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Apply city scoping for managers
        $query = Department::with(['division.city', 'employees'])
            ->forManager()
            ->withCount('employees');

        // Filter by division
        if ($request->filled('division_id')) {
            $query->where('departments.division_id', $request->division_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('departments.status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('departments.name', 'like', '%' . $request->search . '%');
        }

        $departments = $query->orderBy('departments.created_at', 'desc')->paginate(15)->withQueryString();

        // Get divisions for filter dropdown (scoped for managers)
        $divisions = Division::forManager()->active()->orderBy('name')->get();

        return view('admin.departments.index', compact('departments', 'divisions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Department::class);

        // Get divisions that the user can access
        $divisions = Division::forManager()->active()->orderBy('name')->get();

        return view('admin.departments.create', compact('divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
        $validated = $request->validated();
        $divisionIds = $validated['division_ids'];
        $createdCount = 0;

        foreach ($divisionIds as $divisionId) {
            Department::create([
                'division_id' => $divisionId,
                'name' => $validated['name'],
                'status' => $validated['status'],
            ]);
            $createdCount++;
        }

        $message = $createdCount === 1
            ? 'Department created successfully.'
            : "Department created successfully in {$createdCount} divisions.";

        return redirect()->route('admin.departments.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $this->authorize('view', $department);

        $department->load(['division.city', 'employees']);
        $stats = $this->departmentService->getStatistics($department);

        return view('admin.departments.show', compact('department', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $this->authorize('update', $department);

        // Get divisions that the user can access
        $divisions = Division::forManager()->active()->orderBy('name')->get();

        return view('admin.departments.edit', compact('department', 'divisions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);

        $canDelete = $this->departmentService->canDelete($department);

        if (!$canDelete['can_delete']) {
            // Log the attempt to delete department with children
            AuditLogService::logUnauthorizedAccess(
                'department_delete_with_children',
                auth()->id(),
                [
                    'department_id' => $department->id,
                    'department_name' => $department->name,
                    'employees_count' => $canDelete['employees_count'],
                ]
            );

            return back()->with('error', $canDelete['message']);
        }

        $result = $this->departmentService->delete($department);

        if ($result['success']) {
            return redirect()->route('admin.departments.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Force cascade delete (admin only).
     */
    public function forceDelete(Department $department)
    {
        $this->authorize('forceDelete', $department);

        $result = $this->departmentService->forceCascadeDelete($department);

        if ($result['success']) {
            return redirect()->route('admin.departments.index')
                ->with('success', $result['message'])
                ->with('warning', 'Cascade delete removed ' . $result['deleted']['employees'] . ' employees.');
        }

        return back()->with('error', $result['message']);
    }
}
