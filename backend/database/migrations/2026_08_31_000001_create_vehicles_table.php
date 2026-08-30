<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            // Nullable: vehicles migrated from legacy free-text
            // Dispatch.vehicle_label (see the next migration) have no known
            // capacity. Optimizer-synthesized vehicles always set a real one.
            $table->unsignedInteger('capacity')->nullable();
            $table->foreignUuid('depot_id')->constrained('app_depots')->cascadeOnDelete();
            $table->string('status')->default('available'); // available | in_use | maintenance
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_vehicles');
    }
};
