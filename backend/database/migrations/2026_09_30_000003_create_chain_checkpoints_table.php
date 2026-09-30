<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the nightly "check records" saw: the last sequence number and its
 * fingerprint. A later check fails if entries after it vanish or that entry's
 * fingerprint changes (the chain was rebuilt), which recomputing alone misses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_chain_checkpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('latest_seq');
            $table->string('latest_hash', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_chain_checkpoints');
    }
};
