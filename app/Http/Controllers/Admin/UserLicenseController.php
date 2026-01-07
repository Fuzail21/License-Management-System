<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\License;
use App\Models\UserLicense;
use Illuminate\Http\Request;

class UserLicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserLicense::with(['employee.division.department', 'license.vendor']);

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $userLicenses = $query->latest()->paginate(15);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        return view('admin.user-licenses.index', compact('userLicenses', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $licenses = License::with('vendor')->get();

        $selectedLicenseId = $request->get('license_id');

        return view('admin.user-licenses.create', compact('employees', 'licenses', 'selectedLicenseId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'license_id' => 'required|exists:licenses,id',
            'assigned_date' => 'required|date',
            'status' => 'required|in:active,expired,suspended',
        ]);

        // Add assigned_date as current date
        $validated['assigned_date'] = now();

        UserLicense::create($validated);

        return redirect()->route('admin.user-licenses.index')
            ->with('success', 'License assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UserLicense $userLicense)
    {
        $userLicense->load(['employee.division.department', 'license.vendor']);

        return view('admin.user-licenses.show', compact('userLicense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserLicense $userLicense)
    {
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $licenses = License::with('vendor')->get();

        return view('admin.user-licenses.edit', compact('userLicense', 'employees', 'licenses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserLicense $userLicense)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'license_id' => 'required|exists:licenses,id',
            'assigned_date' => 'required|date',
            'status' => 'required|in:active,expired,suspended',
        ]);

        $userLicense->update($validated);

        return redirect()->route('admin.user-licenses.index')
            ->with('success', 'License assignment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserLicense $userLicense)
    {
        $userLicense->delete();

        return redirect()->route('admin.user-licenses.index')
            ->with('success', 'License assignment deleted successfully.');
    }
}
