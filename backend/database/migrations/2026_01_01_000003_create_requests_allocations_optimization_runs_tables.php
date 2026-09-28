<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_optimization_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->constrained('app_disasters')->cascadeOnDelete();
            $table->foreignUuid('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('algorithm_used')->nullable();
            $table->float('objective_value')->nullable();
            $table->string('status')->default('completed');
            $table->json('response_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('app_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('zone_id')->constrained('app_zones')->cascadeOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity_requested');
            $table->string('priority')->default('medium'); // low | medium | high | critical
            $table->string('status')->default('pending'); // pending | enroute | unmet | fulfilled
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('app_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('request_id')->constrained('app_requests')->cascadeOnDelete();
            $table->foreignUuid('depot_id')->constrained('app_depots')->cascadeOnDelete();
            $table->foreignUuid('optimization_run_id')->nullable()->constrained('app_optimization_runs')->nullOnDelete();
            $table->unsignedInteger('quantity_allocated');
            $table->string('status')->default('pending'); // reserved | pending | en_route | fulfilled | cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_allocations');
        Schema::dropIfExists('app_requests');
        Schema::dropIfExists('app_optimization_runs');
    }
};
