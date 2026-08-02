<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings_disaster_feed', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('usgs_enabled')->default(false);
            $table->decimal('usgs_min_magnitude', 3, 1)->default(4.5);
            $table->boolean('gdacs_enabled')->default(false);
            $table->string('gdacs_min_alert_level')->default('Orange'); // Green | Orange | Red
            $table->unsignedInteger('poll_interval_minutes')->default(60); // 15 | 30 | 60 | 360 | 1440
            $table->boolean('auto_resolve_enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_run_status')->nullable(); // ok | partial | failed
            $table->text('last_run_error')->nullable();
            $table->unsignedInteger('last_run_created_count')->default(0);
            $table->unsignedInteger('last_run_updated_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings_disaster_feed');
    }
};
