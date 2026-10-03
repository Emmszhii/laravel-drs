<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the office's paper ICS 213-RR form and borrow logbook carry that the first
 * digitization left out (photos of 2026-09-03): item subject category, control no.,
 * time, the requester's name and position, a reason per cut line, the Logistics
 * reply, where a borrowed item is used, a position/title per user, and request kits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_resource_types', function (Blueprint $table) {
            $table->string('category')->default('general')->after('unit'); // medical | cleaning | office | general | other
        });

        Schema::table('app_ics_requests', function (Blueprint $table) {
            $table->string('control_no', 30)->nullable()->unique()->after('id'); // the number printed on the paper form
            $table->time('time')->nullable()->after('date');
            $table->string('requester_name', 150)->nullable()->after('requested_by'); // form item 1: who filled in the paper form
            $table->string('requester_position', 100)->nullable()->after('requester_name');
            $table->text('remarks')->nullable(); // form item 11: Logistics' reply
        });

        Schema::table('app_ics_request_lines', function (Blueprint $table) {
            $table->text('approval_note')->nullable(); // why this line was approved for less than requested
        });

        Schema::table('app_borrows', function (Blueprint $table) {
            $table->string('location', 150)->nullable()->after('borrower_division'); // where the item is used (logbook "Location")
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 100)->nullable()->after('role'); // title shown beside who approved or released
        });

        // A reusable set of request lines, e.g. "Medical backpack #1".
        Schema::create('app_kits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('app_kit_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kit_id')->constrained('app_kits')->cascadeOnDelete();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_kit_lines');
        Schema::dropIfExists('app_kits');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('position'));
        Schema::table('app_borrows', fn (Blueprint $t) => $t->dropColumn('location'));
        Schema::table('app_ics_request_lines', fn (Blueprint $t) => $t->dropColumn('approval_note'));
        Schema::table('app_ics_requests', function (Blueprint $table) {
            $table->dropUnique(['control_no']);
            $table->dropColumn(['control_no', 'time', 'requester_name', 'requester_position', 'remarks']);
        });
        Schema::table('app_resource_types', fn (Blueprint $t) => $t->dropColumn('category'));
    }
};
