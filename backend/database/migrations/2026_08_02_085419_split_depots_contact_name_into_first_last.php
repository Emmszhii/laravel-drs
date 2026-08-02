<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_depots', function (Blueprint $table) {
            $table->string('contact_first_name')->nullable()->after('lng');
            $table->string('contact_last_name')->nullable()->after('contact_first_name');
        });

        DB::table('app_depots')->select('id', 'contact_name')->orderBy('id')->get()->each(function ($depot) {
            if (! $depot->contact_name) {
                return;
            }

            [$first, $last] = $this->splitName($depot->contact_name);

            DB::table('app_depots')->where('id', $depot->id)->update([
                'contact_first_name' => $first,
                'contact_last_name' => $last,
            ]);
        });

        Schema::table('app_depots', function (Blueprint $table) {
            $table->dropColumn('contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('app_depots', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('lng');
        });

        DB::table('app_depots')->select('id', 'contact_first_name', 'contact_last_name')->orderBy('id')->get()->each(function ($depot) {
            $name = trim(($depot->contact_first_name ?? '').' '.($depot->contact_last_name ?? ''));

            DB::table('app_depots')->where('id', $depot->id)->update([
                'contact_name' => $name !== '' ? $name : null,
            ]);
        });

        Schema::table('app_depots', function (Blueprint $table) {
            $table->dropColumn(['contact_first_name', 'contact_last_name']);
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $name = trim($name);
        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0], $parts[1] ?? ''];
    }
};
