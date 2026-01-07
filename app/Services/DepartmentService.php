<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * Check if department can be safely deleted.
     *
     * @param Department $department
     * @return array
     */
    public function canDelete(Department $department): array
    {
        $divisionCount = $department->divisions()->count();

        if ($divisionCount > 0) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete department with existing divisions.',
                'divisions_count' => $divisionCount,
                'message' => "This department has {$divisionCount} division(s). Please delete or reassign all divisions before deleting this department.",
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'divisions_count' => 0,
            'message' => null,
        ];
    }

    /**
     * Delete department with validation.
     *
     * @param Department $department
     * @return array
     * @throws \Exception
     */
    public function delete(Department $department): array
    {
        $canDelete = $this->canDelete($department);

        if (!$canDelete['can_delete']) {
            return [
                'success' => false,
                'message' => $canDelete['message'],
                'data' => $canDelete,
            ];
        }

        try {
            $department->delete();

            return [
                'success' => true,
                'message' => 'Department deleted successfully.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get department statistics.
     *
     * @param Department $department
     * @return array
     */
    public function getStatistics(Department $department): array
    {
        return [
            'divisions_count' => $department->divisions()->count(),
            'active_divisions_count' => $department->divisions()->where('divisions.status', 'active')->count(),
            'inactive_divisions_count' => $department->divisions()->where('divisions.status', 'inactive')->count(),
            'employees_count' => $department->employees()->count(),
            'active_employees_count' => $department->employees()->where('employees.status', 'active')->count(),
        ];
    }

    /**
     * Force cascade delete (Admin only - requires explicit confirmation).
     *
     * @param Department $department
     * @return array
     */
    public function forceCascadeDelete(Department $department): array
    {
        $stats = $this->getStatistics($department);

        DB::beginTransaction();

        try {
            // Log the cascade deletion
            \Log::info('Force cascade delete initiated for department', [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'user_id' => auth()->id(),
                'statistics' => $stats,
            ]);

            // Get all divisions for this department
            $divisions = $department->divisions()->with('employees')->get();

            // Delete all employees in all divisions
            foreach ($divisions as $division) {
                $division->employees()->delete();
            }

            // Delete all divisions
            $department->divisions()->delete();

            // Delete the department
            $department->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Department and all related data deleted successfully.',
                'deleted' => [
                    'department' => 1,
                    'divisions' => $stats['divisions_count'],
                    'employees' => $stats['employees_count'],
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Force cascade delete failed for department', [
                'department_id' => $department->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete department: ' . $e->getMessage(),
                'deleted' => null,
            ];
        }
    }
}
