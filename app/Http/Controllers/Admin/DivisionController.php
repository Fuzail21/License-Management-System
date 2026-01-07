<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;
use App\Models\Department;
use App\Models\Division;
use App\Services\AuditLogService;
use App\Services\DivisionService;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    protected $divisionService;

    public function __construct(DivisionService $divisionService)
    {
        $this->divisionService = $divisionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Apply city scoping for managers
        $query = Division::with(['department.city', 'employees'])
            ->forManager()
            ->withCount('employees');

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('divisions.department_id', $request->department_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('divisions.status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('divisions.name', 'like', '%' . $request->search . '%');
        }

        $divisions = $query->orderBy('divisions.created_at', 'desc')->paginate(15)->withQueryString();

        // Get departments for filter dropdown (scoped for managers)
        $departments = Department::forManager()->active()->orderBy('name')->get();

        return view('admin.divisions.index', compact('divisions', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Division::class);

        // Get departments that the user can access
        $departments = Department::forManager()->active()->orderBy('name')->get();

        return view('admin.divisions.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDivisionRequest $request)
    {
        $division = Division::create($request->validated());

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {
        $this->authorize('view', $division);

        $division->load(['department.city', 'employees']);
        $stats = $this->divisionService->getStatistics($division);

        return view('admin.divisions.show', compact('division', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Division $division)
    {
        $this->authorize('update', $division);

        // Get departments that the user can access
        $departments = Department::forManager()->active()->orderBy('name')->get();

        return view('admin.divisions.edit', compact('division', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDivisionRequest $request, Division $division)
    {
        $division->update($request->validated());

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $this->authorize('delete', $division);

        $canDelete = $this->divisionService->canDelete($division);

        if (!$canDelete['can_delete']) {
            // Log the attempt to delete division with children
            AuditLogService::logUnauthorizedAccess(
                'division_delete_with_children',
                auth()->id(),
                [
                    'division_id' => $division->id,
                    'division_name' => $division->name,
                    'employees_count' => $canDelete['employees_count'],
                ]
            );

            return back()->with('error', $canDelete['message']);
        }

        $result = $this->divisionService->delete($division);

        if ($result['success']) {
            return redirect()->route('admin.divisions.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Force cascade delete (admin only).
     */
    public function forceDelete(Division $division)
    {
        $this->authorize('forceDelete', $division);

        $result = $this->divisionService->forceCascadeDelete($division);

        if ($result['success']) {
            return redirect()->route('admin.divisions.index')
                ->with('success', $result['message'])
                ->with('warning', 'Cascade delete removed ' . $result['deleted']['employees'] . ' employees.');
        }

        return back()->with('error', $result['message']);
    }
}
