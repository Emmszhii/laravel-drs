import { useMemo, useState, type FormEvent } from "react";
import { router, usePage } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { CATEGORY_LABEL, subjectOf } from "@/lib/categories";
import { enqueueStock } from "@/Offline/outbox";
import { isNetworkError, syncOutbox } from "@/Offline/sync";
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
 * The form carries what the paper form does: control no., date and time, the
 * requester's name and position, the subject (from the items), a reason for
 * every cut line, and Logistics' reply.
 */
export default function IcsRequestsIndex({ requests, items, kits, depots, available, divisions, can }: Props) {
  const reload = () => router.reload({ only: ["requests", "available", "kits"] });

  return (
    <div>
      <IcsTabs />
      <DivisionList divisions={divisions} />
      <div className="grid gap-6 p-6 xl:grid-cols-[1fr_360px]">
        <section className="space-y-4">
          {requests.length === 0 && <p className="text-sm text-[var(--text-secondary)]">No requests filed yet.</p>}
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

  return (
    <article className="border border-white/10">
      <button type="button" onClick={() => setOpen(!open)} className="flex w-full flex-wrap items-center justify-between gap-2 px-4 py-3 text-left hover:bg-white/5">
        <span className="text-sm">
          {r.control_no && <span className="mr-2 text-xs text-[var(--text-secondary)]">No. {r.control_no}</span>}
          <strong>{r.requesting_division}</strong> · {PURPOSE[r.purpose]} · {new Date(r.date).toLocaleDateString("en-PH")}
          {r.time ? ` ${r.time.slice(0, 5)}` : ""}
          {r.event_tag && <span className="ml-2 text-xs text-[var(--amber)]">{r.event_tag}</span>}
        </span>
        <span className={`text-xs uppercase ${STATUS_STYLE[r.status] ?? ""}`}>{r.status.replace("_", " ")}</span>
      </button>

      {open && (
        <div className="space-y-4 border-t border-white/10 px-4 py-3">
          <p className="text-xs text-[var(--text-secondary)]">
            Requested by {r.requester_name ?? (r.requester ? `${r.requester.first_name} ${r.requester.last_name}` : "—")}{r.requester_position ? `, ${r.requester_position}` : ""} · Subject: {subjectOf(r.lines.map((l) => l.resource_type)) || "—"}
          </p>
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
                    {l.approval_note && <div className="mt-1 text-xs text-[var(--text-secondary)]">Why less: {l.approval_note}</div>}
                    {can.decide && r.status === "submitted" && (
                      <>
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
                        {cut(l) && (
                          <input className={`${field} mt-1`} placeholder="Why less than requested? (required)" aria-label={`Why ${l.resource_type.name} is approved for less`} value={approve[l.id]?.reason}
                            onChange={(e) => setApprove({ ...approve, [l.id]: { ...approve[l.id], reason: e.target.value } })} />
                        )}
                      </>
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

          {/* Form item 11: Logistics' reply. Everyone reads it; Logistics (and the Admin) write it. */}
          {r.status !== "draft" && can.release && (
            <div className="flex flex-wrap items-start gap-2">
              <textarea className={`${field} min-w-64 flex-1`} rows={2} placeholder="Reply / remarks (e.g. items released)" aria-label="Reply or remarks" value={remarks}
                onChange={(e) => setRemarks(e.target.value)} />
              <Button variant="ghost" disabled={busy || remarks === (r.remarks ?? "")} onClick={() => act("remarks", { remarks }, "Reply saved.")}>Save reply</Button>
            </div>
          )}
          {r.status !== "draft" && !can.release && r.remarks && <p className="whitespace-pre-line text-sm">Reply: {r.remarks}</p>}

          <div className="flex flex-wrap items-center gap-2">
            {/* The Admin (the only one who decides) may approve a request they filed themselves (system-spec.md §2). */}
            {can.decide && r.status === "submitted" && (
              <>
                <Button disabled={busy || missingReason} title={missingReason ? "Say why each reduced line is approved for less" : undefined}
                  onClick={() => act("approve", { lines: approve }, "Approved. Stock is reserved.")}>Approve</Button>
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
                    <input className={`${field} w-48`} list="divisions" placeholder={`Office (default ${r.requesting_division})`} aria-label="Borrower's division or office" value={borrower.borrower_division}
                      onChange={(e) => setBorrower({ ...borrower, borrower_division: e.target.value })} />
                    <input className={`${field} w-36`} placeholder="Location of use" aria-label="Location of use" value={borrower.location}
                      onChange={(e) => setBorrower({ ...borrower, location: e.target.value })} />
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
    <form onSubmit={submit} className="h-fit space-y-3 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">File an ICS 213-RR request</h2>
      <input className={field} placeholder="Control no. (printed on the paper form)" aria-label="Control number" maxLength={30} value={form.control_no}
        onChange={(e) => setForm({ ...form, control_no: e.target.value })} />
      <div className="grid grid-cols-2 gap-2">
        <input className={field} placeholder="Requester's name" aria-label="Requester's name" required value={form.requester_name} onChange={(e) => setForm({ ...form, requester_name: e.target.value })} />
        <input className={field} placeholder="Position" aria-label="Requester's position" value={form.requester_position} onChange={(e) => setForm({ ...form, requester_position: e.target.value })} />
      </div>
      <input className={field} list="divisions" required placeholder="Division / team, e.g. Operations / Team Delta" aria-label="Division or team" value={form.requesting_division}
        onChange={(e) => setForm({ ...form, requesting_division: e.target.value })} />
      <div className="grid grid-cols-2 gap-2">
        <input className={field} type="date" aria-label="Date (blank means today)" title="Date (blank means today)" value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} />
        <input className={field} type="time" aria-label="Time (blank means now)" title="Time (blank means now)" value={form.time} onChange={(e) => setForm({ ...form, time: e.target.value })} />
      </div>
      <p className="text-xs text-[var(--text-secondary)]">Leave date and time blank to use now. Fill them in to encode a paper form (up to 31 days old).</p>
      <div className="grid grid-cols-2 gap-2">
        <select className={field} aria-label="Purpose" value={form.purpose} onChange={(e) => setForm({ ...form, purpose: e.target.value })}>
          {Object.entries(PURPOSE).map(([v, l]) => <option key={v} value={v} className="bg-black">{l}</option>)}
        </select>
        <input className={field} placeholder="Event tag (optional)" aria-label="Event tag (optional)" value={form.event_tag} onChange={(e) => setForm({ ...form, event_tag: e.target.value })} />
      </div>

      {kits.length > 0 && (
        <div className="flex items-center gap-2">
          <select className={field} aria-label="Load a kit" value={kitId} onChange={(e) => loadKit(e.target.value)}>
            <option value="" className="bg-black">Load a kit…</option>
            {kits.map((k) => <option key={k.id} value={k.id} className="bg-black">{k.name}</option>)}
          </select>
          {can.release && kitId && (
            <button type="button" className="shrink-0 text-xs underline text-[var(--text-secondary)]" onClick={deleteKit}>Delete kit</button>
          )}
        </div>
      )}

      {lines.map((l, i) => (
        <div key={i} className="flex gap-2">
          <select className={field} aria-label={`Item ${i + 1}`} value={l.resource_type_id}
            onChange={(e) => setLines(lines.map((x, j) => (j === i ? { ...x, resource_type_id: e.target.value } : x)))}>
            {grouped.map(([c, list]) => (
              <optgroup key={c} label={CATEGORY_LABEL[c]} className="bg-black">
                {list.map((it) => <option key={it.id} value={it.id} className="bg-black">{it.name}</option>)}
              </optgroup>
            ))}
          </select>
          <input className={`${field} w-24`} type="number" min={1} required placeholder={names[l.resource_type_id]?.unit ?? "Qty"}
            aria-label={`Quantity for item ${i + 1}${names[l.resource_type_id]?.unit ? ` (${names[l.resource_type_id].unit})` : ""}`} value={l.quantity_requested}
            onChange={(e) => setLines(lines.map((x, j) => (j === i ? { ...x, quantity_requested: e.target.value } : x)))} />
          {lines.length > 1 && (
            <button type="button" aria-label="Remove line" className="px-2 text-[var(--text-secondary)]" onClick={() => setLines(lines.filter((_, j) => j !== i))}>×</button>
          )}
        </div>
      ))}
      <div className="flex items-center justify-between text-xs text-[var(--text-secondary)]">
        <button type="button" className="underline" onClick={() => setLines([...lines, blankLine()])}>+ Add item</button>
        {can.release && <button type="button" className="underline" onClick={saveKit}>Save these lines as a kit</button>}
      </div>
      <p className="text-xs text-[var(--text-secondary)]">Subject: {subjectOf(lines.map((l) => names[l.resource_type_id]).filter(Boolean)) || "—"}</p>
      <Button type="submit" disabled={busy} className="w-full">{busy ? "Filing…" : "File request"}</Button>
    </form>
  );
}

IcsRequestsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
