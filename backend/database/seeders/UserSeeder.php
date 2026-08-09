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
    }
}
