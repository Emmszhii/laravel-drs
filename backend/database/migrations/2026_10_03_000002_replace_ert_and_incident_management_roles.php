<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * The roles follow the office structure confirmed in the 2026-09-03
 * interview (system-spec.md §2): ERT is a team inside Operations, and
 * "Incident Management" was never named. Accounts with either role move to
 * `operations`, and the two Spatie roles (with their assignments) are deleted.
 * `administrative` needs no migration: RoleSeeder and User::booted create it.
 *
 * Safe on an empty database. Not reversible: the old roles are gone.
 */
return new class extends Migration
{
    private const OLD_ROLES = ['ert', 'incident_management'];

    public function up(): void
    {
        $userIds = DB::table('users')->whereIn('role', self::OLD_ROLES)->pluck('id');

        if ($userIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $userIds)->update(['role' => 'operations']);

            // The query builder skips User::booted()'s role sync, so give them the Spatie role here.
            $operationsId = DB::table('roles')->where(['name' => 'operations', 'guard_name' => 'web'])->value('id')
                ?? DB::table('roles')->insertGetId(['name' => 'operations', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);

            foreach ($userIds as $id) {
                DB::table('model_has_roles')->insertOrIgnore(['role_id' => $operationsId, 'model_type' => User::class, 'model_id' => $id]);
            }
        }

        $oldIds = DB::table('roles')->whereIn('name', self::OLD_ROLES)->pluck('id');
        DB::table('model_has_roles')->whereIn('role_id', $oldIds)->delete();
        DB::table('role_has_permissions')->whereIn('role_id', $oldIds)->delete();
        DB::table('roles')->whereIn('id', $oldIds)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Not reversible.
    }
};
