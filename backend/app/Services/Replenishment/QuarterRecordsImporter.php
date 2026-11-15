<?php

namespace App\Services\Replenishment;

use App\Models\ItemQuarter;
use App\Models\QuarterBudget;
use App\Models\ResourceType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Imports the three quarterly sheets of the data-request workbook
 * (docs/templates/cdrrmo-data-request.xlsx), each saved as CSV:
 *
 *   deliveries — one row per delivery; the quantities received in a quarter
 *                are the office's actual order for that quarter;
 *   stock      — one row per item, one column per quarter ("2023 Q3"): stock
 *                on hand when the quarter closed;
 *   budgets    — one row per quarter: the budget for supplies.
 *
 * All-or-nothing like HistoryImporter. Re-importing replaces the quarters the
 * file covers, so a corrected sheet can simply be imported again. A blank cell
 * means "not recorded", never zero.
 */
class QuarterRecordsImporter
{
    public const KINDS = ['deliveries', 'stock', 'budgets'];

    /** @return array{imported: int, errors: list<string>} */
    public function import(string $kind, string $path, ?string $userId = null): array
    {
        $handle = fopen($path, 'r');
        $header = array_map([self::class, 'key'], fgetcsv($handle) ?: []);
        $rows = [];
        while (($cells = fgetcsv($handle)) !== false) {
            $cells = array_map(fn ($c) => trim((string) $c), $cells);
            if (implode('', $cells) !== '') { // skip blank lines and Excel's ",,,,," rows
                $rows[] = array_pad($cells, count($header), '');
            }
        }
        fclose($handle);

        return match ($kind) {
            'deliveries' => $this->deliveries($header, $rows, $userId),
            'stock' => $this->stock($header, $rows, $userId),
            'budgets' => $this->budgets($header, $rows, $userId),
        };
    }

    private function deliveries(array $header, array $rows, ?string $userId): array
    {
        if ($missing = $this->missing($header, ['date_received', 'item', 'standard_unit', 'quantity_received'])) {
            return $missing;
        }
        $col = array_flip($header);
        $types = $this->types();
        $sums = [];
        $errors = [];
        foreach ($rows as $n => $r) {
            $date = rescue(fn () => Carbon::parse($r[$col['date_received']]), null, false);
            $type = $types[mb_strtolower($r[$col['item']])] ?? null;
            $qty = $r[$col['quantity_received']];
            $problems = array_filter([
                $date ? null : 'bad date_received',
                $this->itemProblem($type, $r[$col['item']], $r[$col['standard_unit']]),
                ctype_digit($qty) ? null : 'quantity_received must be a whole number, 0 or more',
            ]);
            if ($problems) {
                $errors[] = 'Row '.($n + 2).': '.implode('; ', $problems);

                continue;
            }
            $sums[$date->year][$date->quarter][$type->id] = ($sums[$date->year][$date->quarter][$type->id] ?? 0) + (int) $qty;
        }
        if ($errors) {
            return ['imported' => 0, 'errors' => $errors];
        }

        DB::transaction(function () use ($sums, $userId) {
            foreach ($sums as $year => $byQuarter) {
                foreach ($byQuarter as $quarter => $byItem) {
                    // The file is the quarter's full delivery record: items not in it bought nothing.
                    ItemQuarter::where('year', $year)->where('quarter', $quarter)->update(['quantity_ordered' => null]);
                    foreach ($byItem as $typeId => $qty) {
                        $this->put($typeId, $year, $quarter, ['quantity_ordered' => $qty], $userId);
                    }
                }
            }
        });

        return ['imported' => count($rows), 'errors' => []];
    }

    private function stock(array $header, array $rows, ?string $userId): array
    {
        if ($missing = $this->missing($header, ['item', 'standard_unit'])) {
            return $missing;
        }
        $col = array_flip($header);
        $quarters = []; // column index => [year, quarter]
        foreach ($header as $i => $h) {
            if (preg_match('/^(\d{4})_q([1-4])$/', $h, $m)) {
                $quarters[$i] = [(int) $m[1], (int) $m[2]];
            }
        }
        if (! $quarters) {
            return ['imported' => 0, 'errors' => ['No quarter columns found; name them like "2023 Q3".']];
        }
        $types = $this->types();
        $values = [];
        $errors = [];
        foreach ($rows as $n => $r) {
            $type = $types[mb_strtolower($r[$col['item']])] ?? null;
            $problems = array_filter([$this->itemProblem($type, $r[$col['item']], $r[$col['standard_unit']])]);
            foreach ($quarters as $i => [$y, $q]) {
                $cell = $r[$i] ?? '';
                if ($cell === '') {
                    continue; // not counted that quarter
                }
                if (! ctype_digit($cell)) {
                    $problems[] = "{$y} Q{$q} must be blank or a whole number, 0 or more";
                } elseif ($type) {
                    $values[] = [$type->id, $y, $q, (int) $cell];
                }
            }
            if ($problems) {
                $errors[] = 'Row '.($n + 2).': '.implode('; ', $problems);
            }
        }
        if ($errors) {
            return ['imported' => 0, 'errors' => $errors];
        }

        DB::transaction(fn () => collect($values)->each(
            fn ($v) => $this->put($v[0], $v[1], $v[2], ['quantity_available_at_close' => $v[3]], $userId)));

        return ['imported' => count($values), 'errors' => []];
    }

    private function budgets(array $header, array $rows, ?string $userId): array
    {
        $amount = collect($header)->first(fn ($h) => str_starts_with($h, 'budget'));
        if ($missing = $this->missing($header, ['year', 'quarter'])) {
            return $missing;
        }
        if (! $amount) {
            return ['imported' => 0, 'errors' => ['Missing column: budget for supplies']];
        }
        $col = array_flip($header);
        $values = [];
        $errors = [];
        foreach ($rows as $n => $r) {
            $year = $r[$col['year']];
            $quarter = ltrim(strtoupper($r[$col['quarter']]), 'Q');
            $budget = str_replace([',', '₱', ' '], '', $r[$col[$amount]]);
            if ($budget === '') {
                continue; // not recorded
            }
            $problems = array_filter([
                ctype_digit($year) ? null : 'year must be a whole number',
                in_array($quarter, ['1', '2', '3', '4'], true) ? null : 'quarter must be 1-4 or Q1-Q4',
                is_numeric($budget) && $budget >= 0 ? null : 'budget must be a number, 0 or more',
            ]);
            if ($problems) {
                $errors[] = 'Row '.($n + 2).': '.implode('; ', $problems);

                continue;
            }
            $values[] = [(int) $year, (int) $quarter, (float) $budget];
        }
        if ($errors) {
            return ['imported' => 0, 'errors' => $errors];
        }

        DB::transaction(fn () => collect($values)->each(fn ($v) => QuarterBudget::updateOrCreate(
            ['year' => $v[0], 'quarter' => $v[1]], ['budget_amount' => $v[2], 'entered_by' => $userId])));

        return ['imported' => count($values), 'errors' => []];
    }

    private function put(string $typeId, int $year, int $quarter, array $values, ?string $userId): void
    {
        ItemQuarter::updateOrCreate(['resource_type_id' => $typeId, 'year' => $year, 'quarter' => $quarter],
            $values + ['encoded_by' => $userId]);
    }

    private function types()
    {
        return ResourceType::all()->keyBy(fn ($t) => mb_strtolower($t->name));
    }

    private function itemProblem(?ResourceType $type, string $item, string $unit): ?string
    {
        if (! $type) {
            return "unknown item \"{$item}\"";
        }

        return strcasecmp($type->unit, $unit) === 0 ? null : "unit must be \"{$type->unit}\" (convert mixed units first)";
    }

    private function missing(array $header, array $needed): ?array
    {
        $absent = array_diff($needed, $header);

        return $absent ? ['imported' => 0, 'errors' => ['Missing column(s): '.implode(', ', $absent)]] : null;
    }

    /** "Unit price (₱)" → unit_price, "2023 Q3" → 2023_q3, "Date received" → date_received. */
    private static function key(?string $h): string
    {
        $h = mb_strtolower(trim(str_replace("\u{FEFF}", '', (string) $h)));

        return trim(preg_replace('/[^a-z0-9]+/', '_', preg_replace('/\(.*?\)/', '', $h)), '_');
    }
}
