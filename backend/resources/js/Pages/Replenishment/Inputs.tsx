import { useRef, useState, type FormEvent } from "react";
import { Link, router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http, ApiError } from "@/api-service";
import { errorMessage, field, label } from "@/lib/utils";
import type { VedPolicy } from "@/Types/models";
import { ReplenishmentHeader, VED_LABEL } from "./shared";

type Props = {
  policies: VedPolicy[];
  /** Open data-check flags; they block the solver until Logistics resolves them on the Recommendation tab. */
  flags: number;
  can: { run: boolean; encode: boolean; export: boolean };
};

/** What the solver plans from: the VED policy (Admin) and the encoded request history (Logistics). */
export default function ReplenishmentInputs({ policies, flags, can }: Props) {
  return (
    <div>
      <ReplenishmentHeader policies={policies} />

      <div className="grid items-start gap-6 p-4 sm:p-6 lg:grid-cols-2">
        <VedPolicyForm policies={policies} canEdit={can.run} onDone={() => router.reload({ only: ["policies"] })} />
        <div className="space-y-6">
          {flags > 0 && (
            <p className="panel border-amber p-4 text-sm">
              <span className="font-semibold text-amber">{flags} data check{flags === 1 ? "" : "s"} open.</span>{" "}
              The solver can't run until they're resolved. <Link href="/replenishment" className="underline">Resolve them</Link>
            </p>
          )}
          {can.encode
            ? <HistoryUpload onDone={() => router.reload({ only: ["flags"] })} />
            : <p className="panel p-4 text-sm text-[var(--text-secondary)]">Logistics encodes the request history here.</p>}
        </div>
      </div>
    </div>
  );
}

/** Admin: confirm (or edit) Table 4's coverage and weight per VED class, recording who confirmed it and when. Everyone else reads it. */
function VedPolicyForm({ policies, canEdit, onDone }: { policies: VedPolicy[]; canEdit: boolean; onDone: () => void }) {
  const [rows, setRows] = useState(policies.map((p) => ({ ...p })));
  const [position, setPosition] = useState(policies.find((p) => p.confirmed_by_position)?.confirmed_by_position ?? "");
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [busy, setBusy] = useState(false);

  const set = (i: number, key: "coverage_k" | "coverage_m" | "weight", value: string) =>
    setRows(rows.map((r, j) => (j === i ? { ...r, [key]: Number(value) } : r)));

  const save = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    try {
      for (const r of rows) {
        await http.patch(`/api/replenishment/policies/${r.ved_class}`, {
          coverage_k: r.coverage_k, coverage_m: r.coverage_m, weight: r.weight,
          confirmed_by_position: position, confirmed_at: date,
        });
      }
      toast.success("VED policy confirmed.");
    } catch (err) {
      toast.error(errorMessage(err));
    } finally {
      setBusy(false);
      onDone();
    }
  };

  const cell = `${field} text-right`;

  return (
    <form onSubmit={save} className="panel space-y-4 p-4">
      <div>
        <h2 className="text-sm font-semibold">VED policy</h2>
        <p className="mt-1 text-xs text-[var(--text-secondary)]">
          For each class, the target stock covers the use of k of the last M quarters. A higher weight makes that class's shortage count more.
        </p>
      </div>
      <table className="w-full text-sm">
        <thead className="text-left text-xs text-[var(--text-secondary)]">
          <tr>
            <th className="pb-1.5 font-medium">Class</th>
            <th className="pb-1.5 pl-2 text-right font-medium">Covers k</th>
            <th className="pb-1.5 pl-2 text-right font-medium">of last M</th>
            <th className="pb-1.5 pl-2 text-right font-medium">Weight</th>
          </tr>
        </thead>
        <tbody>
          {rows.map((r, i) => (
            <tr key={r.ved_class}>
              <td className="py-1 font-medium">{VED_LABEL[r.ved_class]}</td>
              {canEdit ? (
                <>
                  <td className="py-1 pl-2"><input aria-label={`${VED_LABEL[r.ved_class]}: quarters covered (k)`} className={cell} type="number" min={1} max={r.coverage_m} required value={r.coverage_k} onChange={(e) => set(i, "coverage_k", e.target.value)} /></td>
                  <td className="py-1 pl-2"><input aria-label={`${VED_LABEL[r.ved_class]}: of the last M quarters`} className={cell} type="number" min={1} max={12} required value={r.coverage_m} onChange={(e) => set(i, "coverage_m", e.target.value)} /></td>
                  <td className="py-1 pl-2"><input aria-label={`${VED_LABEL[r.ved_class]}: weight`} className={cell} type="number" min={0.1} step="0.1" required value={r.weight} onChange={(e) => set(i, "weight", e.target.value)} /></td>
                </>
              ) : (
                <>
                  <td className="py-1 pl-2 text-right">{r.coverage_k}</td>
                  <td className="py-1 pl-2 text-right">{r.coverage_m}</td>
                  <td className="py-1 pl-2 text-right">{r.weight}</td>
                </>
              )}
            </tr>
          ))}
        </tbody>
      </table>
      {canEdit && (
        <div className="grid gap-3 sm:grid-cols-2">
          <label className={label}>Confirmed by (position)
            <input className={field} required value={position} placeholder="e.g. Logistics Officer" onChange={(e) => setPosition(e.target.value)} />
          </label>
          <label className={label}>Date confirmed
            <input className={field} type="date" required value={date} onChange={(e) => setDate(e.target.value)} />
          </label>
        </div>
      )}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <p className="text-xs text-[var(--text-secondary)]">
          {policies[0]?.confirmed_at
            ? `Last confirmed ${policies[0].confirmed_at.slice(0, 10)} by ${policies[0].confirmed_by_position}.`
            : <span className="text-amber">A proposal: not yet confirmed.</span>}
        </p>
        {canEdit && <Button type="submit" disabled={busy}>{busy ? "Saving…" : "Confirm policy"}</Button>}
      </div>
    </form>
  );
}

function HistoryUpload({ onDone }: { onDone: () => void }) {
  const input = useRef<HTMLInputElement>(null);
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState<string[]>([]);

  const upload = async (e: FormEvent) => {
    e.preventDefault();
    const file = input.current?.files?.[0];
    if (!file) {
      return;
    }
    const body = new FormData();
    body.append("file", file);
    setBusy(true);
    setErrors([]);
    try {
      const res = await http.post("/api/replenishment/history/import", body);
      toast.success(`Imported ${res.data.imported} history line(s).`);
      if (input.current) {
        input.current.value = "";
      }
    } catch (err) {
      // The import answers 422 with one message per bad row: a list, or Laravel's field → messages map.
      const rows = err instanceof ApiError ? (err.errors as unknown as string[] | Record<string, string[]> | undefined) : undefined;
      const list = Array.isArray(rows) ? rows : rows ? Object.values(rows).flat() : [];
      setErrors(list.length > 0 ? list : [errorMessage(err)]);
    } finally {
      setBusy(false);
      onDone();
    }
  };

  return (
    <form onSubmit={upload} className="panel space-y-3 p-4">
      <div>
        <h2 className="text-sm font-semibold">Encode history (CSV)</h2>
        <p className="mt-1 text-xs text-[var(--text-secondary)]">
          One row per request line, with the columns date, item, standard_unit, quantity_requested, quantity_released,
          purpose, event_tag and requesting_division. A file with any bad row is rejected whole.
        </p>
      </div>
      <input ref={input} type="file" accept=".csv,text/csv" required aria-label="History CSV file"
        className="w-full text-sm file:mr-3 file:h-9 file:cursor-pointer file:border file:border-line-strong file:bg-transparent file:px-3 file:text-sm file:text-[var(--text-primary)]" />
      <Button type="submit" disabled={busy}>{busy ? "Importing…" : "Import"}</Button>
      {errors.length > 0 && (
        <ul role="alert" className="max-h-40 space-y-0.5 overflow-y-auto text-xs text-critical">
          {errors.map((e, i) => <li key={i}>{e}</li>)}
        </ul>
      )}
    </form>
  );
}

ReplenishmentInputs.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
