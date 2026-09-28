import { useCallback, useEffect, useMemo, useState, type FormEvent } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http, ApiError } from "@/api-service";
import { enqueueStock, listAll } from "@/Offline/outbox";
import { syncOutbox } from "@/Offline/sync";
import type { OutboxItem } from "@/Offline/db";

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

const field = "w-full border border-white/20 bg-transparent px-2 py-1.5 text-sm";

function message(err: unknown) {
  return err instanceof ApiError ? err.message : "Something went wrong. Try again.";
}

/**
 * Stock records (system-spec.md §5). Every entry is written to this device's
 * outbox first, with a device-made ID, then synced; it survives closing the
 * browser and going offline. The server keeps a SHA-256 chain of all entries,
 * and "check records" recomputes it.
 */
export default function StockIndex({ items, depots, levels, conflicts, latestFingerprint, can }: Props) {
  const itemName = useMemo(() => Object.fromEntries(items.map((i) => [i.id, i])), [items]);
  const depotName = useMemo(() => Object.fromEntries(depots.map((d) => [d.id, d.name])), [depots]);
  const [queued, setQueued] = useState<OutboxItem[]>([]);

  const refreshQueue = useCallback(async () => {
    setQueued((await listAll()).filter((i) => i.kind === "stock"));
  }, []);

  const syncNow = useCallback(async () => {
    if (navigator.onLine) {
      await syncOutbox();
      router.reload({ only: ["levels", "conflicts", "latestFingerprint"] });
    }
    await refreshQueue();
  }, [refreshQueue]);

  useEffect(() => {
    refreshQueue();
    window.addEventListener("online", syncNow);

    return () => window.removeEventListener("online", syncNow);
  }, [refreshQueue, syncNow]);

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
          {can.record && <EntryForm items={items} depots={depots} onQueued={syncNow} />}
          {queued.length > 0 && (
            <section className="border border-[var(--amber)]/40 p-4 text-sm">
              <h2 className="mb-1 font-semibold uppercase text-[var(--amber)]">Waiting to sync on this device ({queued.length})</h2>
              <p className="mb-2 text-xs text-[var(--text-secondary)]">Saved here; they upload as soon as the connection returns.</p>
              <ul className="space-y-1 text-xs">
                {queued.map((q) => (
                  <li key={q.clientRef}>
                    {String(q.payload.quantity)} {itemName[String(q.payload.resource_type_id)]?.name}
                    {q.payload.priority ? " · priority" : ""}{q.status === "failed" ? ` · failed: ${q.error}` : ""}
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

function EntryForm({ items, depots, onQueued }: { items: Props["items"]; depots: Named[]; onQueued: () => void }) {
  const [form, setForm] = useState({ resource_type_id: items[0]?.id ?? "", depot_id: depots[0]?.id ?? "", movement: "receipt", quantity: "", priority: false, event_tag: "" });

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    const movement = MOVEMENTS.find((m) => m.value === form.movement)!;
    await enqueueStock({
      resource_type_id: form.resource_type_id,
      depot_id: form.depot_id,
      quantity: movement.sign * Number(form.quantity),
      reason: movement.value.startsWith("adjustment") ? "adjustment" : movement.value,
      priority: form.priority,
      event_tag: form.event_tag || null,
    });
    toast.success(navigator.onLine ? "Entry saved." : "Entry saved on this device. It will sync when you're back online.");
    setForm({ ...form, quantity: "", event_tag: "" });
    onQueued();
  };

  return (
    <form onSubmit={submit} className="space-y-3 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">Record a stock entry</h2>
      <select className={field} value={form.resource_type_id} onChange={(e) => setForm({ ...form, resource_type_id: e.target.value })}>
        {items.map((i) => <option key={i.id} value={i.id} className="bg-black">{i.name}</option>)}
      </select>
      <select className={field} value={form.depot_id} onChange={(e) => setForm({ ...form, depot_id: e.target.value })}>
        {depots.map((d) => <option key={d.id} value={d.id} className="bg-black">{d.name}</option>)}
      </select>
      <div className="grid grid-cols-2 gap-2">
        <select className={field} value={form.movement} onChange={(e) => setForm({ ...form, movement: e.target.value })}>
          {MOVEMENTS.map((m) => <option key={m.value} value={m.value} className="bg-black">{m.label}</option>)}
        </select>
        <input className={field} type="number" min={1} required placeholder="Quantity" value={form.quantity}
          onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
      </div>
      <input className={field} placeholder="Event tag (optional, e.g. Kristine)" value={form.event_tag}
        onChange={(e) => setForm({ ...form, event_tag: e.target.value })} />
      <label className="flex items-center gap-2 text-sm">
        <input type="checkbox" checked={form.priority} onChange={(e) => setForm({ ...form, priority: e.target.checked })} />
        Priority (syncs first)
      </label>
      <Button type="submit" className="w-full">Save entry</Button>
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

  const resolve = async (c: Conflict) => {
    const n = notes[c.id] ?? { note: "", correction: "" };
    try {
      await http.post(`/api/stock/conflicts/${c.id}/resolve`, { note: n.note, correction: n.correction ? Number(n.correction) : null });
      toast.success("Conflict resolved.");
    } catch (err) {
      toast.error(message(err));
    }
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
                <input className={`${field} min-w-[200px] flex-1`} placeholder="What happened (required)" value={notes[c.id]?.note ?? ""}
                  onChange={(e) => setNotes({ ...notes, [c.id]: { ...(notes[c.id] ?? { correction: "" }), note: e.target.value } })} />
                <input className={`${field} w-32`} type="number" placeholder="Correction ±" value={notes[c.id]?.correction ?? ""}
                  onChange={(e) => setNotes({ ...notes, [c.id]: { ...(notes[c.id] ?? { note: "" }), correction: e.target.value } })} />
                <Button variant="ghost" disabled={!notes[c.id]?.note?.trim()} onClick={() => resolve(c)}>Resolve</Button>
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

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    try {
      await http.post("/api/stock/counts", { ...form, counted_quantity: Number(form.counted_quantity) });
      toast.success("Count recorded. A difference from recorded stock is flagged before the next solver run.");
      setForm({ ...form, counted_quantity: "" });
    } catch (err) {
      toast.error(message(err));
    }
  };

  return (
    <form onSubmit={submit} className="space-y-3 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">Weekly physical count</h2>
      <select className={field} value={form.resource_type_id} onChange={(e) => setForm({ ...form, resource_type_id: e.target.value })}>
        {items.map((i) => <option key={i.id} value={i.id} className="bg-black">{i.name}</option>)}
      </select>
      <select className={field} value={form.depot_id} onChange={(e) => setForm({ ...form, depot_id: e.target.value })}>
        {depots.map((d) => <option key={d.id} value={d.id} className="bg-black">{d.name}</option>)}
      </select>
      <input className={field} type="number" min={0} required placeholder="Counted quantity" value={form.counted_quantity}
        onChange={(e) => setForm({ ...form, counted_quantity: e.target.value })} />
      <Button type="submit" variant="ghost" className="w-full">Record count</Button>
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
