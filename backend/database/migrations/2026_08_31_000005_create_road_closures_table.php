<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        Schema::create('app_road_closures', function (Blueprint $table) use ($isPostgres) {
            $table->uuid('id')->primary();
            $table->string('label');
            // Plain lat/lng always present (works on SQLite too, mirrors the
            // lat/lng + optional-geography pattern used elsewhere in this
            // schema); `location` (PostGIS-only, see below) backs the actual
            // ST_DWithin closure check in GeofenceService.
            $table->double('lat');
            $table->double('lng');
            $table->unsignedInteger('radius_m')->default(200); // MVP: a point + radius, not a real road segment
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            if ($isPostgres) {
                $table->geography('location', 'point', 4326)->nullable();
            }
        });

        if ($isPostgres) {
            DB::statement("UPDATE app_road_closures SET location = ST_SetSRID(ST_MakePoint(lng, lat), 4326)::geography WHERE lat IS NOT NULL AND lng IS NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_road_closures');
    }
};
