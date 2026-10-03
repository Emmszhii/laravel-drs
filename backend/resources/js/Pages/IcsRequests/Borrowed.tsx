import { useState, type FormEvent } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { enqueueStock } from "@/Offline/outbox";
import { isNetworkError, syncOutbox } from "@/Offline/sync";
import useOfflineSync from "@/Hooks/useOfflineSync";
import type { OutboxItem } from "@/Offline/db";
import { IcsTabs, History, DivisionList, field, message, type Can, type StatusChange } from "./shared";

type Borrow = {
  id: string;
  resource_type_id: string;
  depot_id: string;
  resource_type: { id: string; name: string };
  property_number: string | null;
  borrower_division: string;
  location?: string | null;
  borrower_name?: string; // only sent to the Admin and Logistics
  borrower_contact?: string | null;
  issued_at: string;
  status: "issued" | "returned" | "for_repair" | "condemned" | "surrendered";
  history: StatusChange[];
  /** Set for a unit issued on this device that hasn't synced yet: the outbox entry a return must wait for. */
  pendingIssue?: string;
};
type Equipment = { id: string; name: string; unit: string };
type Props = { borrows: Borrow[]; equipment: Equipment[]; depots: { id: string; name: string }[]; divisions: string[]; can: Can };

// Mirrors RequestWorkflow::BORROW_TRANSITIONS; returned, condemned and surrendered are final.
const NEXT: Record<string, string[]> = {
  issued: ["returned", "for_repair", "condemned", "surrendered"],
  for_repair: ["returned", "condemned"],
};
const LABEL: Record<string, string> = {
  issued: "Issued", returned: "Returned", for_repair: "For repair", condemned: "Condemned (written off)", surrendered: "Surrendered",
};
const day = (iso: string) => new Date(iso).toLocaleDateString("en-PH");

/** Units issued offline on this device, shown like logbook rows until they sync. */
function pendingBorrows(queued: OutboxItem[]): Borrow[] {
  return queued.filter((q) => q.payload.reason === "issue").map((q) => {
    const p = q.payload as Record<string, string>;
    return {
      id: p.borrow_id, resource_type_id: p.resource_type_id, depot_id: p.depot_id, resource_type: { id: p.resource_type_id, name: p.label },
      property_number: null, borrower_division: p.borrower_division, location: p.location, borrower_name: p.borrower_name, borrower_contact: p.borrower_contact,
      issued_at: p.created_at_device, status: "issued", history: [], pendingIssue: q.clientRef,
    };
  });
}

/**
 * Borrowed-equipment logbook (system-spec.md §3.3). Semi-expendable property
 * under COA Circular 2022-004: issue, return and disposal are all documented.
 * Like the paper logbook it records item, quantity, location, the borrower's
 * division (often another city office) and name, the date out and the date back.
 * Equipment is lent here directly (walk-in, no request) or by releasing an ICS 213-RR line.
 * Borrower names and contacts appear only for the Admin and Logistics.
 * A return made with no connection is saved in this device's outbox and syncs
 * after the unit's issue, even when that issue was also made offline.
 */
export default function Borrowed({ borrows, equipment, depots, divisions, can }: Props) {
  const [notes, setNotes] = useState<Record<string, string>>({});
  // Only this user's queued entries (Offline/outbox.ts); the list refreshes itself on every outbox change.
  const { items: queued } = useOfflineSync();
  const [busy, setBusy] = useState<string | null>(null);

  const returning = new Set(queued.filter((q) => q.payload.reason === "return").map((q) => String(q.payload.borrow_id)));
  const rows = [...pendingBorrows(queued), ...borrows];

  const queueReturn = async (b: Borrow) => {
    await enqueueStock({
      resource_type_id: b.resource_type_id, depot_id: b.depot_id, quantity: 1, reason: "return",
      borrow_id: b.id, depends_on_device_id: b.pendingIssue ?? null,
    }, can.userId);
    toast.success(`${b.resource_type.name}: return saved on this device. It syncs when you're back online.`);
    await syncOutbox(can.userId);
  };

  const send = async (b: Borrow, status: string) => {
    if (b.pendingIssue) {
      return queueReturn(b); // not on the server yet; only a return can follow it offline
    }
    try {
      await http.post(`/api/borrows/${b.id}/status`, { status, note: notes[b.id] || null });
      toast.success(`${b.resource_type.name}: ${LABEL[status].toLowerCase()}.`);
    } catch (err) {
      if (status === "returned" && isNetworkError(err)) {
        return queueReturn(b);
      }
      toast.error(message(err));
    }
    router.reload({ only: ["borrows"] });
  };

  const move = async (b: Borrow, status: string) => {
    if (busy) return;
    setBusy(b.id);
    try {
      await send(b, status);
    } finally {
      setBusy(null);
    }
  };

  return (
    <div>
      <IcsTabs />
      <DivisionList divisions={divisions} />
      <div className="grid gap-6 p-6 xl:grid-cols-[1fr_360px]">
        <div>
          {rows.length === 0 && <p className="text-sm text-[var(--text-secondary)]">Nothing has been lent out yet.</p>}
          <ul className="divide-y divide-white/10 border border-white/10">
            {rows.map((b) => {
              const next = returning.has(b.id) ? undefined : b.pendingIssue ? ["returned"] : NEXT[b.status];
              const returnedAt = b.history.find((h) => h.to_status === "returned")?.created_at;
              return (
                <li key={b.id} className="space-y-2 px-4 py-3 text-sm">
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    <span>
                      <strong>{b.resource_type.name}</strong>{b.property_number ? ` · ${b.property_number}` : ""} · {b.borrower_division}
                      {b.location ? ` · at ${b.location}` : ""}
                      {b.borrower_name && <span className="text-[var(--text-secondary)]"> · {b.borrower_name}{b.borrower_contact ? `, ${b.borrower_contact}` : ""}</span>}
                    </span>
                    <span className="text-xs uppercase">
                      {returning.has(b.id) ? "Return waiting to sync" : LABEL[b.status]}
                      {b.pendingIssue && !returning.has(b.id) ? " · waiting to sync" : ""}
                    </span>
                  </div>
                  <div className="text-xs text-[var(--text-secondary)]">
                    Out {day(b.issued_at)}{returnedAt ? ` · Returned ${day(returnedAt)}` : ""}
                  </div>
                  {can.release && next && (
                    <div className="flex flex-wrap items-center gap-2">
                      {!b.pendingIssue && (
                        <input className={`${field} w-56`} placeholder="Note (optional)" aria-label={`Note for ${b.resource_type.name} (optional)`} value={notes[b.id] ?? ""} onChange={(e) => setNotes({ ...notes, [b.id]: e.target.value })} />
                      )}
                      {next.map((s) => (
                        <Button key={s} variant={s === "returned" ? "primary" : "ghost"} disabled={busy !== null} onClick={() => move(b, s)}>{LABEL[s]}</Button>
                      ))}
                    </div>
                  )}
                  <History items={b.history} />
                </li>
              );
            })}
          </ul>
        </div>
        {can.release && <LendForm equipment={equipment} depots={depots} userId={can.userId} onDone={() => router.reload({ only: ["borrows"] })} />}
      </div>
    </div>
  );
}

/**
 * A walk-in loan with no ICS 213-RR behind it, to any office. Online it goes straight to the
 * server; with no connection each unit is saved as an issue entry on this device and syncs later.
 */
function LendForm({ equipment, depots, userId, onDone }: { equipment: Equipment[]; depots: Props["depots"]; userId: string; onDone: () => void }) {
  const blank = { borrower_division: "", borrower_name: "", borrower_contact: "", location: "" };
  const [form, setForm] = useState({ resource_type_id: equipment[0]?.id ?? "", depot_id: depots[0]?.id ?? "", quantity: "1", ...blank });
  const [busy, setBusy] = useState(false);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    const quantity = Math.max(1, Number(form.quantity) || 1);
    const body = { ...form, quantity, borrower_contact: form.borrower_contact.trim() || null, location: form.location.trim() || null };
    try {
      await http.post("/api/borrows", body);
      toast.success("Lent out. Stock entry recorded.");
      setForm({ ...form, quantity: "1", ...blank });
    } catch (err) {
      if (!isNetworkError(err)) {
        toast.error(message(err));
      } else {
        const label = equipment.find((i) => i.id === form.resource_type_id)?.name;
        for (let i = 0; i < quantity; i++) {
          await enqueueStock({
            resource_type_id: body.resource_type_id, depot_id: body.depot_id, quantity: -1, reason: "issue", borrow_id: crypto.randomUUID(), label,
            borrower_name: body.borrower_name, borrower_contact: body.borrower_contact, borrower_division: body.borrower_division, location: body.location,
          }, userId);
        }
        await syncOutbox(userId);
        setForm({ ...form, quantity: "1", ...blank });
        toast.success("Saved on this device. The loan syncs when you're back online.");
      }
    } finally {
      setBusy(false);
      onDone();
    }
  };

  if (equipment.length === 0) {
    return <p className="h-fit border border-white/10 p-4 text-sm text-[var(--text-secondary)]">No borrowable equipment in the catalog yet. Add an item and uncheck "Consumable".</p>;
  }

  return (
    <form onSubmit={submit} className="h-fit space-y-3 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">Lend equipment</h2>
      <p className="text-xs text-[var(--text-secondary)]">For a walk-in loan with no request, like a tool lent to another office.</p>
      <div className="flex gap-2">
        <select className={field} aria-label="Equipment" value={form.resource_type_id} onChange={(e) => setForm({ ...form, resource_type_id: e.target.value })}>
          {equipment.map((i) => <option key={i.id} value={i.id} className="bg-black">{i.name}</option>)}
        </select>
        <input className={`${field} w-20`} type="number" min={1} max={100} required aria-label="Quantity" value={form.quantity} onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
      </div>
      <select className={field} aria-label="Lend from depot" value={form.depot_id} onChange={(e) => setForm({ ...form, depot_id: e.target.value })}>
        {depots.map((d) => <option key={d.id} value={d.id} className="bg-black">From {d.name}</option>)}
      </select>
      <input className={field} list="divisions" required placeholder="Borrower's division / office, e.g. CGSO" aria-label="Borrower's division or office" value={form.borrower_division}
        onChange={(e) => setForm({ ...form, borrower_division: e.target.value })} />
      <div className="grid grid-cols-2 gap-2">
        <input className={field} required placeholder="Borrower's name" aria-label="Borrower's name" value={form.borrower_name} onChange={(e) => setForm({ ...form, borrower_name: e.target.value })} />
        <input className={field} placeholder="Contact (optional)" aria-label="Borrower's contact" value={form.borrower_contact} onChange={(e) => setForm({ ...form, borrower_contact: e.target.value })} />
      </div>
      <input className={field} placeholder="Location of use (optional)" aria-label="Location of use" value={form.location} onChange={(e) => setForm({ ...form, location: e.target.value })} />
      <Button type="submit" disabled={busy} className="w-full">{busy ? "Saving…" : "Lend out"}</Button>
    </form>
  );
}

Borrowed.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
