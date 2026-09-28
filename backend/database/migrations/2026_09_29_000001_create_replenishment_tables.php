<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 1 of the new scope (backend/docs/system-spec.md §3–4): the encoded
 * ICS 213-RR history, the quarterly budget, the VED policy, pre-solve data
 * checks, and replenishment runs. Logistics encodes; the Admin (Head of
 * Office) runs the solver and approves or rejects.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_resource_types', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->nullable()->after('unit'); // latest purchase price per standard unit
            $table->string('ved_class', 1)->default('E')->after('unit_cost'); // V | E | D
            $table->decimal('space_per_unit', 10, 4)->nullable()->after('ved_class'); // m³, only where space is modeled
        });

        // One row per encoded request line (the historical encoding template).
        Schema::create('app_history_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity_requested');
            $table->unsignedInteger('quantity_released')->nullable();
            $table->string('purpose'); // team_kit | relief | office_use
            $table->string('event_tag')->nullable(); // e.g. "Kristine"
            $table->string('requesting_division');
            $table->foreignUuid('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['resource_type_id', 'date']);
        });

        Schema::create('app_ved_policies', function (Blueprint $table) {
            $table->string('ved_class', 1)->primary();
            $table->unsignedTinyInteger('coverage_k');
            $table->unsignedTinyInteger('coverage_m')->default(8);
            $table->decimal('weight', 6, 2);
            $table->string('confirmed_by_position')->nullable();
            $table->date('confirmed_at')->nullable();
            $table->timestamps();
        });
        // Table 4 proposal — editable policy, confirmed later with Logistics.
        $now = now();
        DB::table('app_ved_policies')->insert([
            ['ved_class' => 'V', 'coverage_k' => 8, 'coverage_m' => 8, 'weight' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['ved_class' => 'E', 'coverage_k' => 7, 'coverage_m' => 8, 'weight' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['ved_class' => 'D', 'coverage_k' => 6, 'coverage_m' => 8, 'weight' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::create('app_quarter_budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->decimal('budget_amount', 14, 2);
            $table->foreignUuid('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['year', 'quarter']);
        });

        Schema::create('app_data_check_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('rule'); // use_spike | negative_stock | count_mismatch
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('detail');
            $table->string('status')->default('open'); // open | confirmed | corrected
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->unique(['rule', 'resource_type_id', 'year', 'quarter']);
        });

        Schema::create('app_replenishment_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->decimal('budget', 14, 2);
            $table->string('rule')->default('per_item_multiplier'); // per_item_multiplier | scenario
            $table->string('status')->default('queued'); // queued | completed | failed | approved | rejected | superseded
            $table->json('inputs')->nullable();
            $table->json('result')->nullable();
            $table->string('error')->nullable();
            $table->foreignUuid('run_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_comment')->nullable();
            $table->timestamps();
            $table->index(['year', 'quarter', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_replenishment_runs');
        Schema::dropIfExists('app_data_check_flags');
        Schema::dropIfExists('app_quarter_budgets');
        Schema::dropIfExists('app_ved_policies');
        Schema::dropIfExists('app_history_lines');
        Schema::table('app_resource_types', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'ved_class', 'space_per_unit']);
        });
    }
};
