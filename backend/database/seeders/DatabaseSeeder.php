<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Coordinator',
            'email' => 'admin@disasterresource.local',
            'role' => 'admin',
        ]);

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DisasterDataSeeder::class,
        ]);

        // WithoutModelEvents (above) suppresses User::booted()'s auto-sync
        // hook, so this account needs its Spatie role assigned explicitly —
        // otherwise it passes every backend role:admin check but the
        // sidebar nav (which reads Spatie roles) renders empty for it.
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
