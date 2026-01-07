<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('department');

        if ($request->has('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('designation', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $departments = Department::where('status', 'active')->get();

        return view('admin.users.index', compact('users', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::where('status', 'active')->get();
        $roles = Role::orderBy('name')->get();
        $managerRoleId = Role::where('name', 'Manager')->value('id');

        return view('admin.users.create', compact('departments', 'roles', 'managerRoleId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        // Enforce can_create_license logic (security)
        $managerRole = Role::where('name', 'Manager')->first();

        if ($request->role_id == $managerRole->id) {
            // Only managers can have license create access
            $data['can_create_license'] = $request->boolean('can_create_license');
        } else {
            // Force false for non-managers (security enforcement)
            $data['can_create_license'] = false;
        }

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['department', 'userLicenses.license.vendor']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $departments = Department::where('status', 'active')->get();
        $roles = Role::orderBy('name')->get();
        $managerRoleId = Role::where('name', 'Manager')->value('id');

        return view('admin.users.edit', compact('user', 'departments', 'roles', 'managerRoleId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Enforce can_create_license logic (security)
        $managerRole = Role::where('name', 'Manager')->first();
        $oldCanCreateLicense = $user->can_create_license;

        if ($request->role_id == $managerRole->id) {
            // Only managers can have license create access
            $data['can_create_license'] = $request->boolean('can_create_license');

            // Warn if disabling access while pending licenses exist
            if ($oldCanCreateLicense && !$data['can_create_license']) {
                $pendingCount = $user->getPendingLicensesCount();
                if ($pendingCount > 0) {
                    session()->flash('warning', "This manager has {$pendingCount} pending licenses. Disabling access will not affect existing pending requests.");
                }
            }
        } else {
            // Force false for non-managers (security enforcement)
            $data['can_create_license'] = false;

            // Log if role changed from manager
            if ($user->isDirty('role_id') && $user->isManager()) {
                \Log::info('Revoked license create access due to role change', [
                    'user_id' => $user->id,
                    'old_role_id' => $user->getOriginal('role_id'),
                    'new_role_id' => $request->role_id,
                ]);
            }
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
