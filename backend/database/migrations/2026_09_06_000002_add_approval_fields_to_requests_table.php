<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The 2026-09-03 CDRRMO interview confirmed every resource request needs
 * department-head sign-off (via the office's paper ICS 213-RR form) before
 * release. `approval_status` is a separate axis from the existing `status`
 * column (which tracks fulfillment: pending/enroute/unmet/fulfilled) — a
 * request can be approved but not yet fulfilled, or fulfilled without this
 * having existed historically, so the two are kept independent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_requests', function (Blueprint $table) {
            $table->string('approval_status')->default('pending')->after('status'); // pending | approved | rejected
            $table->foreignUuid('approved_by')->nullable()->after('requested_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('rejection_reason')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('app_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approval_status', 'approved_at', 'rejection_reason']);
        });
    }
};
