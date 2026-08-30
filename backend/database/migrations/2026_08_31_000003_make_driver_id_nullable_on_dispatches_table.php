<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optimizer-generated dispatches (RunDispatchPlanningJob) exist as a
        // suggested route before a coordinator assigns a driver — the manual
        // "log a dispatch" flow still requires one at the request-validation
        // layer (DispatchController::store), this only relaxes the DB
        // constraint so the other creation path can insert without one yet.
        // Avoids ->change() (needs doctrine/dbal, not installed here): drop
        // and recreate the column instead, in two steps so SQLite/Postgres
        // both handle the FK drop before the column drop cleanly.
        Schema::table('app_dispatches', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
        });
        Schema::table('app_dispatches', function (Blueprint $table) {
            $table->dropColumn('driver_id');
        });
        Schema::table('app_dispatches', function (Blueprint $table) {
            $table->foreignUuid('driver_id')->nullable()->after('vehicle_id')
                ->constrained('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('app_dispatches', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
        });
        Schema::table('app_dispatches', function (Blueprint $table) {
            $table->dropColumn('driver_id');
        });
        Schema::table('app_dispatches', function (Blueprint $table) {
            $table->foreignUuid('driver_id')->after('vehicle_id')
                ->constrained('users')->cascadeOnDelete();
        });
    }
};
