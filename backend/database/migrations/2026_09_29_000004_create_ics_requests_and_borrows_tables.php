<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 5 (backend/docs/system-spec.md §3.2, §3.3, §6): the digitized
 * ICS 213-RR resource request (requested and released quantities per line),
 * reservations (approval reserves, release deducts through stock entries),
 * the borrowed-equipment logbook, and a log of every status change.
 * Only the form's fields are modeled — not ICS command roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_ics_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('requesting_division'); // the office's own division/team
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('purpose'); // team_kit | relief | office_use
            $table->string('event_tag')->nullable();
            $table->string('status')->default('draft'); // draft | submitted | approved | partially_released | released | closed | rejected | cancelled
            $table->text('decision_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('app_ics_request_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ics_request_id')->constrained('app_ics_requests')->cascadeOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types');
            $table->foreignUuid('depot_id')->nullable()->constrained('app_depots'); // chosen at approval
            $table->unsignedInteger('quantity_requested'); // never changed after submit
            $table->unsignedInteger('quantity_approved')->nullable();
            $table->unsignedInteger('quantity_released')->default(0); // running total of releases
            $table->timestamps();
        });

        // Stock held for an approved line so it can't be promised twice (available = on hand - held).
        Schema::create('app_reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('request_line_id')->unique()->constrained('app_ics_request_lines')->cascadeOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types');
            $table->foreignUuid('depot_id')->constrained('app_depots');
            $table->unsignedInteger('quantity'); // still held
            $table->timestamps();
        });

        // Borrowed-equipment logbook: one row per unit lent out.
        Schema::create('app_borrows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('request_line_id')->nullable()->constrained('app_ics_request_lines')->nullOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types');
            $table->foreignUuid('depot_id')->constrained('app_depots');
            $table->string('property_number')->nullable(); // serial/property number when the unit has one
            $table->string('borrower_division');
            $table->string('borrower_name'); // personal data: Admin and Logistics only
            $table->string('borrower_contact')->nullable(); // personal data: Admin and Logistics only
            $table->timestamp('issued_at');
            $table->string('status')->default('issued'); // issued | returned | for_repair | condemned | surrendered
            $table->timestamps();
        });

        Schema::create('app_status_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // ics_request | borrow
            $table->uuid('subject_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at');
            $table->index(['subject_type', 'subject_id']);
        });

        // An approval that exceeds available stock is flagged on the request line, not a stock entry.
        Schema::table('app_stock_conflicts', function (Blueprint $table) {
            $table->uuid('stock_entry_id')->nullable()->change();
            $table->uuid('request_line_id')->nullable()->after('stock_entry_id');
        });
    }

    public function down(): void
    {
        Schema::table('app_stock_conflicts', function (Blueprint $table) {
            $table->dropColumn('request_line_id');
        });
        Schema::dropIfExists('app_status_changes');
        Schema::dropIfExists('app_borrows');
        Schema::dropIfExists('app_reservations');
        Schema::dropIfExists('app_ics_request_lines');
        Schema::dropIfExists('app_ics_requests');
    }
};
