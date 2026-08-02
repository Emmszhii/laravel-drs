<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_depots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('address');
            $table->string('status')->default('OPERATIONAL'); // OPERATIONAL | LIMITED CAPACITY | OFFLINE
            $table->double('lat');
            $table->double('lng');
            // $table->geography('location', 'point', 4326)->nullable(); // enable with PostGIS
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();
        });

        Schema::create('app_resource_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('unit'); // kits, packs, containers, pieces...
            $table->timestamps();
        });

        Schema::create('app_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('depot_id')->constrained('app_depots')->cascadeOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity_available')->default(0);
            $table->unsignedInteger('quantity_reserved')->default(0);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_resources');
        Schema::dropIfExists('app_resource_types');
        Schema::dropIfExists('app_depots');
    }
};
