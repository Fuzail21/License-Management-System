<?php

namespace App\Services;

use App\Models\City;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\User;

class ValidationService
{
    /**
     * Verify that a city exists and the user has permission to access it.
     * Admins can access all cities. Managers can only access assigned cities.
     */
    public static function validateCityAccess(User $user, int $cityId): array
    {
        $city = City::find($cityId);

        if (!$city) {
            return [
                'valid' => false,
                'error' => 'The selected city does not exist.',
                'city' => null,
            ];
        }

        if ($user->isAdmin()) {
            return [
                'valid' => true,
                'error' => null,
                'city' => $city,
            ];
        }

        if ($user->isManager()) {
            $managedCityIds = $user->managedCities()->pluck('cities.id')->toArray();

            if (!in_array($cityId, $managedCityIds)) {
                return [
                    'valid' => false,
                    'error' => 'You do not have permission to access this city.',
                    'city' => $city,
                ];
            }

            return [
                'valid' => true,
                'error' => null,
                'city' => $city,
            ];
        }

        return [
            'valid' => false,
            'error' => 'Unauthorized access.',
            'city' => $city,
        ];
    }

    /**
     * Verify that a department exists, belongs to the correct city, and the user has access.
     */
    public static function validateDepartmentAccess(User $user, int $departmentId, ?int $expectedCityId = null): array
    {
        $department = Department::with('city')->find($departmentId);

        if (!$department) {
            return [
                'valid' => false,
                'error' => 'The selected department does not exist.',
                'department' => null,
            ];
        }

        if (!$department->city) {
            return [
                'valid' => false,
                'error' => 'Department is not associated with a city.',
                'department' => $department,
            ];
        }

        // If expected city is provided, verify department belongs to it
        if ($expectedCityId !== null && $department->city_id !== $expectedCityId) {
            return [
                'valid' => false,
                'error' => 'Department does not belong to the specified city.',
                'department' => $department,
            ];
        }

        // Verify user has access to this department's city
        $cityAccess = self::validateCityAccess($user, $department->city_id);

        if (!$cityAccess['valid']) {
            return [
                'valid' => false,
                'error' => 'You do not have permission to access this department.',
                'department' => $department,
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'department' => $department,
        ];
    }

    /**
     * Verify that a division exists, belongs to the correct department, and the user has access.
     */
    public static function validateDivisionAccess(User $user, int $divisionId, ?int $expectedDepartmentId = null): array
    {
        $division = Division::with('department.city')->find($divisionId);

        if (!$division) {
            return [
                'valid' => false,
                'error' => 'The selected division does not exist.',
                'division' => null,
            ];
        }

        if (!$division->department || !$division->department->city) {
            return [
                'valid' => false,
                'error' => 'Division is not properly associated with a department and city.',
                'division' => $division,
            ];
        }

        // If expected department is provided, verify division belongs to it
        if ($expectedDepartmentId !== null && $division->department_id !== $expectedDepartmentId) {
            return [
                'valid' => false,
                'error' => 'Division does not belong to the specified department.',
                'division' => $division,
            ];
        }

        // Verify user has access to this division's city
        $cityAccess = self::validateCityAccess($user, $division->department->city_id);

        if (!$cityAccess['valid']) {
            return [
                'valid' => false,
                'error' => 'You do not have permission to access this division.',
                'division' => $division,
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'division' => $division,
        ];
    }

    /**
     * Verify that an employee exists, belongs to the correct division, and the user has access.
     */
    public static function validateEmployeeAccess(User $user, int $employeeId, ?int $expectedDivisionId = null): array
    {
        $employee = Employee::with('division.department.city')->find($employeeId);

        if (!$employee) {
            return [
                'valid' => false,
                'error' => 'The selected employee does not exist.',
                'employee' => null,
            ];
        }

        if (!$employee->division || !$employee->division->department || !$employee->division->department->city) {
            return [
                'valid' => false,
                'error' => 'Employee is not properly associated with a division, department, and city.',
                'employee' => $employee,
            ];
        }

        // If expected division is provided, verify employee belongs to it
        if ($expectedDivisionId !== null && $employee->division_id !== $expectedDivisionId) {
            return [
                'valid' => false,
                'error' => 'Employee does not belong to the specified division.',
                'employee' => $employee,
            ];
        }

        // Verify user has access to this employee's city
        $cityAccess = self::validateCityAccess($user, $employee->division->department->city_id);

        if (!$cityAccess['valid']) {
            return [
                'valid' => false,
                'error' => 'You do not have permission to access this employee.',
                'employee' => $employee,
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'employee' => $employee,
        ];
    }

    /**
     * Verify hierarchical integrity when creating/updating a department.
     * Ensures the city exists and user has access.
     */
    public static function validateDepartmentHierarchy(User $user, int $cityId): array
    {
        return self::validateCityAccess($user, $cityId);
    }

    /**
     * Verify hierarchical integrity when creating/updating a division.
     * Ensures department exists, belongs to accessible city.
     */
    public static function validateDivisionHierarchy(User $user, int $departmentId): array
    {
        return self::validateDepartmentAccess($user, $departmentId);
    }

    /**
     * Verify hierarchical integrity when creating/updating an employee.
     * Ensures division exists, belongs to accessible department and city.
     */
    public static function validateEmployeeHierarchy(User $user, int $divisionId): array
    {
        return self::validateDivisionAccess($user, $divisionId);
    }

    /**
     * Verify that when moving an entity (department/division/employee) from one parent to another,
     * the user has access to BOTH the source and destination.
     */
    public static function validateEntityMove(User $user, int $currentParentCityId, int $newParentCityId): array
    {
        // Verify access to current city
        $currentAccess = self::validateCityAccess($user, $currentParentCityId);
        if (!$currentAccess['valid']) {
            return [
                'valid' => false,
                'error' => 'You do not have permission to modify entities in the current city.',
            ];
        }

        // Verify access to new city
        $newAccess = self::validateCityAccess($user, $newParentCityId);
        if (!$newAccess['valid']) {
            return [
                'valid' => false,
                'error' => 'You do not have permission to move entities to the new city.',
            ];
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Validate multiple city IDs at once (useful for bulk operations).
     */
    public static function validateMultipleCities(User $user, array $cityIds): array
    {
        $invalidCities = [];

        foreach ($cityIds as $cityId) {
            $result = self::validateCityAccess($user, $cityId);
            if (!$result['valid']) {
                $invalidCities[] = [
                    'city_id' => $cityId,
                    'error' => $result['error'],
                ];
            }
        }

        if (!empty($invalidCities)) {
            return [
                'valid' => false,
                'error' => 'Some cities are invalid or inaccessible.',
                'invalid_cities' => $invalidCities,
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'invalid_cities' => [],
        ];
    }

    /**
     * Sanitize and validate an integer ID from user input.
     * Returns null if invalid.
     */
    public static function sanitizeId(?string $id): ?int
    {
        if ($id === null) {
            return null;
        }

        $sanitized = filter_var($id, FILTER_VALIDATE_INT);

        if ($sanitized === false || $sanitized < 1) {
            return null;
        }

        return $sanitized;
    }

    /**
     * Sanitize and validate multiple IDs from user input.
     * Returns only valid IDs.
     */
    public static function sanitizeIds(array $ids): array
    {
        $sanitized = [];

        foreach ($ids as $id) {
            $validId = self::sanitizeId($id);
            if ($validId !== null) {
                $sanitized[] = $validId;
            }
        }

        return $sanitized;
    }
}
