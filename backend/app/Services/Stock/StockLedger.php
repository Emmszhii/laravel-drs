<?php

namespace App\Services\Stock;

use App\Models\StockConflict;
use App\Models\StockEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The only writer of stock (system-spec.md §5.2). For each incoming entry:
 *   1. duplicate check on the device-made ID — a re-upload is ignored;
 *   2. an entry whose dependency hasn't arrived yet is deferred;
 *   3. append with h_n = SHA-256(h_{n-1} || canonical(entry));
 *   4. conflict checks — the entry is kept and flagged for Logistics, never dropped.
 * Entries merge by addition, so arrival order never changes the totals.
 */
class StockLedger
{
    public const GENESIS = '0000000000000000000000000000000000000000000000000000000000000000';

    /** An entry received this long after it was made on the device counts as made offline. */
    public const OFFLINE_AFTER_SECONDS = 60;

    /** pg_advisory_xact_lock key for the chain (any fixed bigint). */
    private const CHAIN_LOCK_KEY = 7315001;

    /**
     * @param  array{device_id: string, resource_type_id: string, depot_id: string, quantity: int, reason: string,
     *   created_at_device?: string|null, device?: string|null, priority?: bool, depends_on_device_id?: string|null,
     *   request_line_id?: string|null, borrow_id?: string|null, event_tag?: string|null}  $data
     * @param  (callable(StockEntry): list<string>)|null  $then  runs inside the same lock and transaction once the
     *   entry is accepted (never for a duplicate or deferred one); returns any extra conflict reasons
     * @return array{status: 'accepted'|'duplicate'|'deferred', entry: ?StockEntry, conflicts: list<string>}
     */
    public function append(array $data, ?string $userId, ?callable $then = null): array
    {
        // ponytail: one global lock serializes the chain; per-depot chains if write volume ever needs it.
        // On Postgres it is a transaction-level advisory lock, held until the outermost transaction
        // commits, so a caller's surrounding transaction (release, return) can't let a second writer
        // read the same last entry. Other drivers (SQLite in dev/tests) fall back to a cache lock.
        if (DB::getDriverName() === 'pgsql') {
            return DB::transaction(function () use ($data, $userId, $then) {
                DB::select('select pg_advisory_xact_lock(?)', [self::CHAIN_LOCK_KEY]);

                return $this->appendLocked($data, $userId, $then);
            });
        }

        return Cache::lock('stock-ledger-chain', 15)->block(15, fn () => DB::transaction(fn () => $this->appendLocked($data, $userId, $then)));
    }

    private function appendLocked(array $data, ?string $userId, ?callable $then): array
    {
        if ($existing = StockEntry::where('device_id', $data['device_id'])->first()) {
            return ['status' => 'duplicate', 'entry' => $existing, 'conflicts' => []];
        }

        $dependsOn = $data['depends_on_device_id'] ?? null;
        if ($dependsOn && ! StockEntry::where('device_id', $dependsOn)->exists()) {
            return ['status' => 'deferred', 'entry' => null, 'conflicts' => []];
        }

        $last = StockEntry::orderByDesc('seq')->first();
        $entry = new StockEntry([
            'seq' => ($last->seq ?? 0) + 1,
            'device_id' => $data['device_id'],
            'resource_type_id' => $data['resource_type_id'],
            'depot_id' => $data['depot_id'],
            'quantity' => (int) $data['quantity'],
            'reason' => $data['reason'],
            'user_id' => $userId,
            'device' => $data['device'] ?? null,
            'created_at_device' => self::toAppTime($data['created_at_device'] ?? null),
            'received_at' => now()->startOfSecond(),
            'priority' => (bool) ($data['priority'] ?? false),
            'depends_on_device_id' => $dependsOn,
            'request_line_id' => $data['request_line_id'] ?? null,
            'borrow_id' => $data['borrow_id'] ?? null,
            'event_tag' => $data['event_tag'] ?? null,
            'prev_hash' => $last->hash ?? self::GENESIS,
        ]);
        $entry->hash = self::fingerprint($entry);
        $entry->save();

        $extra = $then ? $then($entry) : [];

        return ['status' => 'accepted', 'entry' => $entry, 'conflicts' => [...$this->flagConflicts($entry), ...$extra]];
    }

    /** Server-side entry (receipts from purchases, releases, returns) — no device, made now. */
    public function record(array $data, ?string $userId): StockEntry
    {
        return $this->append(['device_id' => (string) Str::uuid(), 'device' => 'server'] + $data, $userId)['entry'];
    }

    /**
     * Sync order for a batch from one outbox: priority entries first, then by creation time,
     * and an entry that depends on another in the batch always after it.
     */
    public static function syncOrder(array $entries): array
    {
        // Times are compared as instants, so devices sending different offsets or formats still sort right.
        $at = fn (array $e) => isset($e['created_at_device']) ? Carbon::parse($e['created_at_device'])->getTimestamp() : 0;
        usort($entries, fn ($a, $b) => [! ($a['priority'] ?? false), $at($a)] <=> [! ($b['priority'] ?? false), $at($b)]);

        $byId = array_column($entries, null, 'device_id');
        $ordered = [];
        $visit = function (array $e) use (&$visit, &$ordered, $byId) {
            if (isset($ordered[$e['device_id']])) {
                return;
            }
            $ordered[$e['device_id']] = false; // mark before recursing, so a cycle can't loop
            $dep = $e['depends_on_device_id'] ?? null;
            if ($dep && isset($byId[$dep])) {
                $visit($byId[$dep]);
            }
            unset($ordered[$e['device_id']]);
            $ordered[$e['device_id']] = $e;
        };
        foreach ($entries as $e) {
            $visit($e);
        }

        return array_values(array_filter($ordered));
    }

    /** Current stock: the sum of entries per item (and depot). */
    public function levels(): Collection
    {
        return StockEntry::query()
            ->selectRaw('resource_type_id, depot_id, SUM(quantity) AS quantity')
            ->groupBy('resource_type_id', 'depot_id')
            ->get();
    }

    public function level(string $resourceTypeId, ?string $depotId = null, ?Carbon $asOf = null): int
    {
        return (int) StockEntry::where('resource_type_id', $resourceTypeId)
            ->when($depotId, fn ($q) => $q->where('depot_id', $depotId))
            ->when($asOf, fn ($q) => $q->where('created_at_device', '<=', $asOf))
            ->sum('quantity');
    }

    public function latestFingerprint(): string
    {
        return StockEntry::orderByDesc('seq')->value('hash') ?? self::GENESIS;
    }

    /**
     * "Check records": recomputes the chain from the first entry and reports the first entry
     * whose fingerprint no longer matches (or a missing sequence number).
     *
     * @return array{ok: bool, checked: int, first_bad_seq: ?int, problem: ?string, latest_hash: string}
     */
    public function verify(): array
    {
        $prev = self::GENESIS;
        $expectedSeq = 1;
        $checked = 0;

        foreach (StockEntry::orderBy('seq')->lazy(500) as $entry) {
            $problem = match (true) {
                $entry->seq !== $expectedSeq => "entry {$expectedSeq} is missing",
                $entry->prev_hash !== $prev => 'its link to the previous entry is broken',
                ! hash_equals($entry->hash, self::fingerprint($entry)) => 'its contents no longer match its fingerprint',
                default => null,
            };
            if ($problem) {
                return ['ok' => false, 'checked' => $checked, 'first_bad_seq' => $entry->seq, 'problem' => $problem, 'latest_hash' => $this->latestFingerprint()];
            }
            $prev = $entry->hash;
            $expectedSeq++;
            $checked++;
        }

        // Recomputing can't see the newest entries being deleted, or the whole chain being rebuilt;
        // the last checkpoint can. ponytail: the checkpoint lives in the same database, so someone
        // who edits it too still gets past this; the printed report fingerprint is the outside copy.
        $cp = DB::table('app_chain_checkpoints')->orderByDesc('created_at')->first();
        if ($cp) {
            $then = StockEntry::where('seq', $cp->latest_seq)->value('hash');
            $problem = match (true) {
                $then === null => "entries from {$cp->latest_seq} on are missing since the check on ".Carbon::parse($cp->created_at)->toDateString(),
                ! hash_equals($cp->latest_hash, $then) => 'its fingerprint differs from the one recorded on '.Carbon::parse($cp->created_at)->toDateString().' (the chain was rebuilt)',
                default => null,
            };
            if ($problem) {
                return ['ok' => false, 'checked' => $checked, 'first_bad_seq' => (int) $cp->latest_seq, 'problem' => $problem, 'latest_hash' => $prev];
            }
        }

        return ['ok' => true, 'checked' => $checked, 'first_bad_seq' => null, 'problem' => null, 'latest_hash' => $prev];
    }

    /** Records what a passing check saw, for the next check to compare against. */
    public function checkpoint(): void
    {
        $last = StockEntry::orderByDesc('seq')->first(['seq', 'hash']);
        if ($last) {
            DB::table('app_chain_checkpoints')->insert(['id' => (string) Str::uuid(), 'latest_seq' => $last->seq,
                'latest_hash' => $last->hash, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public static function fingerprint(StockEntry $e): string
    {
        $canonical = json_encode([
            'seq' => (int) $e->seq,
            'device_id' => (string) $e->device_id,
            'resource_type_id' => (string) $e->resource_type_id,
            'depot_id' => (string) $e->depot_id,
            'quantity' => (int) $e->quantity,
            'reason' => (string) $e->reason,
            'user_id' => $e->user_id,
            'device' => $e->device,
            'created_at_device' => $e->created_at_device->format('Y-m-d H:i:s'),
            'received_at' => $e->received_at->format('Y-m-d H:i:s'),
            'priority' => (bool) $e->priority,
            'depends_on_device_id' => $e->depends_on_device_id,
            'request_line_id' => $e->request_line_id,
            'borrow_id' => $e->borrow_id,
            'event_tag' => $e->event_tag,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash('sha256', $e->prev_hash . $canonical);
    }

    /** @return list<string> the conflict reasons raised for this entry */
    private function flagConflicts(StockEntry $entry): array
    {
        $flags = [];

        $level = $this->level($entry->resource_type_id, $entry->depot_id);
        if ($level < 0) {
            $flags['negative_stock'] = "Stock would be {$level} after this entry.";
        }

        if ($entry->reason === 'return' && $entry->borrow_id) {
            $issued = StockEntry::where('borrow_id', $entry->borrow_id)->where('seq', '<', $entry->seq)->where('reason', 'issue')->count();
            $returned = StockEntry::where('borrow_id', $entry->borrow_id)->where('seq', '<', $entry->seq)->where('reason', 'return')->count();
            if ($returned >= $issued) {
                $flags['return_not_issued'] = 'This item is not currently issued, so it cannot be returned again.';
            }
        }

        // Two devices changed the same item while this one was offline.
        $madeOffline = $entry->received_at->diffInSeconds($entry->created_at_device, true) > self::OFFLINE_AFTER_SECONDS;
        if ($madeOffline && StockEntry::where('resource_type_id', $entry->resource_type_id)
            ->where('depot_id', $entry->depot_id)
            ->where('seq', '<', $entry->seq)
            ->where('created_at_device', '>', $entry->created_at_device)
            ->where(fn ($q) => $q->whereNull('device')->orWhere('device', '!=', $entry->device ?? ''))
            ->exists()) {
            $flags['concurrent_offline_edit'] = 'Another device changed this item while this entry was waiting offline.';
        }

        foreach ($flags as $reason => $detail) {
            StockConflict::create(['stock_entry_id' => $entry->id, 'reason' => $reason, 'detail' => $detail]);
        }

        return array_keys($flags);
    }

    private static function toAppTime(?string $value): Carbon
    {
        return ($value ? Carbon::parse($value) : now())->setTimezone(config('app.timezone'))->startOfSecond();
    }
}
