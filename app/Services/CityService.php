<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\DB;

class CityService
{
    /**
     * Check if city can be safely deleted.
     *
     * @param City $city
     * @return array
     */
    public function canDelete(City $city): array
    {
        $divisionCount = $city->divisions()->count();
        $managerCount = $city->managers()->count();

        $reasons = [];
        if ($divisionCount > 0) {
            $reasons[] = "{$divisionCount} division(s)";
        }
        if ($managerCount > 0) {
            $reasons[] = "{$managerCount} manager assignment(s)";
        }

        if (!empty($reasons)) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete city with existing ' . implode(' and ', $reasons) . '.',
                'divisions_count' => $divisionCount,
                'managers_count' => $managerCount,
                'message' => "This city has {$divisionCount} division(s) and {$managerCount} manager assignment(s). Please delete or reassign all related data before deleting this city.",
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'divisions_count' => 0,
            'managers_count' => 0,
            'message' => null,
        ];
    }

    /**
     * Delete city with validation.
     *
     * @param City $city
     * @return array
     * @throws \Exception
     */
    public function delete(City $city): array
    {
        $canDelete = $this->canDelete($city);

        if (!$canDelete['can_delete']) {
            return [
                'success' => false,
                'message' => $canDelete['message'],
                'data' => $canDelete,
            ];
        }

        try {
            $city->delete();

            return [
                'success' => true,
                'message' => 'City deleted successfully.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get comprehensive city statistics.
     *
     * @param City $city
     * @return array
     */
    public function getStatistics(City $city): array
    {
        $divisions = $city->divisions()->with('departments.employees')->get();

        $departmentsCount = 0;
        $employeesCount = 0;

        foreach ($divisions as $division) {
            $departmentsCount += $division->departments->count();
            foreach ($division->departments as $department) {
                $employeesCount += $department->employees->count();
            }
        }

        return [
            'divisions_count' => $divisions->count(),
            'departments_count' => $departmentsCount,
            'employees_count' => $employeesCount,
            'managers_count' => $city->managers()->count(),
        ];
    }

    /**
     * Force cascade delete (Admin only - requires explicit confirmation).
     *
     * @param City $city
     * @return array
     */
    public function forceCascadeDelete(City $city): array
    {
        $stats = $this->getStatistics($city);

        DB::beginTransaction();

        try {
            // Log the cascade deletion
            \Log::warning('Force cascade delete initiated for city', [
                'city_id' => $city->id,
                'city_name' => $city->name,
                'user_id' => auth()->id(),
                'statistics' => $stats,
            ]);

            // Get all divisions with departments and employees
            $divisions = $city->divisions()->with('departments.employees')->get();

            // Delete all employees in all departments in all divisions
            foreach ($divisions as $division) {
                foreach ($division->departments as $department) {
                    $department->employees()->delete();
                }
                // Delete all departments
                $division->departments()->delete();
            }

            // Delete all divisions
            $city->divisions()->delete();

            // Delete all manager assignments
            $city->managers()->detach();

            // Delete the city
            $city->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'City and all related data deleted successfully.',
                'deleted' => [
                    'city' => 1,
                    'divisions' => $stats['divisions_count'],
                    'departments' => $stats['departments_count'],
                    'employees' => $stats['employees_count'],
                    'manager_assignments' => $stats['managers_count'],
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Force cascade delete failed for city', [
                'city_id' => $city->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete city: ' . $e->getMessage(),
                'deleted' => null,
            ];
        }
    }

    /**
     * Archive city (soft delete alternative).
     *
     * @param City $city
     * @return array
     */
    public function archive(City $city): array
    {
        try {
            $city->update(['status' => 'inactive']);

            \Log::info('City archived', [
                'city_id' => $city->id,
                'city_name' => $city->name,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'City archived successfully. All related data remains intact.',
                'data' => $city,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to archive city: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Restore archived city.
     *
     * @param City $city
     * @return array
     */
    public function restore(City $city): array
    {
        try {
            $city->update(['status' => 'active']);

            \Log::info('City restored', [
                'city_id' => $city->id,
                'city_name' => $city->name,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'City restored successfully.',
                'data' => $city,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to restore city: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }
}
