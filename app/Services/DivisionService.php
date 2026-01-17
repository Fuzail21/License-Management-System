<?php

namespace App\Services;

use App\Models\Division;
use Illuminate\Support\Facades\DB;

class DivisionService
{
    /**
     * Check if division can be safely deleted.
     *
     * @param Division $division
     * @return array
     */
    public function canDelete(Division $division): array
    {
        $departmentCount = $division->departments()->count();

        if ($departmentCount > 0) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete division with existing departments.',
                'departments_count' => $departmentCount,
                'message' => "This division has {$departmentCount} department(s). Please delete or reassign all departments before deleting this division.",
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'departments_count' => 0,
            'message' => null,
        ];
    }

    /**
     * Delete division with validation.
     *
     * @param Division $division
     * @return array
     * @throws \Exception
     */
    public function delete(Division $division): array
    {
        $canDelete = $this->canDelete($division);

        if (!$canDelete['can_delete']) {
            return [
                'success' => false,
                'message' => $canDelete['message'],
                'data' => $canDelete,
            ];
        }

        try {
            $division->delete();

            return [
                'success' => true,
                'message' => 'Division deleted successfully.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get division statistics.
     *
     * @param Division $division
     * @return array
     */
    public function getStatistics(Division $division): array
    {
        return [
            'departments_count' => $division->departments()->count(),
            'active_departments_count' => $division->departments()->where('departments.status', 'active')->count(),
            'inactive_departments_count' => $division->departments()->where('departments.status', 'inactive')->count(),
            'employees_count' => $division->employees()->count(),
            'active_employees_count' => $division->employees()->where('employees.status', 'active')->count(),
        ];
    }

    /**
     * Force cascade delete (Admin only - requires explicit confirmation).
     *
     * @param Division $division
     * @return array
     */
    public function forceCascadeDelete(Division $division): array
    {
        $stats = $this->getStatistics($division);

        DB::beginTransaction();

        try {
            // Log the cascade deletion
            \Log::info('Force cascade delete initiated for division', [
                'division_id' => $division->id,
                'division_name' => $division->name,
                'user_id' => auth()->id(),
                'statistics' => $stats,
            ]);

            // Get all departments for this division
            $departments = $division->departments()->with('employees')->get();

            // Delete all employees in all departments
            foreach ($departments as $department) {
                $department->employees()->delete();
            }

            // Delete all departments
            $division->departments()->delete();

            // Delete the division
            $division->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Division and all related data deleted successfully.',
                'deleted' => [
                    'division' => 1,
                    'departments' => $stats['departments_count'],
                    'employees' => $stats['employees_count'],
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Force cascade delete failed for division', [
                'division_id' => $division->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete division: ' . $e->getMessage(),
                'deleted' => null,
            ];
        }
    }
}
