<?php
// =============================================================
// src/Services/RbacService.php - Role-Based Access Control
// =============================================================
// Skeleton RBAC system for future admin roles
// Current roles: super_admin, hr_admin, branch_admin
// =============================================================

namespace App\Services;

class RbacService
{
    private const ROLES = [
        'super_admin' => [
            'can_manage_admins',
            'can_manage_employees',
            'can_manage_branches',
            'can_manage_settings',
            'can_view_all_branches',
            'can_delete_records',
            'can_export_data',
            'can_view_audit_log',
        ],
        'hr_admin' => [
            'can_manage_employees',
            'can_view_all_branches',
            'can_export_data',
            'can_approve_leaves',
        ],
        'branch_admin' => [
            'can_view_branch_only',
            'can_view_branch_employees',
            'can_view_branch_reports',
        ],
    ];

    /**
     * Check if admin has permission
     */
    public static function hasPermission(array $admin, string $permission): bool
    {
        $role = $admin['role'] ?? 'super_admin'; // Default to super_admin for backward compatibility
        
        if (!isset(self::ROLES[$role])) {
            return false;
        }
        
        return in_array($permission, self::ROLES[$role], true);
    }

    /**
     * Check if admin can access branch
     */
    public static function canAccessBranch(array $admin, int $branchId): bool
    {
        $role = $admin['role'] ?? 'super_admin';
        
        // Super admin and HR admin can access all branches
        if (in_array($role, ['super_admin', 'hr_admin'], true)) {
            return true;
        }
        
        // Branch admin can only access assigned branch
        if ($role === 'branch_admin') {
            return ($admin['branch_id'] ?? null) === $branchId;
        }
        
        return false;
    }

    /**
     * Get available roles
     */
    public static function getRoles(): array
    {
        return array_keys(self::ROLES);
    }

    /**
     * Get permissions for role
     */
    public static function getPermissions(string $role): array
    {
        return self::ROLES[$role] ?? [];
    }

    /**
     * Require permission or exit with 403
     */
    public static function requirePermission(array $admin, string $permission): void
    {
        if (!self::hasPermission($admin, $permission)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
