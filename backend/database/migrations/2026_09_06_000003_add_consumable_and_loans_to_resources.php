<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The 2026-09-03 CDRRMO interview distinguished consumable resources (used up,
 * never returned) from borrowed ones (tools/equipment needing sign-in/sign-out
 * and a return date). This is aggregate-level, matching how the office's own
 * Excel/logbook already works — not per-serialized-unit tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_resource_types', function (Blueprint $table) {
            $table->boolean('is_consumable')->default(true)->after('unit');
        });

        Schema::create('app_resource_loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resource_id')->constrained('app_resources')->cascadeOnDelete();
            $table->string('borrower_name');
            $table->string('borrower_contact')->nullable();
            $table->unsignedInteger('quantity');
            $table->timestamp('issued_at');
            $table->date('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('status')->default('issued'); // issued | returned | for_repair | condemned | surrendered
            $table->text('notes')->nullable();
            $table->foreignUuid('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_resource_loans');

        Schema::table('app_resource_types', function (Blueprint $table) {
            $table->dropColumn('is_consumable');
        });
    }
};
