<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    /**
     * Admin has full unrestricted access.
     * Managers have city-scoped access through division hierarchy.
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
    public function view(User $user, Department $department): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDepartment($user, $department);
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
            $division = Division::find($divisionId);
            if (!$division) {
                return false;
            }

            return $this->userManagesCity($user, $division->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Department $department): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDepartment($user, $department);
        }

        return false;
    }

    /**
     * Validate division_id for update (when changing division).
     */
    public function updateToDivision(User $user, Department $department, int $newDivisionId): bool
    {
        if ($user->isManager()) {
            $currentDivision = $department->division;
            $newDivision = Division::find($newDivisionId);

            if (!$currentDivision || !$newDivision) {
                return false;
            }

            return $this->userManagesCity($user, $currentDivision->city_id)
                && $this->userManagesCity($user, $newDivision->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Department $department): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDepartment($user, $department);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Department $department): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDepartment($user, $department);
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Department $department): bool
    {
        if ($user->isManager()) {
            return $this->userManagesDepartment($user, $department);
        }

        return false;
    }

    /**
     * Check if user manages the department through its division's city.
     */
    private function userManagesDepartment(User $user, Department $department): bool
    {
        $division = $department->division;
        if (!$division) {
            return false;
        }

        return $this->userManagesCity($user, $division->city_id);
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
