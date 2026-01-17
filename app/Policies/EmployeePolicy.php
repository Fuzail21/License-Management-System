<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    /**
     * Admin has full unrestricted access.
     * Managers have city-scoped access through department -> division -> city hierarchy.
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
     * Validate department_id for creation.
     */
    public function createInDepartment(User $user, int $departmentId): bool
    {
        if ($user->isManager()) {
            $department = Department::with('division')->find($departmentId);
            if (!$department || !$department->division) {
                return false;
            }

            return $this->userManagesCity($user, $department->division->city_id);
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
     * Validate department_id for update (when changing department).
     */
    public function updateToDepartment(User $user, Employee $employee, int $newDepartmentId): bool
    {
        if ($user->isManager()) {
            $currentDepartment = $employee->department()->with('division')->first();
            $newDepartment = Department::with('division')->find($newDepartmentId);

            if (!$currentDepartment || !$currentDepartment->division || !$newDepartment || !$newDepartment->division) {
                return false;
            }

            return $this->userManagesCity($user, $currentDepartment->division->city_id)
                && $this->userManagesCity($user, $newDepartment->division->city_id);
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
     * Check if user manages the employee through department -> division -> city hierarchy.
     */
    private function userManagesEmployee(User $user, Employee $employee): bool
    {
        $department = $employee->department;
        if (!$department) {
            return false;
        }

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
