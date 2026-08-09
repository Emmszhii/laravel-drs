<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings_smtp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_enabled')->default(false);
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // encrypted cast on the model
            $table->string('encryption')->nullable(); // null | tls | ssl
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->string('last_test_status')->nullable(); // ok | failed
            $table->timestamp('last_test_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings_smtp');
    }
};
