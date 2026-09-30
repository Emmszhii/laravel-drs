import { useMemo, useState, type FormEvent } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { enqueueStock } from "@/Offline/outbox";
import { isNetworkError, syncOutbox } from "@/Offline/sync";
import { IcsTabs, History, field, message, type Can, type StatusChange } from "./shared";

type Item = { id: string; name: string; unit: string; is_consumable: boolean };
type Line = {
  id: string;
  resource_type_id: string;
  resource_type: Item;
  depot_id: string | null;
  quantity_requested: number;
  quantity_approved: number | null;
  quantity_released: number;
};
type IcsRequest = {
  id: string;
  requesting_division: string;
  requested_by: string | null;
  requester?: { first_name: string; last_name: string } | null;
  date: string;
  purpose: "team_kit" | "relief" | "office_use";
  event_tag: string | null;
  status: string;
  decision_reason: string | null;
  lines: Line[];
  history: StatusChange[];
};
type Props = {
  requests: IcsRequest[];
  items: Item[];
  depots: { id: string; name: string }[];
  available: { resource_type_id: string; depot_id: string; available: number }[];
  can: Can;
};

const PURPOSE: Record<string, string> = { team_kit: "Team kit", relief: "Relief", office_use: "Office use" };
const DIVISIONS = ["ERT", "Operations", "Incident Management", "Logistics", "Research and Planning", "Admin and Training"];
const STATUS_STYLE: Record<string, string> = {
  submitted: "text-[var(--amber)]",
  approved: "text-sky-300",
  partially_released: "text-sky-300",
  released: "text-emerald-400",
  closed: "text-emerald-400",
  rejected: "text-[var(--critical)]",
  cancelled: "text-[var(--text-secondary)]",
};

/**
 * ICS 213-RR requests (system-spec.md §3.2, §6). Requested and released
 * quantities are both kept per line. The Admin approves (reserving stock)
 * or rejects; Logistics releases, which deducts stock through stock entries,
 * and borrowed equipment goes into the logbook one unit at a time.
 */
export default function IcsRequestsIndex({ requests, items, depots, available, can }: Props) {
  const reload = () => router.reload({ only: ["requests", "available"] });

  return (
    <div>
      <IcsTabs />
      <div className="grid gap-6 p-6 xl:grid-cols-[1fr_360px]">
        <section className="space-y-4">
          {requests.length === 0 && <p className="text-sm text-[var(--text-secondary)]">No requests filed yet.</p>}
          {requests.map((r) => (
            <RequestCard key={r.id} r={r} depots={depots} available={available} can={can} onDone={reload} />
          ))}
        </section>
        {can.file && <FileForm items={items} onDone={reload} />}
      </div>
    </div>
  );
}

function RequestCard({ r, depots, available, can, onDone }: {
  r: IcsRequest;
  depots: Props["depots"];
  available: Props["available"];
  can: Can;
  onDone: () => void;
}) {
  const [open, setOpen] = useState(r.status === "submitted" || r.status === "approved" || r.status === "partially_released");
  const [approve, setApprove] = useState<Record<string, { quantity_approved: number; depot_id: string }>>(
    Object.fromEntries(r.lines.map((l) => [l.id, { quantity_approved: l.quantity_requested, depot_id: depots[0]?.id ?? "" }])),
  );
  const [release, setRelease] = useState<Record<string, string>>({});
  const [borrower, setBorrower] = useState({ borrower_name: "", borrower_contact: "" });
  const [reason, setReason] = useState("");
  // One request in flight per card: every action button is disabled until it answers.
  const [busy, setBusy] = useState(false);

  const own = r.requested_by === can.userId;
  const hasBorrowable = r.lines.some((l) => !l.resource_type.is_consumable);
  const availableAt = (typeId: string, depotId: string) =>
    available.find((a) => a.resource_type_id === typeId && a.depot_id === depotId)?.available ?? 0;

  const act = async (path: string, body: Record<string, unknown> = {}, ok = "Done.") => {
    if (busy) return;
    setBusy(true);
    try {
      const res = await http.post(`/api/ics-requests/${r.id}/${path}`, body);
      if (res.data?.lines_exceeding_stock?.length) {
        toast.warning("Approved, but some lines exceed available stock. Logistics has been flagged.");
      } else {
        toast.success(ok);
      }
    } catch (err) {
      toast.error(message(err));
    }
    setBusy(false);
    onDone();
  };

  // Online, the server checks the release at once; with no connection it is saved as
  // stock entries in this device's outbox (one per consumable line, one per borrowed unit).
  const releaseNow = async () => {
    if (busy) return;
    const quantities = Object.fromEntries(Object.entries(release).map(([k, v]) => [k, Number(v) || 0]));
    setBusy(true);
    try {
      await http.post(`/api/ics-requests/${r.id}/release`, { quantities, ...borrower });
      toast.success("Released. Stock entries recorded.");
    } catch (err) {
      if (!isNetworkError(err)) {
        toast.error(message(err));
      } else if (r.lines.some((l) => !l.resource_type.is_consumable && quantities[l.id] > 0) && !borrower.borrower_name.trim()) {
        setBusy(false);
        return toast.error("Borrowed equipment needs the borrower's name.");
      } else {
        await queueRelease(r, quantities, borrower, can.userId);
        setRelease({});
        toast.success("Saved on this device. The release syncs when you're back online.");
      }
    }
    setBusy(false);
    onDone();
  };

  return (
    <article className="border border-white/10">
      <button type="button" onClick={() => setOpen(!open)} className="flex w-full flex-wrap items-center justify-between gap-2 px-4 py-3 text-left hover:bg-white/5">
        <span className="text-sm">
          <strong>{r.requesting_division}</strong> · {PURPOSE[r.purpose]} · {new Date(r.date).toLocaleDateString("en-PH")}
          {r.event_tag && <span className="ml-2 text-xs text-[var(--amber)]">{r.event_tag}</span>}
        </span>
        <span className={`text-xs uppercase ${STATUS_STYLE[r.status] ?? ""}`}>{r.status.replace("_", " ")}</span>
      </button>

      {open && (
        <div className="space-y-4 border-t border-white/10 px-4 py-3">
          <table className="w-full text-sm">
            <thead className="text-left text-xs uppercase text-[var(--text-secondary)]">
              <tr><th className="py-1">Item</th><th className="text-right">Requested</th><th className="text-right">Approved</th><th className="text-right">Released</th></tr>
            </thead>
            <tbody>
              {r.lines.map((l) => (
                <tr key={l.id} className="border-t border-white/5 align-top">
                  <td className="py-2">
                    {l.resource_type.name}
                    {!l.resource_type.is_consumable && <span className="ml-2 text-xs text-[var(--text-secondary)]">borrowed</span>}
                    {can.decide && r.status === "submitted" && (
                      <div className="mt-1 flex gap-2">
                        <select className={`${field} w-auto`} aria-label={`Depot for ${l.resource_type.name}`} value={approve[l.id]?.depot_id}
                          onChange={(e) => setApprove({ ...approve, [l.id]: { ...approve[l.id], depot_id: e.target.value } })}>
                          {depots.map((d) => (
                            <option key={d.id} value={d.id} className="bg-black">{d.name} ({availableAt(l.resource_type_id, d.id)} available)</option>
                          ))}
                        </select>
                        <input className={`${field} w-24`} type="number" min={0} max={l.quantity_requested} aria-label={`Quantity to approve for ${l.resource_type.name}`} value={approve[l.id]?.quantity_approved}
                          onChange={(e) => setApprove({ ...approve, [l.id]: { ...approve[l.id], quantity_approved: Number(e.target.value) } })} />
                      </div>
                    )}
                    {can.release && ["approved", "partially_released"].includes(r.status) && (l.quantity_approved ?? 0) > l.quantity_released && (
                      <input className={`${field} mt-1 w-32`} type="number" min={0} max={(l.quantity_approved ?? 0) - l.quantity_released}
                        placeholder={`Release (≤ ${(l.quantity_approved ?? 0) - l.quantity_released})`}
                        aria-label={`Quantity to release for ${l.resource_type.name}, up to ${(l.quantity_approved ?? 0) - l.quantity_released}`} value={release[l.id] ?? ""}
                        onChange={(e) => setRelease({ ...release, [l.id]: e.target.value })} />
                    )}
                  </td>
                  <td className="text-right">{l.quantity_requested}</td>
                  <td className="text-right">{l.quantity_approved ?? "–"}</td>
                  <td className="text-right">{l.quantity_released}</td>
                </tr>
              ))}
            </tbody>
          </table>

          {r.decision_reason && <p className="text-sm">Reason: {r.decision_reason}</p>}

          <div className="flex flex-wrap items-center gap-2">
            {/* The Admin (the only one who decides) may approve a request they filed themselves (system-spec.md §2). */}
            {can.decide && r.status === "submitted" && (
              <>
                <Button disabled={busy} onClick={() => act("approve", { lines: approve }, "Approved. Stock is reserved.")}>Approve</Button>
                <input className={`${field} w-56`} placeholder="Reason (required to reject)" aria-label="Reason (required to reject)" value={reason} onChange={(e) => setReason(e.target.value)} />
                <Button variant="ghost" disabled={busy || !reason.trim()} onClick={() => act("reject", { reason }, "Rejected.")}>Reject</Button>
              </>
            )}
            {can.release && ["approved", "partially_released"].includes(r.status) && (
              <>
                {hasBorrowable && (
                  <>
                    <input className={`${field} w-44`} placeholder="Borrower's name" aria-label="Borrower's name" value={borrower.borrower_name}
                      onChange={(e) => setBorrower({ ...borrower, borrower_name: e.target.value })} />
                    <input className={`${field} w-36`} placeholder="Contact" aria-label="Borrower's contact" value={borrower.borrower_contact}
                      onChange={(e) => setBorrower({ ...borrower, borrower_contact: e.target.value })} />
                  </>
                )}
                <Button disabled={busy} onClick={releaseNow}>{busy ? "Working…" : "Release"}</Button>
              </>
            )}
            {can.release && r.status === "partially_released" && (
              <Button variant="ghost" disabled={busy} onClick={() => act("close", {}, "Closed. The rest of the reservation is freed.")}>Close</Button>
            )}
            {((own && ["draft", "submitted"].includes(r.status)) || (can.decide && ["draft", "submitted", "approved"].includes(r.status))) && (
              <Button variant="ghost" disabled={busy} onClick={() => act("cancel", {}, "Cancelled.")}>Cancel request</Button>
            )}
            {own && r.status === "draft" && <Button variant="ghost" disabled={busy} onClick={() => act("submit", {}, "Submitted.")}>Submit</Button>}
          </div>

          <History items={r.history} />
        </div>
      )}
    </article>
  );
}

// ponytail: the page keeps showing the pre-release numbers until it syncs; show queued releases here if that confuses users.
async function queueRelease(r: IcsRequest, quantities: Record<string, number>, borrower: { borrower_name: string; borrower_contact: string }, userId: string) {
  for (const l of r.lines) {
    const qty = quantities[l.id] ?? 0;
    if (qty <= 0 || !l.depot_id) continue;
    const base = { resource_type_id: l.resource_type_id, depot_id: l.depot_id, request_line_id: l.id, event_tag: r.event_tag };
    if (l.resource_type.is_consumable) {
      await enqueueStock({ ...base, quantity: -qty, reason: "release" }, userId);
      continue;
    }
    // Each borrowed unit gets a device-made borrow ID, so it can be returned before it has synced.
    for (let i = 0; i < qty; i++) {
      await enqueueStock({
        ...base, quantity: -1, reason: "issue", borrow_id: crypto.randomUUID(), label: l.resource_type.name,
        borrower_name: borrower.borrower_name, borrower_contact: borrower.borrower_contact || null, borrower_division: r.requesting_division,
      }, userId);
    }
  }
  await syncOutbox(userId);
}

function FileForm({ items, onDone }: { items: Item[]; onDone: () => void }) {
  const blankLine = () => ({ resource_type_id: items[0]?.id ?? "", quantity_requested: "" });
  const [form, setForm] = useState({ requesting_division: DIVISIONS[0], purpose: "team_kit", event_tag: "" });
  const [lines, setLines] = useState([blankLine()]);
  const [busy, setBusy] = useState(false);
  const names = useMemo(() => Object.fromEntries(items.map((i) => [i.id, i])), [items]);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    try {
      await http.post("/api/ics-requests", {
        ...form,
        event_tag: form.event_tag || null,
        lines: lines.map((l) => ({ resource_type_id: l.resource_type_id, quantity_requested: Number(l.quantity_requested) })),
      });
      toast.success("Request filed and submitted for the Head of Office's approval.");
      setLines([blankLine()]);
    } catch (err) {
      toast.error(message(err));
    } finally {
      setBusy(false);
      onDone();
    }
  };

  return (
    <form onSubmit={submit} className="h-fit space-y-3 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">File an ICS 213-RR request</h2>
      <select className={field} aria-label="Requesting division" value={form.requesting_division} onChange={(e) => setForm({ ...form, requesting_division: e.target.value })}>
        {DIVISIONS.map((d) => <option key={d} className="bg-black">{d}</option>)}
      </select>
      <div className="grid grid-cols-2 gap-2">
        <select className={field} aria-label="Purpose" value={form.purpose} onChange={(e) => setForm({ ...form, purpose: e.target.value })}>
          {Object.entries(PURPOSE).map(([v, l]) => <option key={v} value={v} className="bg-black">{l}</option>)}
        </select>
        <input className={field} placeholder="Event tag (optional)" aria-label="Event tag (optional)" value={form.event_tag} onChange={(e) => setForm({ ...form, event_tag: e.target.value })} />
      </div>
      {lines.map((l, i) => (
        <div key={i} className="flex gap-2">
          <select className={field} aria-label={`Item ${i + 1}`} value={l.resource_type_id}
            onChange={(e) => setLines(lines.map((x, j) => (j === i ? { ...x, resource_type_id: e.target.value } : x)))}>
            {items.map((it) => <option key={it.id} value={it.id} className="bg-black">{it.name}</option>)}
          </select>
          <input className={`${field} w-24`} type="number" min={1} required placeholder={names[l.resource_type_id]?.unit ?? "Qty"}
            aria-label={`Quantity for item ${i + 1}${names[l.resource_type_id]?.unit ? ` (${names[l.resource_type_id].unit})` : ""}`} value={l.quantity_requested}
            onChange={(e) => setLines(lines.map((x, j) => (j === i ? { ...x, quantity_requested: e.target.value } : x)))} />
          {lines.length > 1 && (
            <button type="button" aria-label="Remove line" className="px-2 text-[var(--text-secondary)]" onClick={() => setLines(lines.filter((_, j) => j !== i))}>×</button>
          )}
        </div>
      ))}
      <button type="button" className="text-xs underline text-[var(--text-secondary)]" onClick={() => setLines([...lines, blankLine()])}>+ Add item</button>
      <Button type="submit" disabled={busy} className="w-full">{busy ? "Filing…" : "File request"}</Button>
    </form>
  );
}

IcsRequestsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
