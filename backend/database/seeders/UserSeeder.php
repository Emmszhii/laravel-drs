<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Legacy full-access account: `admin_training` division, plus the
        // is_system_admin flag so it keeps passing every role:system_admin
        // gated route (Settings, user management, routing, etc.).
        $user = User::firstOrCreate(
            ['email' => 'emmanuelmanalo707@gmail.com'],
            [
                'first_name' => 'Emmanuel',
                'last_name' => 'Manalo',
                'password' => Hash::make('123123qQ!'),
                'role' => 'admin_training',
                'is_system_admin' => true,
            ]
        );

        if (! $user->hasRole('admin_training')) {
            $user->assignRole('admin_training');
        }

        // One account per remaining role, for manually testing the full access
        // matrix in the browser. DatabaseSeeder wraps the whole
        // seeding run in Model::withoutEvents() (via its WithoutModelEvents
        // trait), which suppresses User::booted()'s auto-sync hook here too
        // — not just for the top-level account above — so each Spatie role
        // still needs assigning explicitly, same as above.
        foreach (['logistics', 'operations', 'research_planning', 'administrative'] as $role) {
            $roleUser = User::firstOrCreate(
                ['email' => "{$role}@disasterresource.local"],
                [
                    'first_name' => ucfirst(str_replace('_', ' ', $role)),
                    'last_name' => 'Test',
                    'password' => Hash::make('password'),
                    'role' => $role,
                ]
            );

            if (! $roleUser->hasRole($role)) {
                $roleUser->assignRole($role);
            }
        }
    }
}
