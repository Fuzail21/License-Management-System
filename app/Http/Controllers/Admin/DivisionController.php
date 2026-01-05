<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDivisionRequest;
use App\Http\Requests\Admin\UpdateDivisionRequest;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisions = Division::with('gm')
            ->withCount('departments')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.divisions.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDivisionRequest $request)
    {
        Division::create($request->validated());

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {
        $division->loadCount('departments');
        $division->load(['gm', 'departments' => function($query) {
            $query->limit(10);
        }]);

        return view('admin.divisions.show', compact('division'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Division $division)
    {
        $users = User::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.divisions.edit', compact('division', 'users'));
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
        $departmentCount = $division->departments()->count();

        if ($departmentCount > 0) {
            return redirect()->route('admin.divisions.index')
                ->with('error', "Cannot delete division. {$departmentCount} department(s) are assigned to this division.");
        }

        $division->delete();

        return redirect()->route('admin.divisions.index')
            ->with('success', 'Division deleted successfully.');
    }
}
