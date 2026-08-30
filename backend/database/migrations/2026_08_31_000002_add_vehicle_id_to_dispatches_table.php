<?php

use App\Models\Dispatch;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_dispatches', function (Blueprint $table) {
            // Additive, not a replacement for `vehicle_label` — the manual
            // "log a dispatch" flow (DispatchController, Dispatch/Index.tsx)
            // still free-types a label with no fleet data behind it.
            // Optimizer-generated dispatches (RunDispatchPlanningJob) set
            // both this and `vehicle_label` (denormalized for display);
            // manual ones keep setting only `vehicle_label`, same as today.
            $table->foreignUuid('vehicle_id')->nullable()->after('vehicle_label')
                ->constrained('app_vehicles')->nullOnDelete();
        });

        // Backfill: one Vehicle per distinct (depot_id, vehicle_label) pair
        // already logged, so existing dispatches gain a real vehicle_id
        // instead of just leaving it null for pre-existing rows.
        Dispatch::query()
            ->whereNotNull('vehicle_label')
            ->select('depot_id', 'vehicle_label')
            ->distinct()
            ->get()
            ->each(function ($row) {
                $vehicle = Vehicle::firstOrCreate(
                    ['depot_id' => $row->depot_id, 'label' => $row->vehicle_label],
                    ['status' => 'available'],
                );

                Dispatch::where('depot_id', $row->depot_id)
                    ->where('vehicle_label', $row->vehicle_label)
                    ->update(['vehicle_id' => $vehicle->id]);
            });
    }

    public function down(): void
    {
        Schema::table('app_dispatches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_id');
        });
    }
};
