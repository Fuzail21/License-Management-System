<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\User;
use App\Models\UserLicense;
use Illuminate\Http\Request;

class UserLicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserLicense::with(['user.department', 'license.vendor']);

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $userLicenses = $query->latest()->paginate(15);
        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('admin.user-licenses.index', compact('userLicenses', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        $licenses = License::with('vendor')->get();

        $selectedLicenseId = $request->get('license_id');

        return view('admin.user-licenses.create', compact('users', 'licenses', 'selectedLicenseId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_id' => 'required|exists:licenses,id',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'renewal_cycle' => 'required|in:monthly,quarterly,yearly,perpetual',
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
        $userLicense->load(['user.department', 'license.vendor', 'renewals']);

        return view('admin.user-licenses.show', compact('userLicense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserLicense $userLicense)
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        $licenses = License::with('vendor')->get();

        return view('admin.user-licenses.edit', compact('userLicense', 'users', 'licenses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserLicense $userLicense)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_id' => 'required|exists:licenses,id',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'renewal_cycle' => 'required|in:monthly,quarterly,yearly,perpetual',
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
