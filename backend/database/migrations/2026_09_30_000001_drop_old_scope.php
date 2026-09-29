<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Removes the pre-paper scope (incidents, zones, dispatch, evacuation centers,
 * disaster feed, routing, the old allocation solver, requests, stock rows,
 * loans and reports). The paper's system keeps stock in app_stock_entries,
 * requests in app_ics_requests and borrowed items in app_borrows.
 *
 * Depots lose their map coordinates and gain free_space (m³) for the optional
 * depot-space limit (system-spec.md §4.5).
 *
 * Not reversible: the old features' code is gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'app_dispatches', 'app_evacuation_centers', 'app_allocations', 'app_resource_loans',
            'app_quarterly_stock_snapshots', 'app_requests', 'app_optimization_runs', 'app_reports',
            'app_zones', 'app_disasters', 'app_resources', 'app_settings_routing', 'app_settings_disaster_feed',
        ] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        Schema::table('app_depots', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
        Schema::table('app_depots', function (Blueprint $table) {
            $table->decimal('free_space', 12, 3)->nullable()->after('status');
        });

        DB::table('permissions')->whereIn('name', [
            'view-incidents', 'view-requests', 'manage-incidents', 'manage-requests', 'approve-requests',
            'manage-evacuation-centers', 'run-optimization', 'manage-routing-settings', 'system-configuration',
        ])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Not reversible.
    }
};
