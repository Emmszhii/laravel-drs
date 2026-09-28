<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 4: stored rolling-origin backtests (flat 1.5 vs per-item multiplier
 * vs scenario model), so the evaluation results are kept with their inputs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_backtests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('budget', 14, 2); // for quarters without their own recorded budget
            $table->json('quarters'); // [[year, quarter], ...], same index as the result rows
            $table->json('inputs');
            $table->json('result');
            $table->foreignUuid('run_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_backtests');
    }
};
