<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formalizes CDRRMO's existing manual practice (2026-09-03 interview,
 * Logistics section): stock is reviewed each quarter, and the prior
 * quarter's consumption informs the next quarter's request/reorder. One row
 * per depot/resource_type/quarter — see SnapshotQuarterlyStockJob (the
 * writer) and ReorderSuggestionCalculator (the reader).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_quarterly_stock_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('depot_id')->constrained('app_depots')->cascadeOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter'); // 1-4
            $table->unsignedInteger('quantity_available_at_close');
            $table->unsignedInteger('quantity_consumed');
            $table->timestamps();

            $table->unique(['depot_id', 'resource_type_id', 'year', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_quarterly_stock_snapshots');
    }
};
