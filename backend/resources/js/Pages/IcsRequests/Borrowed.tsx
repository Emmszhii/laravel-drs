import { useState } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { IcsTabs, History, field, message, type Can, type StatusChange } from "./shared";

type Borrow = {
  id: string;
  resource_type: { id: string; name: string };
  property_number: string | null;
  borrower_division: string;
  borrower_name?: string; // only sent to the Admin and Logistics
  borrower_contact?: string | null;
  issued_at: string;
  status: "issued" | "returned" | "for_repair" | "condemned" | "surrendered";
  history: StatusChange[];
};

// Mirrors RequestWorkflow::BORROW_TRANSITIONS; returned, condemned and surrendered are final.
const NEXT: Record<string, string[]> = {
  issued: ["returned", "for_repair", "condemned", "surrendered"],
  for_repair: ["returned", "condemned"],
};
const LABEL: Record<string, string> = {
  issued: "Issued", returned: "Returned", for_repair: "For repair", condemned: "Condemned (written off)", surrendered: "Surrendered",
};

/**
 * Borrowed-equipment logbook (system-spec.md §3.3). Semi-expendable property
 * under COA Circular 2022-004: issue, return and disposal are all documented.
 * Borrower names and contacts appear only for the Admin and Logistics.
 */
export default function Borrowed({ borrows, can }: { borrows: Borrow[]; can: Can }) {
  const [notes, setNotes] = useState<Record<string, string>>({});

  const move = async (b: Borrow, status: string) => {
    try {
      await http.post(`/api/borrows/${b.id}/status`, { status, note: notes[b.id] || null });
      toast.success(`${b.resource_type.name}: ${LABEL[status].toLowerCase()}.`);
    } catch (err) {
      toast.error(message(err));
    }
    router.reload({ only: ["borrows"] });
  };

  return (
    <div>
      <IcsTabs />
      <div className="p-6">
        {borrows.length === 0 && <p className="text-sm text-[var(--text-secondary)]">Nothing has been lent out yet.</p>}
        <ul className="divide-y divide-white/10 border border-white/10">
          {borrows.map((b) => (
            <li key={b.id} className="space-y-2 px-4 py-3 text-sm">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <span>
                  <strong>{b.resource_type.name}</strong>{b.property_number ? ` · ${b.property_number}` : ""} · {b.borrower_division}
                  {b.borrower_name && <span className="text-[var(--text-secondary)]"> · {b.borrower_name}{b.borrower_contact ? `, ${b.borrower_contact}` : ""}</span>}
                </span>
                <span className="text-xs uppercase">{LABEL[b.status]}</span>
              </div>
              {can.release && NEXT[b.status] && (
                <div className="flex flex-wrap items-center gap-2">
                  <input className={`${field} w-56`} placeholder="Note (optional)" value={notes[b.id] ?? ""} onChange={(e) => setNotes({ ...notes, [b.id]: e.target.value })} />
                  {NEXT[b.status].map((s) => (
                    <Button key={s} variant={s === "returned" ? "primary" : "ghost"} onClick={() => move(b, s)}>{LABEL[s]}</Button>
                  ))}
                </div>
              )}
              <History items={b.history} />
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}

Borrowed.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
