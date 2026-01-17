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
        $employeeCount = $department->employees()->count();

        if ($employeeCount > 0) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete department with existing employees.',
                'employees_count' => $employeeCount,
                'message' => "This department has {$employeeCount} employee(s). Please delete or reassign all employees before deleting this department.",
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'employees_count' => 0,
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
            'employees_count' => $department->employees()->count(),
            'active_employees_count' => $department->employees()->where('status', 'active')->count(),
            'inactive_employees_count' => $department->employees()->where('status', 'inactive')->count(),
            'on_leave_employees_count' => $department->employees()->where('status', 'on_leave')->count(),
            'terminated_employees_count' => $department->employees()->where('status', 'terminated')->count(),
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
                'division_id' => $department->division_id,
                'user_id' => auth()->id(),
                'statistics' => $stats,
            ]);

            // Delete all employees
            $department->employees()->delete();

            // Delete the department
            $department->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Department and all employees deleted successfully.',
                'deleted' => [
                    'department' => 1,
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
