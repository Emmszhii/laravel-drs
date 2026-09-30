<?php

namespace App\Services\Replenishment;

use App\Models\HistoryLine;
use App\Models\ResourceType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports the historical encoding template, one row per request line
 * (backend/docs/templates/history-encoding-template.csv). All-or-nothing:
 * any bad row rejects the whole file, so a half-imported quarter never
 * feeds the model. Names and signatures have no column, by design.
 */
class HistoryImporter
{
    public const COLUMNS = ['date', 'item', 'standard_unit', 'quantity_requested', 'quantity_released', 'purpose', 'event_tag', 'requesting_division'];

    private const PURPOSES = ['team kit' => 'team_kit', 'team_kit' => 'team_kit', 'relief' => 'relief', 'office use' => 'office_use', 'office_use' => 'office_use'];

    /** @return array{imported: int, errors: list<string>} */
    public function import(string $path, ?string $userId = null): array
    {
        $handle = fopen($path, 'r');
        $bom = "\u{FEFF}";
        $header = array_map(fn ($h) => strtolower(trim(str_replace($bom, '', (string) $h))), fgetcsv($handle) ?: []);
        if ($header !== self::COLUMNS) {
            fclose($handle);

            return ['imported' => 0, 'errors' => ['Header must be: ' . implode(',', self::COLUMNS)]];
        }

        $types = ResourceType::all()->keyBy(fn ($t) => mb_strtolower($t->name));
        $rows = [];
        $errors = [];
        for ($line = 2; ($cells = fgetcsv($handle)) !== false; $line++) {
            if ($cells === [null]) {
                continue; // blank line
            }
            $r = array_combine(self::COLUMNS, array_slice(array_pad(array_map('trim', $cells), count(self::COLUMNS), ''), 0, count(self::COLUMNS)));
            $type = $types[mb_strtolower($r['item'])] ?? null;
            $purpose = self::PURPOSES[strtolower($r['purpose'])] ?? null;
            $date = rescue(fn () => Carbon::parse($r['date']), null, false);

            $problems = array_filter([
                $date ? null : 'bad date',
                $type ? null : "unknown item \"{$r['item']}\"",
                ! $type || strcasecmp($type->unit, $r['standard_unit']) === 0 ? null : "unit must be \"{$type->unit}\" (convert mixed units first)",
                ctype_digit($r['quantity_requested']) ? null : 'quantity_requested must be a whole number, 0 or more',
                $r['quantity_released'] === '' || ctype_digit($r['quantity_released']) ? null : 'quantity_released must be blank or a whole number, 0 or more',
                $purpose ? null : 'purpose must be team kit, relief, or office use',
                $r['requesting_division'] !== '' ? null : 'requesting_division is required',
            ]);

            if ($problems) {
                $errors[] = "Row {$line}: " . implode('; ', $problems);

                continue;
            }

            $rows[] = [
                'date' => $date->toDateString(),
                'resource_type_id' => $type->id,
                'quantity_requested' => (int) $r['quantity_requested'],
                'quantity_released' => $r['quantity_released'] === '' ? null : (int) $r['quantity_released'],
                'purpose' => $purpose,
                'event_tag' => $r['event_tag'] ?: null,
                'requesting_division' => $r['requesting_division'],
                'encoded_by' => $userId,
            ];
        }
        fclose($handle);

        if ($errors) {
            return ['imported' => 0, 'errors' => $errors];
        }

        // Identical rows can be genuine (two equal request lines on one day), so duplicates are caught per file.
        $hash = hash_file('sha256', $path);
        if ($done = DB::table('app_history_imports')->where('file_hash', $hash)->first()) {
            return ['imported' => 0, 'errors' => ['This file was already imported on '.Carbon::parse($done->created_at)->toDateString().'; importing it again would double its quarters.']];
        }

        DB::transaction(function () use ($rows, $hash, $userId) {
            collect($rows)->each(fn ($row) => HistoryLine::create($row));
            DB::table('app_history_imports')->insert(['id' => (string) Str::uuid(), 'file_hash' => $hash, 'rows' => count($rows),
                'encoded_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
        });

        return ['imported' => count($rows), 'errors' => []];
    }
}
