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
        $employeeCount = $division->employees()->count();

        if ($employeeCount > 0) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete division with existing employees.',
                'employees_count' => $employeeCount,
                'message' => "This division has {$employeeCount} employee(s). Please delete or reassign all employees before deleting this division.",
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
            'employees_count' => $division->employees()->count(),
            'active_employees_count' => $division->employees()->where('status', 'active')->count(),
            'inactive_employees_count' => $division->employees()->where('status', 'inactive')->count(),
            'on_leave_employees_count' => $division->employees()->where('status', 'on_leave')->count(),
            'terminated_employees_count' => $division->employees()->where('status', 'terminated')->count(),
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
                'department_id' => $division->department_id,
                'user_id' => auth()->id(),
                'statistics' => $stats,
            ]);

            // Delete all employees
            $division->employees()->delete();

            // Delete the division
            $division->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Division and all employees deleted successfully.',
                'deleted' => [
                    'division' => 1,
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
