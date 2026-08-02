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
            'manage-incidents',
            'manage-depots',
            'manage-requests',
            'run-optimization',
            'manage-routing-settings',
            'system-configuration',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminPermissions = [
            'view-dashboard',
            'manage-incidents',
            'manage-depots',
            'manage-requests',
            'run-optimization',
            'manage-routing-settings',
        ];

        $rolePermissions = [
            'viewer' => ['view-dashboard'],
            'coordinator' => [
                'view-dashboard',
                'manage-incidents',
                'manage-requests',
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
