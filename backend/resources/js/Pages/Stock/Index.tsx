import { useCallback, useEffect, useMemo, useState, type FormEvent } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { enqueueStock, markPending, remove } from "@/Offline/outbox";
import { syncOutbox } from "@/Offline/sync";
import useOfflineSync from "@/Hooks/useOfflineSync";
import { useAsyncAction } from "@/Hooks/use-async-action";
import { errorMessage as message, field } from "@/lib/utils";

type Named = { id: string; name: string };
type Level = { resource_type_id: string; depot_id: string; quantity: number | string };
type Conflict = {
  id: string;
  reason: string;
  detail: string;
  entry: { seq: number; resource_type_id: string; depot_id: string; quantity: number; reason: string; device: string | null; created_at_device: string };
};
type Props = {
  items: (Named & { unit: string })[];
  depots: Named[];
  levels: Level[];
  conflicts: Conflict[];
  latestFingerprint: string;
  can: { record: boolean; resolve: boolean };
};

// Movement kinds a stock keeper records directly; the sign is applied here.
const MOVEMENTS = [
  { value: "receipt", label: "Receipt (items in)", sign: 1 },
  { value: "release", label: "Release (items out)", sign: -1 },
  { value: "adjustment_in", label: "Adjustment +", sign: 1 },
  { value: "adjustment_out", label: "Adjustment −", sign: -1 },
] as const;

/** Plain words for a queued entry's reason. */
const REASON_LABEL: Record<string, string> = {
  receipt: "receipt", release: "release", adjustment: "adjustment", issue: "borrowed item issued", return: "borrowed item returned",
};

/**
 * Stock records (system-spec.md §5). Every entry is written to this device's
 * outbox first, with a device-made ID, then synced; it survives closing the
 * browser and going offline. The server keeps a SHA-256 chain of all entries,
 * and "check records" recomputes it.
 */
export default function StockIndex({ items, depots, levels, conflicts, latestFingerprint, can }: Props) {
  const itemName = useMemo(() => Object.fromEntries(items.map((i) => [i.id, i])), [items]);
  const depotName = useMemo(() => Object.fromEntries(depots.map((d) => [d.id, d.name])), [depots]);
  const { userId, items: queued, needsSignIn, outcome } = useOfflineSync();

  // Tried even while offline: the failed upload is what hands the batch to Background Sync.
  const syncNow = useCallback(async () => {
    await syncOutbox(userId);
    if (navigator.onLine) {
      router.reload({ only: ["levels", "conflicts", "latestFingerprint"] });
    }
  }, [userId]);

  useEffect(() => {
    window.addEventListener("online", syncNow);

    return () => window.removeEventListener("online", syncNow);
  }, [syncNow]);

  const retry = async (clientRef: string) => {
    await markPending(clientRef);
    await syncNow();
  };

  const discard = async (clientRef: string) => {
    if (window.confirm("Discard this entry? It was never recorded on the server and can't be recovered.")) {
      await remove(clientRef);
    }
  };

  const total = (typeId: string) => levels.filter((l) => l.resource_type_id === typeId).reduce((s, l) => s + Number(l.quantity), 0);

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6 py-3">
        <h1 className="text-lg font-semibold uppercase">Stock</h1>
        <span className="max-w-[50%] truncate font-mono text-xs text-[var(--text-secondary)]" title={latestFingerprint}>
          Latest fingerprint {latestFingerprint.slice(0, 16)}…
        </span>
      </div>

      <div className="grid gap-6 p-6 xl:grid-cols-[1fr_340px]">
        <div className="space-y-6">
          {conflicts.length > 0 && <ConflictList conflicts={conflicts} itemName={itemName} depotName={depotName} canResolve={can.resolve} onDone={syncNow} />}

          <section>
            <h2 className="mb-2 text-sm font-semibold uppercase">Current stock (sum of entries)</h2>
            <table className="w-full text-sm">
              <thead className="text-left text-xs uppercase text-[var(--text-secondary)]">
                <tr className="border-b border-white/10"><th className="py-2">Item</th><th>By depot</th><th className="text-right">Total</th></tr>
              </thead>
              <tbody>
                {items.map((i) => (
                  <tr key={i.id} className="border-b border-white/5">
                    <td className="py-2">{i.name}</td>
                    <td className="text-xs text-[var(--text-secondary)]">
                      {levels.filter((l) => l.resource_type_id === i.id).map((l) => `${depotName[l.depot_id]}: ${l.quantity}`).join(" · ") || "–"}
                    </td>
                    <td className={`text-right font-semibold ${total(i.id) < 0 ? "text-[var(--critical)]" : ""}`}>{total(i.id)} {i.unit}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </section>
        </div>

        <aside className="space-y-6">
          {can.record && userId && <EntryForm items={items} depots={depots} userId={userId} onQueued={syncNow} />}
          {queued.length > 0 && (
            <section className="border border-[var(--amber)]/40 p-4 text-sm">
              <h2 className="mb-1 font-semibold uppercase text-[var(--amber)]">Waiting to sync on this device ({queued.length})</h2>
              <p className="mb-2 text-xs text-[var(--text-secondary)]">Saved here; they upload as soon as the connection returns.</p>
              {needsSignIn && (
                <p role="alert" className="mb-2 text-xs font-semibold text-[var(--amber)]">
                  Your session has ended. <a href="/login" className="underline">Sign in again</a> to sync these entries.
                </p>
              )}
              {outcome === "forbidden" && (
                <p role="alert" className="mb-2 text-xs text-[var(--critical)]">Your account can't record stock right now; these stay on this device.</p>
              )}
              <ul className="space-y-2 text-xs">
                {queued.map((q) => (
                  <li key={q.clientRef}>
                    {String(q.payload.quantity)} {itemName[String(q.payload.resource_type_id)]?.name}
                    {" · "}{REASON_LABEL[String(q.payload.reason)] ?? String(q.payload.reason)}
                    {q.payload.priority ? " · priority" : ""}
                    {q.status === "failed" && (
                      <>
                        <span className="text-[var(--critical)]"> · failed: {q.error}</span>
                        <span className="mt-1 flex gap-3">
                          <button type="button" className="underline hover:text-[var(--amber)]" onClick={() => retry(q.clientRef)}>Retry</button>
                          <button type="button" className="underline hover:text-[var(--critical)]" onClick={() => discard(q.clientRef)}>Discard</button>
                        </span>
                      </>
                    )}
                  </li>
                ))}
              </ul>
            </section>
          )}
          {can.resolve && <CountForm items={items} depots={depots} />}
          {can.record && <CheckRecords />}
        </aside>
      </div>
    </div>
  );
}

function EntryForm({ items, depots, userId, onQueued }: { items: Props["items"]; depots: Named[]; userId: string; onQueued: () => void }) {
  const [form, setForm] = useState({ resource_type_id: items[0]?.id ?? "", depot_id: depots[0]?.id ?? "", movement: "receipt", quantity: "", priority: false, event_tag: "" });

  const save = useAsyncAction(async () => {
    const movement = MOVEMENTS.find((m) => m.value === form.movement)!;
    await enqueueStock({
      resource_type_id: form.resource_type_id,
      depot_id: form.depot_id,
      quantity: movement.sign * Number(form.quantity),
      reason: movement.value.startsWith("adjustment") ? "adjustment" : movement.value,
      priority: form.priority,
      event_tag: form.event_tag || null,
    }, userId);
    toast.success(navigator.onLine ? "Entry saved." : "Entry saved on this device. It will sync when you're back online.");
    setForm({ ...form, quantity: "", event_tag: "" });
    onQueued();
  });
  const submit = (e: FormEvent) => {
    e.preventDefault();
    save.run();
  };

  return (
    <form onSubmit={submit} className="space-y-3 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">Record a stock entry</h2>
      <select className={field} aria-label="Item" value={form.resource_type_id} onChange={(e) => setForm({ ...form, resource_type_id: e.target.value })}>
        {items.map((i) => <option key={i.id} value={i.id} className="bg-black">{i.name}</option>)}
      </select>
      <select className={field} aria-label="Depot" value={form.depot_id} onChange={(e) => setForm({ ...form, depot_id: e.target.value })}>
        {depots.map((d) => <option key={d.id} value={d.id} className="bg-black">{d.name}</option>)}
      </select>
      <div className="grid grid-cols-2 gap-2">
        <select className={field} aria-label="Movement" value={form.movement} onChange={(e) => setForm({ ...form, movement: e.target.value })}>
          {MOVEMENTS.map((m) => <option key={m.value} value={m.value} className="bg-black">{m.label}</option>)}
        </select>
        <input className={field} type="number" min={1} required placeholder="Quantity" aria-label="Quantity" value={form.quantity}
          onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
      </div>
      <input className={field} placeholder="Event tag (optional, e.g. Kristine)" aria-label="Event tag (optional)" value={form.event_tag}
        onChange={(e) => setForm({ ...form, event_tag: e.target.value })} />
      <label className="flex items-center gap-2 text-sm">
        <input type="checkbox" checked={form.priority} onChange={(e) => setForm({ ...form, priority: e.target.checked })} />
        Priority (syncs first)
      </label>
      <Button type="submit" disabled={save.loading} className="w-full">{save.loading ? "Saving…" : "Save entry"}</Button>
    </form>
  );
}

function ConflictList({ conflicts, itemName, depotName, canResolve, onDone }: {
  conflicts: Conflict[];
  itemName: Record<string, Named>;
  depotName: Record<string, string>;
  canResolve: boolean;
  onDone: () => void;
}) {
  const [notes, setNotes] = useState<Record<string, { note: string; correction: string }>>({});
  const [busy, setBusy] = useState<string | null>(null);

  const resolve = async (c: Conflict) => {
    if (busy) return;
    setBusy(c.id);
    const n = notes[c.id] ?? { note: "", correction: "" };
    try {
      await http.post(`/api/stock/conflicts/${c.id}/resolve`, { note: n.note, correction: n.correction ? Number(n.correction) : null });
      toast.success("Conflict resolved.");
    } catch (err) {
      toast.error(message(err));
    }
    setBusy(null);
    onDone();
  };

  return (
    <section className="border border-[var(--critical)]/50 p-4">
      <h2 className="mb-3 text-sm font-semibold uppercase text-[var(--critical)]">Conflicts to resolve ({conflicts.length})</h2>
      <ul className="space-y-4 text-sm">
        {conflicts.map((c) => (
          <li key={c.id}>
            <p>
              <strong>Entry #{c.entry.seq}</strong>: {c.entry.quantity} {itemName[c.entry.resource_type_id]?.name} at {depotName[c.entry.depot_id]}
              {c.entry.device ? ` from ${c.entry.device}` : ""}. {c.detail}
            </p>
            {canResolve && (
              <div className="mt-2 flex flex-wrap gap-2">
                <input className={`${field} min-w-[200px] flex-1`} placeholder="What happened (required)" aria-label="What happened (required)" value={notes[c.id]?.note ?? ""}
                  onChange={(e) => setNotes({ ...notes, [c.id]: { ...(notes[c.id] ?? { correction: "" }), note: e.target.value } })} />
                <input className={`${field} w-32`} type="number" placeholder="Correction ±" aria-label="Correction (plus or minus)" value={notes[c.id]?.correction ?? ""}
                  onChange={(e) => setNotes({ ...notes, [c.id]: { ...(notes[c.id] ?? { note: "" }), correction: e.target.value } })} />
                <Button variant="ghost" disabled={!notes[c.id]?.note?.trim() || busy !== null} onClick={() => resolve(c)}>{busy === c.id ? "Resolving…" : "Resolve"}</Button>
              </div>
            )}
          </li>
        ))}
      </ul>
    </section>
  );
}

function CountForm({ items, depots }: { items: Props["items"]; depots: Named[] }) {
  const [form, setForm] = useState({ resource_type_id: items[0]?.id ?? "", depot_id: depots[0]?.id ?? "", counted_quantity: "" });
  const [busy, setBusy] = useState(false);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    if (busy) return;
    setBusy(true);
    try {
      await http.post("/api/stock/counts", { ...form, counted_quantity: Number(form.counted_quantity) });
      toast.success("Count recorded. A difference from recorded stock is flagged before the next solver run.");
      setForm({ ...form, counted_quantity: "" });
    } catch (err) {
      toast.error(message(err));
    } finally {
      setBusy(false);
    }
  };

  return (
    <form onSubmit={submit} className="space-y-3 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">Weekly physical count</h2>
      <select className={field} aria-label="Item counted" value={form.resource_type_id} onChange={(e) => setForm({ ...form, resource_type_id: e.target.value })}>
        {items.map((i) => <option key={i.id} value={i.id} className="bg-black">{i.name}</option>)}
      </select>
      <select className={field} aria-label="Depot counted" value={form.depot_id} onChange={(e) => setForm({ ...form, depot_id: e.target.value })}>
        {depots.map((d) => <option key={d.id} value={d.id} className="bg-black">{d.name}</option>)}
      </select>
      <input className={field} type="number" min={0} required placeholder="Counted quantity" aria-label="Counted quantity" value={form.counted_quantity}
        onChange={(e) => setForm({ ...form, counted_quantity: e.target.value })} />
      <Button type="submit" variant="ghost" disabled={busy} className="w-full">{busy ? "Recording…" : "Record count"}</Button>
    </form>
  );
}

function CheckRecords() {
  const [result, setResult] = useState<{ ok: boolean; checked: number; first_bad_seq: number | null; problem: string | null } | null>(null);
  const [busy, setBusy] = useState(false);

  const check = async () => {
    setBusy(true);
    try {
      setResult((await http.get("/api/stock/verify")).data);
    } catch (err) {
      toast.error(message(err));
    } finally {
      setBusy(false);
    }
  };

  return (
    <section className="space-y-2 border border-white/10 p-4 text-sm">
      <h2 className="font-semibold uppercase">Check records</h2>
      <p className="text-xs text-[var(--text-secondary)]">Recomputes every entry's fingerprint from the first one and reports the first that no longer matches.</p>
      <Button variant="ghost" onClick={check} disabled={busy} className="w-full">{busy ? "Checking…" : "Check records"}</Button>
      {result && (result.ok
        ? <p className="text-emerald-400">Records intact: {result.checked} entries checked.</p>
        : <p className="text-[var(--critical)]">Check failed at entry #{result.first_bad_seq}: {result.problem}.</p>)}
    </section>
  );
}

StockIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
