import { useMemo, useState, type FormEvent } from "react";
import { router, usePage } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { CATEGORY_LABEL, subjectOf } from "@/lib/categories";
import { enqueueStock } from "@/Offline/outbox";
import { isNetworkError, syncOutbox } from "@/Offline/sync";
import { PageHeader } from "@/Components/PageHeader";
import { StatusBadge, type Tone } from "@/Components/StatusBadge";
import { cn, label } from "@/lib/utils";
import { IcsTabs, History, DivisionList, field, message, type Can, type StatusChange } from "./shared";

type Item = { id: string; name: string; unit: string; category: string; is_consumable: boolean };
type Line = {
  id: string;
  resource_type_id: string;
  resource_type: Item;
  depot_id: string | null;
  quantity_requested: number;
  quantity_approved: number | null;
  quantity_released: number;
  approval_note: string | null;
};
type IcsRequest = {
  id: string;
  control_no: string | null;
  requesting_division: string;
  requested_by: string | null;
  requester_name: string | null;
  requester_position: string | null;
  requester?: { first_name: string; last_name: string } | null;
  date: string;
  time: string | null;
  purpose: "team_kit" | "relief" | "office_use";
  event_tag: string | null;
  status: string;
  decision_reason: string | null;
  remarks: string | null;
  lines: Line[];
  history: StatusChange[];
};
type Kit = { id: string; name: string; lines: { resource_type_id: string; quantity: number }[] };
type Props = {
  requests: IcsRequest[];
  items: Item[];
  kits: Kit[];
  depots: { id: string; name: string }[];
  available: { resource_type_id: string; depot_id: string; available: number }[];
  divisions: string[];
  can: Can;
};

const PURPOSE: Record<string, string> = { team_kit: "Team kit", relief: "Relief", office_use: "Office use" };
const STATUS_TONE: Record<string, Tone> = {
  draft: "neutral",
  submitted: "amber",
  approved: "info",
  partially_released: "info",
  released: "ok",
  closed: "ok",
  rejected: "critical",
  cancelled: "neutral",
};
const statusLabel = (s: string) => s.charAt(0).toUpperCase() + s.slice(1).replace("_", " ");

/**
 * ICS 213-RR requests (system-spec.md §3.2, §6). Requested and released
 * quantities are both kept per line. The Admin approves (reserving stock)
 * or rejects; Logistics releases, which deducts stock through stock entries,
 * and borrowed equipment goes into the logbook one unit at a time.
 * The form carries what the paper form does: control no., date and time, the
 * requester's name and position, the subject (from the items), a reason for
 * every cut line, and Logistics' reply.
 */
export default function IcsRequestsIndex({ requests, items, kits, depots, available, divisions, can }: Props) {
  const reload = () => router.reload({ only: ["requests", "available", "kits"] });

  return (
    <div>
      <PageHeader title="ICS 213-RR" description="Resource requests on the ICS 213-RR form: filed by the teams, approved by the Head of Office, released by Logistics." />
      <IcsTabs />
      <DivisionList divisions={divisions} />
      <div className="grid gap-6 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section className="min-w-0 space-y-3">
          {requests.length === 0 && <p className="panel p-6 text-sm text-[var(--text-secondary)]">No requests filed yet.{can.file ? " File one with the form." : ""}</p>}
          {requests.map((r) => (
            <RequestCard key={r.id} r={r} depots={depots} available={available} can={can} onDone={reload} />
          ))}
        </section>
        {can.file && <FileForm items={items} kits={kits} can={can} onDone={reload} />}
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
  const [approve, setApprove] = useState<Record<string, { quantity_approved: number; depot_id: string; reason: string }>>(
    Object.fromEntries(r.lines.map((l) => [l.id, { quantity_approved: l.quantity_requested, depot_id: depots[0]?.id ?? "", reason: "" }])),
  );
  const [release, setRelease] = useState<Record<string, string>>({});
  const [borrower, setBorrower] = useState<Borrower>({ borrower_name: "", borrower_contact: "", borrower_division: "", location: "" });
  const [reason, setReason] = useState("");
  const [remarks, setRemarks] = useState(r.remarks ?? "");
  // One request in flight per card: every action button is disabled until it answers.
  const [busy, setBusy] = useState(false);

  const own = r.requested_by === can.userId;
  const hasBorrowable = r.lines.some((l) => !l.resource_type.is_consumable);
  const availableAt = (typeId: string, depotId: string) =>
    available.find((a) => a.resource_type_id === typeId && a.depot_id === depotId)?.available ?? 0;
  // The paper form asks why items are disapproved: a line approved for less than requested needs its reason.
  const cut = (l: Line) => (approve[l.id]?.quantity_approved ?? 0) < l.quantity_requested;
  const missingReason = r.lines.some((l) => cut(l) && !approve[l.id]?.reason.trim());

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

  const releasable = can.release && ["approved", "partially_released"].includes(r.status);
  const cancellable = (own && ["draft", "submitted"].includes(r.status)) || (can.decide && ["draft", "submitted", "approved"].includes(r.status));

  return (
    <article className="panel">
      <button type="button" aria-expanded={open} onClick={() => setOpen(!open)} className="flex w-full items-start justify-between gap-3 px-4 py-3 text-left hover:bg-tint">
        <span className="min-w-0 text-sm">
          <span className="block">
            <strong className="font-semibold">{r.requesting_division}</strong>
            <span className="text-[var(--text-secondary)]"> · {PURPOSE[r.purpose]} · {new Date(r.date).toLocaleDateString("en-PH")}{r.time ? ` ${r.time.slice(0, 5)}` : ""}</span>
          </span>
          <span className="mt-0.5 block text-xs text-[var(--text-secondary)]">
            {r.lines.length} item{r.lines.length === 1 ? "" : "s"}
            {r.control_no && ` · No. ${r.control_no}`}
            {r.event_tag && <span className="text-amber"> · {r.event_tag}</span>}
          </span>
        </span>
        <StatusBadge tone={STATUS_TONE[r.status] ?? "neutral"}>{statusLabel(r.status)}</StatusBadge>
      </button>

      {open && (
        <div className="space-y-4 border-t border-line px-4 py-4">
          <p className="text-xs text-[var(--text-secondary)]">
            Requested by {r.requester_name ?? (r.requester ? `${r.requester.first_name} ${r.requester.last_name}` : "—")}{r.requester_position ? `, ${r.requester_position}` : ""} · Subject: {subjectOf(r.lines.map((l) => l.resource_type)) || "—"}
          </p>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[480px] text-sm">
              <thead className="text-left text-xs text-[var(--text-secondary)]">
                <tr><th className="pb-1.5 font-medium">Item</th><th className="pb-1.5 pl-3 text-right font-medium">Requested</th><th className="pb-1.5 pl-3 text-right font-medium">Approved</th><th className="pb-1.5 pl-3 text-right font-medium">Released</th></tr>
              </thead>
              <tbody>
                {r.lines.map((l) => (
                  <tr key={l.id} className="border-t border-line align-top">
                    <td className="py-2">
                      {l.resource_type.name}
                      {!l.resource_type.is_consumable && <span className="ml-2 text-xs text-[var(--text-secondary)]">borrowed</span>}
                      {l.approval_note && <div className="mt-1 text-xs text-[var(--text-secondary)]">Why less: {l.approval_note}</div>}
                      {can.decide && r.status === "submitted" && (
                        <>
                          <div className="mt-1.5 flex flex-wrap gap-2">
                            <select className={cn(field, "w-auto max-w-full")} aria-label={`Depot for ${l.resource_type.name}`} value={approve[l.id]?.depot_id}
                              onChange={(e) => setApprove({ ...approve, [l.id]: { ...approve[l.id], depot_id: e.target.value } })}>
                              {depots.map((d) => (
                                <option key={d.id} value={d.id}>{d.name} ({availableAt(l.resource_type_id, d.id)} available)</option>
                              ))}
                            </select>
                            <input className={cn(field, "w-24")} type="number" min={0} max={l.quantity_requested} aria-label={`Quantity to approve for ${l.resource_type.name}`} value={approve[l.id]?.quantity_approved}
                              onChange={(e) => setApprove({ ...approve, [l.id]: { ...approve[l.id], quantity_approved: Number(e.target.value) } })} />
                          </div>
                          {cut(l) && (
                            <input className={cn(field, "mt-1.5 block")} placeholder="Why less than requested? (required)" aria-label={`Why ${l.resource_type.name} is approved for less`} value={approve[l.id]?.reason}
                              onChange={(e) => setApprove({ ...approve, [l.id]: { ...approve[l.id], reason: e.target.value } })} />
                          )}
                        </>
                      )}
                      {releasable && (l.quantity_approved ?? 0) > l.quantity_released && (
                        <input className={cn(field, "mt-1.5 block w-36")} type="number" min={0} max={(l.quantity_approved ?? 0) - l.quantity_released}
                          placeholder={`Release (up to ${(l.quantity_approved ?? 0) - l.quantity_released})`}
                          aria-label={`Quantity to release for ${l.resource_type.name}, up to ${(l.quantity_approved ?? 0) - l.quantity_released}`} value={release[l.id] ?? ""}
                          onChange={(e) => setRelease({ ...release, [l.id]: e.target.value })} />
                      )}
                    </td>
                    <td className="py-2 pl-3 text-right">{l.quantity_requested}</td>
                    <td className="py-2 pl-3 text-right">{l.quantity_approved ?? "–"}</td>
                    <td className="py-2 pl-3 text-right">{l.quantity_released}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {r.decision_reason && <p className="text-sm"><span className="text-[var(--text-secondary)]">Reason:</span> {r.decision_reason}</p>}

          {/* The Admin (the only one who decides) may approve a request they filed themselves (system-spec.md §2). */}
          {can.decide && r.status === "submitted" && (
            <div className="flex flex-wrap items-end gap-2 border-l-2 border-amber bg-tint p-3">
              <label className={`${label} min-w-[200px] flex-1`}>Reason (required to reject)
                <input className={field} value={reason} onChange={(e) => setReason(e.target.value)} />
              </label>
              <Button variant="ghost" disabled={busy || !reason.trim()} onClick={() => act("reject", { reason }, "Rejected.")}>Reject</Button>
              <Button disabled={busy || missingReason} title={missingReason ? "Say why each reduced line is approved for less" : undefined}
                onClick={() => act("approve", { lines: approve }, "Approved. Stock is reserved.")}>Approve</Button>
            </div>
          )}

          {releasable && (
            <div className="space-y-2 border-l-2 border-info bg-tint p-3">
              {hasBorrowable && (
                <div className="grid gap-2 sm:grid-cols-2">
                  <label className={label}>Borrower's name
                    <input className={field} value={borrower.borrower_name} onChange={(e) => setBorrower({ ...borrower, borrower_name: e.target.value })} />
                  </label>
                  <label className={label}>Contact (optional)
                    <input className={field} value={borrower.borrower_contact} onChange={(e) => setBorrower({ ...borrower, borrower_contact: e.target.value })} />
                  </label>
                  <label className={label}>Office
                    <input className={field} list="divisions" placeholder={r.requesting_division} value={borrower.borrower_division}
                      onChange={(e) => setBorrower({ ...borrower, borrower_division: e.target.value })} />
                  </label>
                  <label className={label}>Location of use (optional)
                    <input className={field} value={borrower.location} onChange={(e) => setBorrower({ ...borrower, location: e.target.value })} />
                  </label>
                </div>
              )}
              <div className="flex flex-wrap justify-end gap-2">
                {r.status === "partially_released" && (
                  <Button variant="ghost" disabled={busy} onClick={() => act("close", {}, "Closed. The rest of the reservation is freed.")}>Close</Button>
                )}
                <Button disabled={busy} onClick={releaseNow}>{busy ? "Working…" : "Release"}</Button>
              </div>
            </div>
          )}

          {/* Form item 11: Logistics' reply. Everyone reads it; Logistics (and the Admin) write it. */}
          {r.status !== "draft" && can.release && (
            <div className="flex flex-wrap items-end gap-2">
              <label className={`${label} min-w-64 flex-1`}>Reply / remarks
                <textarea className={cn(field, "h-auto py-2")} rows={2} placeholder="e.g. items released" value={remarks}
                  onChange={(e) => setRemarks(e.target.value)} />
              </label>
              <Button variant="ghost" disabled={busy || remarks === (r.remarks ?? "")} onClick={() => act("remarks", { remarks }, "Reply saved.")}>Save reply</Button>
            </div>
          )}
          {r.status !== "draft" && !can.release && r.remarks && <p className="whitespace-pre-line text-sm"><span className="text-[var(--text-secondary)]">Reply:</span> {r.remarks}</p>}

          {(cancellable || (own && r.status === "draft")) && (
            <div className="flex flex-wrap gap-2">
              {own && r.status === "draft" && <Button disabled={busy} onClick={() => act("submit", {}, "Submitted.")}>Submit</Button>}
              {cancellable && <Button variant="ghost" disabled={busy} onClick={() => act("cancel", {}, "Cancelled.")}>Cancel request</Button>}
            </div>
          )}

          <History items={r.history} />
        </div>
      )}
    </article>
  );
}

type Borrower = { borrower_name: string; borrower_contact: string; borrower_division: string; location: string };

// ponytail: the page keeps showing the pre-release numbers until it syncs; show queued releases here if that confuses users.
async function queueRelease(r: IcsRequest, quantities: Record<string, number>, borrower: Borrower, userId: string) {
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
        borrower_name: borrower.borrower_name, borrower_contact: borrower.borrower_contact || null,
        borrower_division: borrower.borrower_division.trim() || r.requesting_division, location: borrower.location.trim() || null,
      }, userId);
    }
  }
  await syncOutbox(userId);
}

function FileForm({ items, kits, can, onDone }: { items: Item[]; kits: Kit[]; can: Can; onDone: () => void }) {
  const me = usePage().props.auth?.user;
  const blankLine = () => ({ resource_type_id: items[0]?.id ?? "", quantity_requested: "" });
  // Date, time and control no. are left blank unless a paper form is being encoded: blank date and time mean now.
  const [form, setForm] = useState({
    control_no: "", requesting_division: "", requester_name: me?.name ?? "", requester_position: me?.position ?? "",
    date: "", time: "", purpose: "team_kit", event_tag: "",
  });
  const [lines, setLines] = useState([blankLine()]);
  const [kitId, setKitId] = useState("");
  const [busy, setBusy] = useState(false);
  const names = useMemo(() => Object.fromEntries(items.map((i) => [i.id, i])), [items]);
  const grouped = useMemo(
    () => Object.keys(CATEGORY_LABEL).map((c) => [c, items.filter((i) => i.category === c)] as const).filter(([, list]) => list.length > 0),
    [items],
  );
  const filled = lines.filter((l) => Number(l.quantity_requested) >= 1);

  const loadKit = (id: string) => {
    setKitId(id);
    const kit = kits.find((k) => k.id === id);
    if (kit) setLines(kit.lines.map((l) => ({ resource_type_id: l.resource_type_id, quantity_requested: String(l.quantity) })));
  };

  const saveKit = async () => {
    if (filled.length === 0) return toast.error("Enter a quantity for at least one item first.");
    const name = window.prompt("Name this kit (saving under an existing name replaces it):", kits.find((k) => k.id === kitId)?.name ?? "");
    if (!name?.trim()) return;
    try {
      await http.post("/api/kits", { name: name.trim(), lines: filled.map((l) => ({ resource_type_id: l.resource_type_id, quantity: Number(l.quantity_requested) })) });
      toast.success("Kit saved.");
      onDone();
    } catch (err) {
      toast.error(message(err));
    }
  };

  const deleteKit = async () => {
    if (!kitId || !window.confirm("Delete this kit? Requests already filed are not affected.")) return;
    try {
      await http.delete(`/api/kits/${kitId}`);
      setKitId("");
      toast.success("Kit deleted.");
      onDone();
    } catch (err) {
      toast.error(message(err));
    }
  };

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    try {
      await http.post("/api/ics-requests", {
        ...form,
        control_no: form.control_no.trim() || null,
        date: form.date || null,
        time: form.time || null,
        requester_position: form.requester_position.trim() || null,
        event_tag: form.event_tag || null,
        lines: lines.map((l) => ({ resource_type_id: l.resource_type_id, quantity_requested: Number(l.quantity_requested) })),
      });
      toast.success("Request filed and submitted for the Head of Office's approval.");
      setLines([blankLine()]);
      setKitId("");
      setForm({ ...form, control_no: "", date: "", time: "", event_tag: "" });
    } catch (err) {
      toast.error(message(err));
    } finally {
      setBusy(false);
      onDone();
    }
  };

  return (
    <form onSubmit={submit} className="panel h-fit space-y-3 p-4">
      <h2 className="text-sm font-semibold">File an ICS 213-RR request</h2>
      <div className="grid grid-cols-2 gap-2">
        <label className={label}>Requester's name
          <input className={field} required value={form.requester_name} onChange={(e) => setForm({ ...form, requester_name: e.target.value })} />
        </label>
        <label className={label}>Position
          <input className={field} value={form.requester_position} onChange={(e) => setForm({ ...form, requester_position: e.target.value })} />
        </label>
      </div>
      <label className={label}>Division / team
        <input className={field} list="divisions" required placeholder="e.g. Operations / Team Delta" value={form.requesting_division}
          onChange={(e) => setForm({ ...form, requesting_division: e.target.value })} />
      </label>
      <div className="grid grid-cols-2 gap-2">
        <label className={label}>Purpose
          <select className={field} value={form.purpose} onChange={(e) => setForm({ ...form, purpose: e.target.value })}>
            {Object.entries(PURPOSE).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
          </select>
        </label>
        <label className={label}>Event tag (optional)
          <input className={field} value={form.event_tag} onChange={(e) => setForm({ ...form, event_tag: e.target.value })} />
        </label>
      </div>

      <details className="border border-line px-3 py-2 text-sm" open={Boolean(form.control_no || form.date || form.time)}>
        <summary className="cursor-pointer text-xs font-medium text-[var(--text-secondary)]">Encoding a paper form? Control no., date and time</summary>
        <div className="mt-2 space-y-2">
          <label className={label}>Control no.
            <input className={field} maxLength={30} value={form.control_no} onChange={(e) => setForm({ ...form, control_no: e.target.value })} />
          </label>
          <div className="grid grid-cols-2 gap-2">
            <label className={label}>Date
              <input className={field} type="date" value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} />
            </label>
            <label className={label}>Time
              <input className={field} type="time" value={form.time} onChange={(e) => setForm({ ...form, time: e.target.value })} />
            </label>
          </div>
          <p className="text-xs text-[var(--text-secondary)]">Blank date and time mean now. A paper form can be up to 31 days old.</p>
        </div>
      </details>

      {kits.length > 0 && (
        <div className="flex items-end gap-2">
          <label className={`${label} flex-1`}>Load a kit
            <select className={field} value={kitId} onChange={(e) => loadKit(e.target.value)}>
              <option value="">None</option>
              {kits.map((k) => <option key={k.id} value={k.id}>{k.name}</option>)}
            </select>
          </label>
          {can.release && kitId && (
            <Button type="button" variant="ghost" onClick={deleteKit}>Delete kit</Button>
          )}
        </div>
      )}

      <p className="text-xs font-medium text-[var(--text-secondary)]">Items requested</p>
      {lines.map((l, i) => (
        <div key={i} className="flex gap-2">
          <select className={field} aria-label={`Item ${i + 1}`} value={l.resource_type_id}
            onChange={(e) => setLines(lines.map((x, j) => (j === i ? { ...x, resource_type_id: e.target.value } : x)))}>
            {grouped.map(([c, list]) => (
              <optgroup key={c} label={CATEGORY_LABEL[c]}>
                {list.map((it) => <option key={it.id} value={it.id}>{it.name}</option>)}
              </optgroup>
            ))}
          </select>
          <input className={cn(field, "w-24")} type="number" min={1} required placeholder={names[l.resource_type_id]?.unit ?? "Qty"}
            aria-label={`Quantity for item ${i + 1}${names[l.resource_type_id]?.unit ? ` (${names[l.resource_type_id].unit})` : ""}`} value={l.quantity_requested}
            onChange={(e) => setLines(lines.map((x, j) => (j === i ? { ...x, quantity_requested: e.target.value } : x)))} />
          {lines.length > 1 && (
            <button type="button" aria-label={`Remove item ${i + 1}`} className="w-8 shrink-0 text-lg text-[var(--text-secondary)] hover:text-critical" onClick={() => setLines(lines.filter((_, j) => j !== i))}>×</button>
          )}
        </div>
      ))}
      <div className="flex items-center justify-between text-xs text-[var(--text-secondary)]">
        <button type="button" className="font-medium text-[var(--text-primary)] underline-offset-2 hover:underline" onClick={() => setLines([...lines, blankLine()])}>+ Add item</button>
        {can.release && <button type="button" className="underline-offset-2 hover:underline" onClick={saveKit}>Save these lines as a kit</button>}
      </div>
      <p className="text-xs text-[var(--text-secondary)]">Subject: {subjectOf(lines.map((l) => names[l.resource_type_id]).filter(Boolean)) || "—"}</p>
      <Button type="submit" disabled={busy} className="w-full">{busy ? "Filing…" : "File request"}</Button>
    </form>
  );
}

IcsRequestsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
