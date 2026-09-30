<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per imported history file, keyed by its SHA-256, so importing the
 * same CSV twice is refused instead of silently doubling the history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_history_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_hash', 64)->unique();
            $table->unsignedInteger('rows');
            $table->foreignUuid('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_history_imports');
    }
};
