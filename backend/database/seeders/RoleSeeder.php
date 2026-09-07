<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-dashboard',
            'view-incidents',
            'view-requests',
            'manage-incidents',
            'manage-depots',
            'manage-requests',
            'approve-requests',
            'manage-evacuation-centers',
            'run-optimization',
            'manage-routing-settings',
            'system-configuration',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // The 6 CDRRMO San Pablo City functional divisions confirmed by the
        // 2026-09-03 stakeholder interview (see CLAUDE.md), plus `viewer` for
        // unassigned/minimal accounts. `admin_training` also gets
        // `system-configuration` by default (Admin & Training is the closest
        // real-world equivalent to "runs the office's systems"), but the
        // canonical way to grant system-configuration/Settings access is the
        // separate `users.is_system_admin` flag (see EnsureUserHasRole),
        // which any user can be granted independent of role.
        $rolePermissions = [
            'viewer' => ['view-dashboard'],
            // Frontline Emergency Response Team: read access, plus managing
            // evacuation centers during a response.
            'ert' => ['view-dashboard', 'view-incidents', 'view-requests', 'manage-evacuation-centers'],
            // Logistics: the office's resource/inventory division — owns
            // depot stock and request approval (ICS 213-RR sign-off).
            'logistics' => [
                'view-dashboard',
                'view-incidents',
                'view-requests',
                'manage-depots',
                'manage-requests',
                'approve-requests',
            ],
            'incident_management' => [
                'view-dashboard',
                'view-incidents',
                'view-requests',
                'manage-incidents',
                'manage-requests',
                'run-optimization',
            ],
            'operations' => [
                'view-dashboard',
                'view-incidents',
                'view-requests',
                'manage-incidents',
                'manage-evacuation-centers',
                'run-optimization',
            ],
            // Research and Planning: reporting/analytics, mostly read-only.
            'research_planning' => ['view-dashboard', 'view-incidents', 'view-requests'],
            'admin_training' => [
                'view-dashboard',
                'view-incidents',
                'view-requests',
                'manage-incidents',
                'manage-depots',
                'manage-requests',
                'approve-requests',
                'manage-evacuation-centers',
                'run-optimization',
                'manage-routing-settings',
                'system-configuration',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePerms);
        }
    }
}
