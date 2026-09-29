<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * The 6 CDRRMO San Pablo City functional divisions confirmed by the
 * 2026-09-03 stakeholder interview, plus `viewer` for unassigned accounts.
 *
 * The backend gates routes by `users.role` (EnsureUserHasRole), not by these
 * permissions; they only drive the frontend's `can()` (Hooks/use-permissions.ts).
 * The Admin (`is_system_admin`, the Head of Office) gets every permission
 * (User::toInertiaPayload) and passes every role check.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['view-dashboard', 'manage-depots'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'viewer' => ['view-dashboard'],
            'ert' => ['view-dashboard'],
            'logistics' => ['view-dashboard'],
            'incident_management' => ['view-dashboard'],
            'operations' => ['view-dashboard'],
            'research_planning' => ['view-dashboard'],
            // Admin and Training keeps the item catalog and depot list.
            'admin_training' => ['view-dashboard', 'manage-depots'],
        ];

        foreach ($rolePermissions as $roleName => $rolePerms) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])->syncPermissions($rolePerms);
        }
    }
}
