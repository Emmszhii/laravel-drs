<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3 (backend/docs/system-spec.md §5): offline-safe, tamper-evident
 * stock records. Stock entries are append-only and are the only way stock
 * changes; current stock is the sum of an item's entries. Each entry carries
 * a device-made ID (unique, so re-uploads are ignored) and a SHA-256 hash
 * chained to the entry before it (`seq` order).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_stock_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('seq')->unique(); // chain order
            $table->uuid('device_id')->unique(); // made on the device; duplicates are rejected
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types');
            $table->foreignUuid('depot_id')->constrained('app_depots');
            $table->integer('quantity'); // signed: positive adds, negative removes
            $table->string('reason'); // receipt | release | return | adjustment | count_correction | opening_balance | conflict_resolution | issue
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device')->nullable(); // source device label
            $table->timestamp('created_at_device');
            $table->timestamp('received_at');
            $table->boolean('priority')->default(false);
            $table->uuid('depends_on_device_id')->nullable(); // e.g. a return depends on its borrow
            $table->uuid('request_line_id')->nullable();
            $table->uuid('borrow_id')->nullable();
            $table->string('event_tag')->nullable();
            $table->char('prev_hash', 64);
            $table->char('hash', 64);
            $table->index(['resource_type_id', 'depot_id']);
        });

        // Conflicts live beside the entries so resolving one never edits a chained row.
        Schema::create('app_stock_conflicts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_entry_id')->constrained('app_stock_entries');
            $table->string('reason'); // negative_stock | return_not_issued | concurrent_offline_edit | approvals_exceed_stock
            $table->string('detail');
            $table->string('status')->default('open'); // open | resolved
            $table->text('resolution_note')->nullable();
            $table->uuid('resolution_entry_id')->nullable(); // the correcting entry, if one was recorded
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Weekly physical counts, for data check 3 (recorded stock vs. count).
        Schema::create('app_stock_counts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types');
            $table->foreignUuid('depot_id')->constrained('app_depots');
            $table->integer('counted_quantity');
            $table->timestamp('counted_at');
            $table->foreignUuid('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_stock_counts');
        Schema::dropIfExists('app_stock_conflicts');
        Schema::dropIfExists('app_stock_entries');
    }
};
