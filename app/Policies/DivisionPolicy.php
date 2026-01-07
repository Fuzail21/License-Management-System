<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\Division;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DivisionPolicy
{
    /**
     * Admin has full unrestricted access.
     * Managers have city-scoped access through department hierarchy.
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
    public function view(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDivision($user, $division);
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
     * Validate department_id for creation.
     */
    public function createInDepartment(User $user, int $departmentId): bool
    {
        if ($user->isManager()) {
            $department = Department::find($departmentId);
            if (!$department) {
                return false;
            }

            return $this->userManagesCity($user, $department->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDivision($user, $division);
        }

        return false;
    }

    /**
     * Validate department_id for update (when changing department).
     */
    public function updateToDepartment(User $user, Division $division, int $newDepartmentId): bool
    {
        if ($user->isManager()) {
            $currentDepartment = $division->department;
            $newDepartment = Department::find($newDepartmentId);

            if (!$currentDepartment || !$newDepartment) {
                return false;
            }

            return $this->userManagesCity($user, $currentDepartment->city_id)
                && $this->userManagesCity($user, $newDepartment->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDivision($user, $division);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDivision($user, $division);
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDivision($user, $division);
        }

        return false;
    }

    /**
     * Check if user manages the division through its department's city.
     */
    private function userManagesDivision(User $user, Division $division): bool
    {
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
