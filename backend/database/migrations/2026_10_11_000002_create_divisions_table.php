<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The office's divisions and the teams inside them (e.g. Operations and Warning /
 * Team Delta), kept by Administration and Training. An ICS 213-RR request picks
 * its "Division / team" from here; the request still stores the label as text,
 * so renaming or removing a division never rewrites old requests.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_divisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->uuid('parent_id')->nullable();
            $table->timestamps();
        });

        // A separate step: in one Schema::create, Postgres gets this self-reference
        // before the primary key exists and refuses it ("no unique constraint matching").
        Schema::table('app_divisions', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('app_divisions')->cascadeOnDelete(); // the API refuses to delete a division with teams
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_divisions');
    }
};
