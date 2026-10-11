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
import { cn, errorMessage as message, field, label } from "@/lib/utils";
import { PageHeader } from "@/Components/PageHeader";

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
  const [search, setSearch] = useState("");
  const shown = items.filter((i) => i.name.toLowerCase().includes(search.trim().toLowerCase()));

  return (
    <div>
      <PageHeader
        title="Stock"
        description="Current stock is the sum of every recorded entry. Entries save on this device first, so recording works offline."
        actions={(
          <span className="max-w-full truncate font-mono text-xs text-[var(--text-secondary)]" title={latestFingerprint}>
            Fingerprint {latestFingerprint.slice(0, 16)}…
          </span>
        )}
      />

      <div className="grid gap-6 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div className="min-w-0 space-y-6">
          {conflicts.length > 0 && <ConflictList conflicts={conflicts} itemName={itemName} depotName={depotName} canResolve={can.resolve} onDone={syncNow} />}

          <section className="panel">
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-line px-4 py-3">
              <h2 className="text-sm font-semibold">Current stock</h2>
              <input className={cn(field, "w-full sm:w-64")} type="search" placeholder="Filter items" aria-label="Filter items" value={search}
                onChange={(e) => setSearch(e.target.value)} />
            </div>
            <div className="table-scroll">
              <table className="w-full text-sm sm:min-w-[480px]">
                <thead className="text-left text-xs text-[var(--text-secondary)]">
                  <tr className="border-b border-line"><th className="px-4 py-2.5 font-medium">Item</th><th className="px-3 font-medium">By depot</th><th className="px-4 text-right font-medium">Total</th></tr>
                </thead>
                <tbody>
                  {shown.map((i) => (
                    <tr key={i.id} className="border-b border-line last:border-0">
                      <td className="px-4 py-2">{i.name}</td>
                      <td className="px-3 text-xs text-[var(--text-secondary)]">
                        {levels.filter((l) => l.resource_type_id === i.id).map((l) => `${depotName[l.depot_id]}: ${l.quantity}`).join(" · ") || "–"}
                      </td>
                      <td className={`whitespace-nowrap px-4 text-right ${total(i.id) < 0 ? "font-semibold text-critical" : total(i.id) === 0 ? "text-[var(--text-secondary)]" : "font-semibold"}`}>
                        {total(i.id)} <span className="text-xs font-normal text-[var(--text-secondary)]">{i.unit}</span>
                      </td>
                    </tr>
                  ))}
                  {shown.length === 0 && <tr><td colSpan={3} className="px-4 py-3 text-[var(--text-secondary)]">No item matches “{search}”.</td></tr>}
                </tbody>
              </table>
            </div>
          </section>
        </div>

        {/* Recording is the job on a phone: the forms come before the long stock list until the two-column layout. */}
        <aside className="order-first min-w-0 space-y-6 xl:order-none">
          {can.record && userId && <EntryForm items={items} depots={depots} userId={userId} onQueued={syncNow} />}
          {queued.length > 0 && (
            <section className="panel border-amber p-4 text-sm">
              <h2 className="mb-1 font-semibold text-[var(--amber)]">Waiting to sync on this device ({queued.length})</h2>
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

const itemUnit = (items: Props["items"], id: string) => items.find((i) => i.id === id)?.unit ?? "";

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
    <form onSubmit={submit} className="panel space-y-3 p-4">
      <h2 className="text-sm font-semibold">Record a stock entry</h2>
      <label className={label}>Item
        <select className={field} value={form.resource_type_id} onChange={(e) => setForm({ ...form, resource_type_id: e.target.value })}>
          {items.map((i) => <option key={i.id} value={i.id}>{i.name}</option>)}
        </select>
      </label>
      <label className={label}>Depot
        <select className={field} value={form.depot_id} onChange={(e) => setForm({ ...form, depot_id: e.target.value })}>
          {depots.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
        </select>
      </label>
      <div className="grid grid-cols-2 gap-2">
        <label className={label}>Movement
          <select className={field} value={form.movement} onChange={(e) => setForm({ ...form, movement: e.target.value })}>
            {MOVEMENTS.map((m) => <option key={m.value} value={m.value}>{m.label}</option>)}
          </select>
        </label>
        <label className={label}>Quantity
          <input className={field} type="number" min={1} required placeholder={itemUnit(items, form.resource_type_id)} value={form.quantity}
            onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
        </label>
      </div>
      <label className={label}>Event tag (optional)
        <input className={field} placeholder="e.g. Kristine" value={form.event_tag}
          onChange={(e) => setForm({ ...form, event_tag: e.target.value })} />
      </label>
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
    <section className="panel border-critical p-4" role="alert">
      <h2 className="mb-3 text-sm font-semibold text-critical">{conflicts.length} conflict{conflicts.length === 1 ? "" : "s"} to resolve</h2>
      <ul className="space-y-4 text-sm">
        {conflicts.map((c) => (
          <li key={c.id}>
            <p>
              <strong>Entry #{c.entry.seq}</strong>: {c.entry.quantity} {itemName[c.entry.resource_type_id]?.name} at {depotName[c.entry.depot_id]}
              {c.entry.device ? ` from ${c.entry.device}` : ""}. {c.detail}
            </p>
            {canResolve && (
              <div className="mt-2 flex flex-wrap gap-2">
                <input className={cn(field, "min-w-[200px] flex-1")} placeholder="What happened (required)" aria-label="What happened (required)" value={notes[c.id]?.note ?? ""}
                  onChange={(e) => setNotes({ ...notes, [c.id]: { ...(notes[c.id] ?? { correction: "" }), note: e.target.value } })} />
                <input className={cn(field, "w-32")} type="number" placeholder="Correction ±" aria-label="Correction (plus or minus)" value={notes[c.id]?.correction ?? ""}
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
    <form onSubmit={submit} className="panel space-y-3 p-4">
      <h2 className="text-sm font-semibold">Weekly physical count</h2>
      <label className={label}>Item counted
        <select className={field} value={form.resource_type_id} onChange={(e) => setForm({ ...form, resource_type_id: e.target.value })}>
          {items.map((i) => <option key={i.id} value={i.id}>{i.name}</option>)}
        </select>
      </label>
      <label className={label}>Depot
        <select className={field} value={form.depot_id} onChange={(e) => setForm({ ...form, depot_id: e.target.value })}>
          {depots.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
        </select>
      </label>
      <label className={label}>Counted quantity
        <input className={field} type="number" min={0} required placeholder={itemUnit(items, form.resource_type_id)} value={form.counted_quantity}
          onChange={(e) => setForm({ ...form, counted_quantity: e.target.value })} />
      </label>
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
    <section className="panel space-y-2 p-4 text-sm">
      <h2 className="font-semibold">Check records</h2>
      <p className="text-xs text-[var(--text-secondary)]">Recomputes every entry's fingerprint from the first one and reports the first that no longer matches.</p>
      <Button variant="ghost" onClick={check} disabled={busy} className="w-full">{busy ? "Checking…" : "Check records"}</Button>
      {result && (result.ok
        ? <p className="text-ok">Records intact: {result.checked} entries checked.</p>
        : <p className="text-[var(--critical)]">Check failed at entry #{result.first_bad_seq}: {result.problem}.</p>)}
    </section>
  );
}

StockIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
