import { useCallback, useEffect, useState } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { enqueueStock, listAll } from "@/Offline/outbox";
import { isNetworkError, syncOutbox } from "@/Offline/sync";
import type { OutboxItem } from "@/Offline/db";
import { IcsTabs, History, field, message, type Can, type StatusChange } from "./shared";

type Borrow = {
  id: string;
  resource_type_id: string;
  depot_id: string;
  resource_type: { id: string; name: string };
  property_number: string | null;
  borrower_division: string;
  borrower_name?: string; // only sent to the Admin and Logistics
  borrower_contact?: string | null;
  issued_at: string;
  status: "issued" | "returned" | "for_repair" | "condemned" | "surrendered";
  history: StatusChange[];
  /** Set for a unit issued on this device that hasn't synced yet: the outbox entry a return must wait for. */
  pendingIssue?: string;
};

// Mirrors RequestWorkflow::BORROW_TRANSITIONS; returned, condemned and surrendered are final.
const NEXT: Record<string, string[]> = {
  issued: ["returned", "for_repair", "condemned", "surrendered"],
  for_repair: ["returned", "condemned"],
};
const LABEL: Record<string, string> = {
  issued: "Issued", returned: "Returned", for_repair: "For repair", condemned: "Condemned (written off)", surrendered: "Surrendered",
};

/** Units issued offline on this device, shown like logbook rows until they sync. */
function pendingBorrows(queued: OutboxItem[]): Borrow[] {
  return queued.filter((q) => q.payload.reason === "issue").map((q) => {
    const p = q.payload as Record<string, string>;
    return {
      id: p.borrow_id, resource_type_id: p.resource_type_id, depot_id: p.depot_id, resource_type: { id: p.resource_type_id, name: p.label },
      property_number: null, borrower_division: p.borrower_division, borrower_name: p.borrower_name, borrower_contact: p.borrower_contact,
      issued_at: p.created_at_device, status: "issued", history: [], pendingIssue: q.clientRef,
    };
  });
}

/**
 * Borrowed-equipment logbook (system-spec.md §3.3). Semi-expendable property
 * under COA Circular 2022-004: issue, return and disposal are all documented.
 * Borrower names and contacts appear only for the Admin and Logistics.
 * A return made with no connection is saved in this device's outbox and syncs
 * after the unit's issue, even when that issue was also made offline.
 */
export default function Borrowed({ borrows, can }: { borrows: Borrow[]; can: Can }) {
  const [notes, setNotes] = useState<Record<string, string>>({});
  const [queued, setQueued] = useState<OutboxItem[]>([]);
  const refreshQueue = useCallback(async () => setQueued(await listAll()), []);
  useEffect(() => {
    refreshQueue();
  }, [refreshQueue]);

  const returning = new Set(queued.filter((q) => q.payload.reason === "return").map((q) => String(q.payload.borrow_id)));
  const rows = [...pendingBorrows(queued), ...borrows];

  const queueReturn = async (b: Borrow) => {
    await enqueueStock({
      resource_type_id: b.resource_type_id, depot_id: b.depot_id, quantity: 1, reason: "return",
      borrow_id: b.id, depends_on_device_id: b.pendingIssue ?? null,
    });
    toast.success(`${b.resource_type.name}: return saved on this device. It syncs when you're back online.`);
    await syncOutbox();
    await refreshQueue();
  };

  const move = async (b: Borrow, status: string) => {
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

  return (
    <div>
      <IcsTabs />
      <div className="p-6">
        {rows.length === 0 && <p className="text-sm text-[var(--text-secondary)]">Nothing has been lent out yet.</p>}
        <ul className="divide-y divide-white/10 border border-white/10">
          {rows.map((b) => {
            const next = returning.has(b.id) ? undefined : b.pendingIssue ? ["returned"] : NEXT[b.status];
            return (
              <li key={b.id} className="space-y-2 px-4 py-3 text-sm">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <span>
                    <strong>{b.resource_type.name}</strong>{b.property_number ? ` · ${b.property_number}` : ""} · {b.borrower_division}
                    {b.borrower_name && <span className="text-[var(--text-secondary)]"> · {b.borrower_name}{b.borrower_contact ? `, ${b.borrower_contact}` : ""}</span>}
                  </span>
                  <span className="text-xs uppercase">
                    {returning.has(b.id) ? "Return waiting to sync" : LABEL[b.status]}
                    {b.pendingIssue && !returning.has(b.id) ? " · waiting to sync" : ""}
                  </span>
                </div>
                {can.release && next && (
                  <div className="flex flex-wrap items-center gap-2">
                    {!b.pendingIssue && (
                      <input className={`${field} w-56`} placeholder="Note (optional)" value={notes[b.id] ?? ""} onChange={(e) => setNotes({ ...notes, [b.id]: e.target.value })} />
                    )}
                    {next.map((s) => (
                      <Button key={s} variant={s === "returned" ? "primary" : "ghost"} onClick={() => move(b, s)}>{LABEL[s]}</Button>
                    ))}
                  </div>
                )}
                <History items={b.history} />
              </li>
            );
          })}
        </ul>
      </div>
    </div>
  );
}

Borrowed.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
