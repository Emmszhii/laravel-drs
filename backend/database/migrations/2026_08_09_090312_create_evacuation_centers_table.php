<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_evacuation_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->constrained('app_disasters')->cascadeOnDelete();
            $table->foreignUuid('zone_id')->nullable()->constrained('app_zones')->nullOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->double('lat');
            $table->double('lng');
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('families_served')->default(0);
            $table->unsignedInteger('individuals_served')->default(0);
            $table->string('status')->default('OPEN'); // OPEN | CLOSED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_evacuation_centers');
    }
};
