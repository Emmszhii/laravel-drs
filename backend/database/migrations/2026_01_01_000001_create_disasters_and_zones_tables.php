<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_disasters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // typhoon, flood, earthquake, fire, landslide
            $table->unsignedTinyInteger('signal'); // 1-5
            $table->string('zone'); // display label, e.g. "Legazpi City, Albay"
            $table->double('lat');
            $table->double('lng');
            // $table->geography('location', 'point', 4326)->nullable(); // enable with PostGIS
            $table->string('status')->default('ACTIVE'); // ACTIVE | MONITORING | RESOLVED
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('app_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->constrained('app_disasters')->cascadeOnDelete();
            $table->string('name'); // barangay name
            $table->unsignedInteger('population_affected')->default(0);
            $table->string('severity_level')->default('Medium'); // High | Medium
            $table->double('lat');
            $table->double('lng');
            // $table->geography('boundary', 'polygon', 4326)->nullable(); // enable with PostGIS
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_zones');
        Schema::dropIfExists('app_disasters');
    }
};
