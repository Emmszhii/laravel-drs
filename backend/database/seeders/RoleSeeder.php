<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * The roles follow the CDRRMO San Pablo City office structure confirmed by the
 * 2026-09-03 requirements interview: Research and Planning, Operations and
 * Warning, Administration and Training, and Administrative, with Logistics as
 * a section under Administration and Training; plus `viewer` for unassigned
 * accounts. ERT teams sit inside Operations, so they are not roles; the
 * Assistant Department Head is a `users.position`, not a role.
 *
 * The backend gates routes by `users.role` (EnsureUserHasRole), not by these
 * permissions; they only drive the frontend's `can()` (Hooks/use-permissions.ts).
 * The Admin (`is_system_admin`, the Department Head / Head of Office) gets every
 * permission (User::toInertiaPayload) and passes every role check.
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
            'logistics' => ['view-dashboard'],
            'operations' => ['view-dashboard'],
            'research_planning' => ['view-dashboard'],
            // Administrative is read-only, like viewer.
            'administrative' => ['view-dashboard'],
            // Admin and Training keeps the item catalog and depot list.
            'admin_training' => ['view-dashboard', 'manage-depots'],
        ];

        foreach ($rolePermissions as $roleName => $rolePerms) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])->syncPermissions($rolePerms);
        }
    }
}
