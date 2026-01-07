<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CityService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    protected $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;

        // Note: Admin middleware is applied at route level (routes/web.php)
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = City::withCount(['departments', 'managers']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('cities.status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cities.name', 'like', '%' . $request->search . '%')
                    ->orWhere('cities.code', 'like', '%' . $request->search . '%');
            });
        }

        $cities = $query->orderBy('cities.created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.cities.index', compact('cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', City::class);

        return view('admin.cities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->validated());

        return redirect()->route('admin.cities.index')
            ->with('success', 'City created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(City $city)
    {
        $this->authorize('view', $city);

        $city->load(['departments.divisions', 'managers']);
        $stats = $this->cityService->getStatistics($city);

        return view('admin.cities.show', compact('city', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(City $city)
    {
        $this->authorize('update', $city);

        return view('admin.cities.edit', compact('city'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());

        return redirect()->route('admin.cities.index')
            ->with('success', 'City updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city)
    {
        $this->authorize('delete', $city);

        $canDelete = $this->cityService->canDelete($city);

        if (!$canDelete['can_delete']) {
            // Log the attempt to delete city with children
            AuditLogService::logUnauthorizedAccess(
                'city_delete_with_children',
                auth()->id(),
                [
                    'city_id' => $city->id,
                    'city_name' => $city->name,
                    'departments_count' => $canDelete['departments_count'],
                    'managers_count' => $canDelete['managers_count'],
                ]
            );

            return back()->with('error', $canDelete['message']);
        }

        $result = $this->cityService->delete($city);

        if ($result['success']) {
            return redirect()->route('admin.cities.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Force cascade delete (removes all departments, divisions, employees).
     */
    public function forceDelete(City $city)
    {
        $this->authorize('forceDelete', $city);

        $result = $this->cityService->forceCascadeDelete($city);

        if ($result['success']) {
            return redirect()->route('admin.cities.index')
                ->with('success', $result['message'])
                ->with('warning', "Cascade delete removed {$result['deleted']['departments']} departments, {$result['deleted']['divisions']} divisions, and {$result['deleted']['employees']} employees.");
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Archive a city (set status to inactive).
     */
    public function archive(City $city)
    {
        $this->authorize('update', $city);

        $result = $this->cityService->archive($city);

        if ($result['success']) {
            AuditLogService::logCityArchival(
                $city->id,
                'archived',
                auth()->id()
            );
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Restore a city (set status to active).
     */
    public function restore(City $city)
    {
        $this->authorize('update', $city);

        $result = $this->cityService->restore($city);

        if ($result['success']) {
            AuditLogService::logCityArchival(
                $city->id,
                'restored',
                auth()->id()
            );
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Show city manager assignment page.
     */
    public function managers(City $city)
    {
        $this->authorize('manageManagers', $city);

        $city->load('managers.role');

        // Get available managers (users with manager role not already assigned)
        $assignedManagerIds = $city->managers->pluck('id')->toArray();
        $availableManagers = User::whereHas('role', function($query) {
            $query->where('name', 'Manager');
        })
        ->whereNotIn('id', $assignedManagerIds)
        ->where('status', 'active')
        ->orderBy('name')
        ->get();

        return view('admin.cities.managers', compact('city', 'availableManagers'));
    }

    /**
     * Assign a manager to a city.
     */
    public function assignManager(Request $request, City $city)
    {
        $this->authorize('manageManagers', $city);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Verify the user is a manager
        $user = User::findOrFail($validated['user_id']);

        if (!$user->isManager()) {
            return back()->with('error', 'Selected user is not a manager.');
        }

        // Check if already assigned
        if ($city->managers()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'This manager is already assigned to this city.');
        }

        $city->managers()->attach($user->id, [
            'assigned_at' => now(),
        ]);

        AuditLogService::logCityManagerAssignment(
            $city->id,
            $user->id,
            auth()->id()
        );

        return back()->with('success', 'Manager assigned successfully.');
    }

    /**
     * Remove a manager from a city.
     */
    public function removeManager(City $city, User $manager)
    {
        $this->authorize('manageManagers', $city);

        // Verify the manager is assigned to this city
        if (!$city->managers()->where('user_id', $manager->id)->exists()) {
            return back()->with('error', 'This manager is not assigned to this city.');
        }

        $city->managers()->detach($manager->id);

        AuditLogService::logCityManagerRemoval(
            $city->id,
            $manager->id,
            auth()->id()
        );

        return back()->with('success', 'Manager removed successfully.');
    }
}
