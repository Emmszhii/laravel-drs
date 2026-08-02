<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        DB::table('users')->select('id', 'name')->orderBy('id')->get()->each(function ($user) {
            [$first, $last] = $this->splitName($user->name);

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $first,
                'last_name' => $last,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('users')->select('id', 'first_name', 'last_name')->orderBy('id')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'name' => trim("{$user->first_name} {$user->last_name}"),
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name']);
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return ['Unknown', 'User'];
        }

        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0], $parts[1] ?? ''];
    }
};
