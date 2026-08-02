<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('disaster_id')->nullable()->constrained('app_disasters')->nullOnDelete();
            $table->string('name');
            $table->string('type'); // After-action summary | Resource utilization | Response timeline
            $table->timestamp('generated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_reports');
    }
};
