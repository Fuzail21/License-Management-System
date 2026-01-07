<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Log city-manager assignment.
     */
    public static function logCityManagerAssignment(int $cityId, int $userId, int $adminId): void
    {
        Log::channel('audit')->info('City-Manager assignment created', [
            'action' => 'city_manager_assigned',
            'city_id' => $cityId,
            'manager_user_id' => $userId,
            'admin_user_id' => $adminId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log city-manager removal.
     */
    public static function logCityManagerRemoval(int $cityId, int $userId, int $adminId): void
    {
        Log::channel('audit')->warning('City-Manager assignment removed', [
            'action' => 'city_manager_removed',
            'city_id' => $cityId,
            'manager_user_id' => $userId,
            'admin_user_id' => $adminId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log failed authorization attempt.
     */
    public static function logUnauthorizedAccess(string $action, ?int $userId, array $context = []): void
    {
        Log::channel('security')->warning('Unauthorized access attempt', [
            'action' => $action,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'context' => $context,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log role change attempt.
     */
    public static function logRoleChange(int $targetUserId, int $oldRoleId, int $newRoleId, int $adminId): void
    {
        Log::channel('audit')->warning('User role changed', [
            'action' => 'role_changed',
            'target_user_id' => $targetUserId,
            'old_role_id' => $oldRoleId,
            'new_role_id' => $newRoleId,
            'admin_user_id' => $adminId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log cascade delete operation.
     */
    public static function logCascadeDelete(string $entityType, int $entityId, array $statistics, int $adminId): void
    {
        Log::channel('audit')->error('Cascade delete performed', [
            'action' => 'cascade_delete',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'statistics' => $statistics,
            'admin_user_id' => $adminId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log entity archival.
     */
    public static function logArchive(string $entityType, int $entityId, int $userId): void
    {
        Log::channel('audit')->info('Entity archived', [
            'action' => 'entity_archived',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log entity restoration.
     */
    public static function logRestore(string $entityType, int $entityId, int $userId): void
    {
        Log::channel('audit')->info('Entity restored', [
            'action' => 'entity_restored',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log data export.
     */
    public static function logDataExport(string $dataType, int $userId, int $recordCount): void
    {
        Log::channel('audit')->info('Data export performed', [
            'action' => 'data_export',
            'data_type' => $dataType,
            'record_count' => $recordCount,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log bulk operations.
     */
    public static function logBulkOperation(string $operation, string $entityType, int $count, int $userId): void
    {
        Log::channel('audit')->info('Bulk operation performed', [
            'action' => 'bulk_operation',
            'operation' => $operation,
            'entity_type' => $entityType,
            'affected_count' => $count,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log failed login attempts.
     */
    public static function logFailedLogin(string $email, string $reason): void
    {
        Log::channel('security')->warning('Failed login attempt', [
            'action' => 'failed_login',
            'email' => $email,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log successful login.
     */
    public static function logSuccessfulLogin(int $userId, string $email): void
    {
        Log::channel('audit')->info('Successful login', [
            'action' => 'login',
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
