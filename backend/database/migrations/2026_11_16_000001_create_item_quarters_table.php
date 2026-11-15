<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per item and quarter, what the office's own records show (Table 8):
     * what it actually bought (delivery inspection documents) and what was
     * on hand when the quarter closed (Excel stock sheet). The backtest uses
     * the first as the office's actual orders and the second as opening stock.
     */
    public function up(): void
    {
        Schema::create('app_item_quarters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resource_type_id')->constrained('app_resource_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedInteger('quantity_ordered')->nullable();
            $table->unsignedInteger('quantity_available_at_close')->nullable();
            $table->foreignUuid('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['resource_type_id', 'year', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_item_quarters');
    }
};
