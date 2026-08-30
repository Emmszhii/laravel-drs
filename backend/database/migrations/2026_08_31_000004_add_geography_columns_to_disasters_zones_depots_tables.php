<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PostGIS-only — the `geography` column type doesn't exist on SQLite,
     * which local dev falls back to when pdo_pgsql isn't available (see
     * CLAUDE.md). A no-op there, same as the original commented-out
     * placeholder lines in the base disasters/zones/depots migrations this
     * one is deliberately kept separate from.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('app_disasters', function (Blueprint $table) {
            $table->geography('location', 'point', 4326)->nullable();
        });
        DB::statement("UPDATE app_disasters SET location = ST_SetSRID(ST_MakePoint(lng, lat), 4326)::geography WHERE lat IS NOT NULL AND lng IS NOT NULL");

        // Zones only ever have a point (lat/lng), no outline geometry
        // anywhere to backfill a polygon `boundary` from (the original
        // placeholder), so this adds a point `location` instead — consistent
        // with disasters/depots and actually backed by real data.
        Schema::table('app_zones', function (Blueprint $table) {
            $table->geography('location', 'point', 4326)->nullable();
        });
        DB::statement("UPDATE app_zones SET location = ST_SetSRID(ST_MakePoint(lng, lat), 4326)::geography WHERE lat IS NOT NULL AND lng IS NOT NULL");

        Schema::table('app_depots', function (Blueprint $table) {
            $table->geography('location', 'point', 4326)->nullable();
        });
        DB::statement("UPDATE app_depots SET location = ST_SetSRID(ST_MakePoint(lng, lat), 4326)::geography WHERE lat IS NOT NULL AND lng IS NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('app_disasters', fn (Blueprint $table) => $table->dropColumn('location'));
        Schema::table('app_zones', fn (Blueprint $table) => $table->dropColumn('location'));
        Schema::table('app_depots', fn (Blueprint $table) => $table->dropColumn('location'));
    }
};
