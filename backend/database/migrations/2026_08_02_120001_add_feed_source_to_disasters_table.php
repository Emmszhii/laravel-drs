<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_disasters', function (Blueprint $table) {
            // null = manually logged by a coordinator; otherwise which external
            // feed created/adopted this incident (see App\Services\DisasterFeed).
            $table->string('source')->nullable()->after('status');
            $table->string('external_id')->nullable()->after('source');
            $table->string('source_url')->nullable()->after('external_id');
            $table->timestamp('external_updated_at')->nullable()->after('source_url');

            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('app_disasters', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_id']);
            $table->dropColumn(['source', 'external_id', 'source_url', 'external_updated_at']);
        });
    }
};
