<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The depot a request is fulfilled from isn't known until a coordinator
 * approves it and picks a depot with matching stock (see
 * RequestController::approve()) — request creation itself stays
 * depot-agnostic, so this column is nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_requests', function (Blueprint $table) {
            $table->foreignUuid('depot_id')->nullable()->after('resource_type_id')->constrained('app_depots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('app_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('depot_id');
        });
    }
};
