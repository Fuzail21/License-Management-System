<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    /**
     * Admin has full unrestricted access.
     * Managers have city-scoped access through division -> department -> city hierarchy.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employee $employee): bool
    {
        if ($user->isManager()) {
            return $this->userManagesEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Validate division_id for creation.
     */
    public function createInDivision(User $user, int $divisionId): bool
    {
        if ($user->isManager()) {
            $division = Division::with('department')->find($divisionId);
            if (!$division || !$division->department) {
                return false;
            }

            return $this->userManagesCity($user, $division->department->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee $employee): bool
    {
        if ($user->isManager()) {
            return $this->userManagesEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Validate division_id for update (when changing division).
     */
    public function updateToDivision(User $user, Employee $employee, int $newDivisionId): bool
    {
        if ($user->isManager()) {
            $currentDivision = $employee->division()->with('department')->first();
            $newDivision = Division::with('department')->find($newDivisionId);

            if (!$currentDivision || !$currentDivision->department || !$newDivision || !$newDivision->department) {
                return false;
            }

            return $this->userManagesCity($user, $currentDivision->department->city_id)
                && $this->userManagesCity($user, $newDivision->department->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
        if ($user->isManager()) {
            return $this->userManagesEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employee $employee): bool
    {
        if ($user->isManager()) {
            return $this->userManagesEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        if ($user->isManager()) {
            return $this->userManagesEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Check if user manages the employee through division -> department -> city hierarchy.
     */
    private function userManagesEmployee(User $user, Employee $employee): bool
    {
        $division = $employee->division;
        if (!$division) {
            return false;
        }

        $department = $division->department;
        if (!$department) {
            return false;
        }

        return $this->userManagesCity($user, $department->city_id);
    }

    /**
     * Check if user manages the given city.
     */
    private function userManagesCity(User $user, ?int $cityId): bool
    {
        if ($cityId === null) {
            return false;
        }

        return $user->managedCities()->where('cities.id', $cityId)->exists();
    }
}
