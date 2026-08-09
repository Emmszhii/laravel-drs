<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_dispatches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->constrained('app_disasters')->cascadeOnDelete();
            $table->foreignUuid('zone_id')->nullable()->constrained('app_zones')->nullOnDelete();
            $table->foreignUuid('depot_id')->constrained('app_depots')->cascadeOnDelete();
            // Nullable so a dispatch can exist standalone today, and link into a
            // formal allocation-approval flow later without a schema change —
            // mirrors app_allocations.optimization_run_id's nullable-FK pattern.
            $table->foreignUuid('allocation_id')->nullable()->constrained('app_allocations')->nullOnDelete();
            $table->foreignUuid('resource_type_id')->nullable()->constrained('app_resource_types')->nullOnDelete();
            $table->foreignUuid('driver_id')->constrained('users')->cascadeOnDelete();
            $table->string('vehicle_label')->nullable();
            $table->string('status')->default('pending'); // pending | en_route | delivered | cancelled
            $table->double('origin_lat');
            $table->double('origin_lng');
            $table->double('destination_lat');
            $table->double('destination_lng');
            $table->double('current_lat')->nullable();
            $table->double('current_lng')->nullable();
            $table->timestamp('last_position_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_dispatches');
    }
};
