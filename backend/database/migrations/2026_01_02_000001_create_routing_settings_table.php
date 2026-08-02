<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routing_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('active_provider')->default('osrm'); // osrm | premium
            $table->string('premium_base_url')->nullable();
            $table->text('premium_api_key')->nullable(); // encrypted cast on the model
            $table->unsignedInteger('premium_monthly_cap')->nullable();
            $table->string('usage_period')->nullable(); // "Y-m", e.g. "2026-08"
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('fallback_triggered_at')->nullable();
            $table->string('fallback_reason')->nullable(); // cap_exceeded | provider_error
            $table->string('last_test_status')->nullable(); // ok | failed
            $table->timestamp('last_test_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_settings');
    }
};
