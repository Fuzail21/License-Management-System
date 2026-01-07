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
        $departmentCount = $city->departments()->count();
        $managerCount = $city->managers()->count();

        $reasons = [];
        if ($departmentCount > 0) {
            $reasons[] = "{$departmentCount} department(s)";
        }
        if ($managerCount > 0) {
            $reasons[] = "{$managerCount} manager assignment(s)";
        }

        if (!empty($reasons)) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete city with existing ' . implode(' and ', $reasons) . '.',
                'departments_count' => $departmentCount,
                'managers_count' => $managerCount,
                'message' => "This city has {$departmentCount} department(s) and {$managerCount} manager assignment(s). Please delete or reassign all related data before deleting this city.",
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'departments_count' => 0,
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
        $departments = $city->departments()->with('divisions.employees')->get();

        $divisionsCount = 0;
        $employeesCount = 0;

        foreach ($departments as $department) {
            $divisionsCount += $department->divisions->count();
            foreach ($department->divisions as $division) {
                $employeesCount += $division->employees->count();
            }
        }

        return [
            'departments_count' => $departments->count(),
            'divisions_count' => $divisionsCount,
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

            // Get all departments with divisions and employees
            $departments = $city->departments()->with('divisions.employees')->get();

            // Delete all employees in all divisions in all departments
            foreach ($departments as $department) {
                foreach ($department->divisions as $division) {
                    $division->employees()->delete();
                }
                // Delete all divisions
                $department->divisions()->delete();
            }

            // Delete all departments
            $city->departments()->delete();

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
                    'departments' => $stats['departments_count'],
                    'divisions' => $stats['divisions_count'],
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
