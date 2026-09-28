<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NDRRMC/Sendai alignment was never confirmed by the CDRRMO stakeholder
 * interview — it only appeared in an earlier proposal draft as a panelist
 * suggestion. Removed end-to-end (see also the deleted NdrrmcReportBuilder/
 * NdrrmcCsvExporter/ndrrmc-sitrep.blade.php/ZoneNdrrmcModal.tsx) in favor of
 * the Inventory Burn-Rate & Reorder Report.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_zones', function (Blueprint $table) {
            $table->dropColumn([
                'casualties_dead',
                'casualties_injured',
                'casualties_missing',
                'families_affected',
                'houses_totally_damaged',
                'houses_partially_damaged',
                'estimated_damage_cost',
                'damage_cost_currency',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('app_zones', function (Blueprint $table) {
            $table->unsignedInteger('casualties_dead')->default(0);
            $table->unsignedInteger('casualties_injured')->default(0);
            $table->unsignedInteger('casualties_missing')->default(0);
            $table->unsignedInteger('families_affected')->default(0);
            $table->unsignedInteger('houses_totally_damaged')->default(0);
            $table->unsignedInteger('houses_partially_damaged')->default(0);
            $table->decimal('estimated_damage_cost', 14, 2)->nullable();
            $table->string('damage_cost_currency', 3)->default('PHP');
        });
    }
};
