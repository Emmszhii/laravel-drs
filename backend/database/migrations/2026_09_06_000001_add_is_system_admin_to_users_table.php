<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the old admin/developer/coordinator/viewer/respondent placeholder
 * roles with the 6 CDRRMO San Pablo City divisions (Incident Management, ERT,
 * Logistics, Operations, Research and Planning, Admin & Training) confirmed by
 * the 2026-09-03 stakeholder interview, plus `viewer` for unassigned accounts.
 * System-configuration access (Settings, user management, routing, etc.) is no
 * longer tied to a role string — it's this independent flag, since "who
 * configures the app" and "which office division someone works in" don't map
 * 1:1 onto each other.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_system_admin')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_system_admin');
        });
    }
};
