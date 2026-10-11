<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * The `viewer` role is gone: an account always belongs to one of the office's
 * divisions. Viewer accounts move to `administrative`, which has the same
 * read-only access, and it becomes the column default. The Spatie role (with
 * its assignments) is deleted.
 *
 * Safe on an empty database. Not reversible: the old role is gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $userIds = DB::table('users')->where('role', 'viewer')->pluck('id');

        if ($userIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $userIds)->update(['role' => 'administrative']);

            // The query builder skips User::booted()'s role sync, so give them the Spatie role here.
            $administrativeId = DB::table('roles')->where(['name' => 'administrative', 'guard_name' => 'web'])->value('id')
                ?? DB::table('roles')->insertGetId(['name' => 'administrative', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);

            foreach ($userIds as $id) {
                DB::table('model_has_roles')->insertOrIgnore(['role_id' => $administrativeId, 'model_type' => User::class, 'model_id' => $id]);
            }
        }

        $oldIds = DB::table('roles')->where('name', 'viewer')->pluck('id');
        DB::table('model_has_roles')->whereIn('role_id', $oldIds)->delete();
        DB::table('role_has_permissions')->whereIn('role_id', $oldIds)->delete();
        DB::table('roles')->whereIn('id', $oldIds)->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('administrative')->change();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Not reversible.
    }
};
