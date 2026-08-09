<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Legacy `users.role` is set to 'admin' so this account keeps passing
        // today's role:admin-gated routes, alongside the Spatie 'developer' role.
        $user = User::firstOrCreate(
            ['email' => 'emmanuelmanalo707@gmail.com'],
            [
                'first_name' => 'Emmanuel',
                'last_name' => 'Manalo',
                'password' => Hash::make('123123qQ!'),
                'role' => 'admin',
            ]
        );

        if (! $user->hasRole('developer')) {
            $user->assignRole('developer');
        }

        // One account per remaining role, for manually testing the full
        // access matrix in the browser. DatabaseSeeder wraps the whole
        // seeding run in Model::withoutEvents() (via its WithoutModelEvents
        // trait), which suppresses User::booted()'s auto-sync hook here too
        // — not just for the top-level admin account — so each Spatie role
        // still needs assigning explicitly, same as the developer account
        // above.
        foreach (['coordinator', 'respondent', 'viewer'] as $role) {
            $roleUser = User::firstOrCreate(
                ['email' => "{$role}@disasterresource.local"],
                [
                    'first_name' => ucfirst($role),
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
