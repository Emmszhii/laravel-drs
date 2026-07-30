<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optimization_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->constrained('disasters')->cascadeOnDelete();
            $table->foreignUuid('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('algorithm_used')->nullable();
            $table->float('objective_value')->nullable();
            $table->string('status')->default('completed');
            $table->json('response_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('resource_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity_requested');
            $table->string('priority')->default('medium'); // low | medium | high | critical
            $table->string('status')->default('pending'); // pending | enroute | unmet | fulfilled
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignUuid('depot_id')->constrained('depots')->cascadeOnDelete();
            $table->foreignUuid('optimization_run_id')->nullable()->constrained('optimization_runs')->nullOnDelete();
            $table->unsignedInteger('quantity_allocated');
            $table->string('status')->default('pending'); // pending | en_route | delivered | cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
        Schema::dropIfExists('requests');
        Schema::dropIfExists('optimization_runs');
    }
};
