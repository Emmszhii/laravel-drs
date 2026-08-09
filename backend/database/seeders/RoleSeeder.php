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
            'manage-evacuation-centers',
            'run-optimization',
            'manage-routing-settings',
            'system-configuration',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminPermissions = [
            'view-dashboard',
            'view-incidents',
            'view-requests',
            'manage-incidents',
            'manage-depots',
            'manage-requests',
            'manage-evacuation-centers',
            'run-optimization',
            'manage-routing-settings',
        ];

        $rolePermissions = [
            'viewer' => ['view-dashboard'],
            // Field responder: read-only access to incidents/requests, no management rights.
            'respondent' => ['view-dashboard', 'view-incidents', 'view-requests'],
            'coordinator' => [
                'view-dashboard',
                'view-incidents',
                'view-requests',
                'manage-incidents',
                'manage-requests',
                'manage-evacuation-centers',
                'run-optimization',
            ],
            // Developer shares every admin feature, plus full system configuration.
            'admin' => $adminPermissions,
            'developer' => [...$adminPermissions, 'system-configuration'],
        ];

        foreach ($rolePermissions as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePerms);
        }
    }
}
