<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\City;
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
        $query = Department::with(['city', 'divisions'])
            ->forManager()
            ->withCount('divisions');

        // Filter by city
        if ($request->filled('city_id')) {
            $query->where('departments.city_id', $request->city_id);
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

        // Get cities for filter dropdown (scoped for managers)
        $cities = City::forManager()->active()->orderBy('name')->get();

        return view('admin.departments.index', compact('departments', 'cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Department::class);

        // Get cities that the user can access
        $cities = City::forManager()->active()->orderBy('name')->get();

        return view('admin.departments.create', compact('cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
        $department = Department::create($request->validated());

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $this->authorize('view', $department);

        $department->load([
            'city',
            'divisions.employees' => fn ($q) =>
                $q->where('employees.status', 'active')
        ]);

        $stats = $this->departmentService->getStatistics($department);

        return view('admin.departments.show', compact('department', 'stats'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $this->authorize('update', $department);

        // Get cities that the user can access
        $cities = City::forManager()->active()->orderBy('name')->get();

        return view('admin.departments.edit', compact('department', 'cities'));
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
                    'divisions_count' => $canDelete['divisions_count'],
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
     * Toggle department status.
     */
    public function toggleStatus(Department $department)
    {
        $this->authorize('update', $department);

        $oldStatus = $department->status->value;
        $newStatus = $department->isActive() ? 'inactive' : 'active';

        $department->status = $newStatus;
        $department->save();

        return redirect()->back()
            ->with('success', 'Department status updated successfully.');
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
                ->with('warning', 'Cascade delete removed ' . $result['deleted']['divisions'] . ' divisions and ' . $result['deleted']['employees'] . ' employees.');
        }

        return back()->with('error', $result['message']);
    }
}
