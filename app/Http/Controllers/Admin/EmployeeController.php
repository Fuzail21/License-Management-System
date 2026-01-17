<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Apply city scoping for managers
        $query = Employee::with(['department.division.city'])
            ->forManager();

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('employees.department_id', $request->department_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('employees.status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('employees.first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('employees.last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('employees.email', 'like', '%' . $request->search . '%')
                  ->orWhere('employees.employee_number', 'like', '%' . $request->search . '%');
            });
        }

        $employees = $query->orderBy('employees.created_at', 'desc')->paginate(15)->withQueryString();

        // Get departments for filter dropdown (scoped for managers)
        $departments = Department::with('division.city')
            ->forManager()
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Employee::class);

        // Get departments that the user can access
        $departments = Department::with('division.city')
            ->forManager()
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.employees.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $this->authorize('view', $employee);

        $employee->load(['department.division.city', 'userLicenses.license.vendor']);

        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $this->authorize('update', $employee);

        // Get departments that the user can access
        $departments = Department::with('division.city')
            ->forManager()
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Bulk delete employees.
     */
    public function bulkDelete(Request $request)
    {
        $this->authorize('bulkDelete', Employee::class);

        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $employeeIds = $validated['employee_ids'];

        // Verify all employees are accessible to the user
        $employees = Employee::forManager()
            ->whereIn('id', $employeeIds)
            ->get();

        if ($employees->count() !== count($employeeIds)) {
            AuditLogService::logUnauthorizedAccess(
                'employee_bulk_delete_unauthorized',
                auth()->id(),
                [
                    'requested_ids' => $employeeIds,
                    'accessible_count' => $employees->count(),
                ]
            );

            return back()->with('error', 'Some employees are not accessible to you.');
        }

        Employee::whereIn('id', $employeeIds)->delete();

        AuditLogService::logBulkOperation(
            'bulk_delete',
            'employee',
            $employeeIds,
            auth()->id()
        );

        $count = count($employeeIds);
        return back()->with('success', "{$count} employees deleted successfully.");
    }

    /**
     * Export employees data.
     */
    public function export(Request $request)
    {
        $this->authorize('export', Employee::class);

        $filters = $request->only(['department_id', 'status', 'date_from', 'date_to']);

        // Apply city scoping for managers
        $query = Employee::with(['department.division.city'])
            ->forManager();

        // Apply filters
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('hire_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('hire_date', '<=', $filters['date_to']);
        }

        $employees = $query->get();

        // Log the export
        AuditLogService::logDataExport(
            'employees',
            $filters,
            auth()->id()
        );

        // Generate CSV
        $filename = 'employees_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Employee Number',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Job Title',
                'Hire Date',
                'Status',
                'Department',
                'Division',
                'City',
            ]);

            // CSV rows
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->employee_number,
                    $employee->first_name,
                    $employee->last_name,
                    $employee->email,
                    $employee->phone,
                    $employee->job_title,
                    $employee->hire_date,
                    $employee->status,
                    $employee->department->name ?? 'N/A',
                    $employee->department->division->name ?? 'N/A',
                    $employee->department->division->city->name ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
