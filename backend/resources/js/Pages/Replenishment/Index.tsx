import { useEffect, useMemo, useState, type FormEvent } from "react";
import { Link, router } from "@inertiajs/react";
import { toast } from "sonner";
import { CartesianGrid, Line, LineChart, ReferenceLine, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { errorMessage, field, label } from "@/lib/utils";
import type { DataCheckFlag, ReplenishmentRun, ResourceType, VedPolicy } from "@/Types/models";
import { ReplenishmentHeader, RULE_LABELS, RunStatus, peso } from "./shared";

type Props = {
  runs: Pick<ReplenishmentRun, "id" | "year" | "quarter" | "budget" | "status" | "rule" | "created_at" | "decided_at">[];
  run: ReplenishmentRun | null;
  items: Pick<ResourceType, "id" | "name" | "unit">[];
  flags: DataCheckFlag[];
  policies: VedPolicy[];
  defaults: { year: number; quarter: number; budget: number | null };
  can: { run: boolean; encode: boolean; export: boolean };
};

type Names = Record<string, Pick<ResourceType, "id" | "name" | "unit">>;

const BASIS_NOTE: Record<string, string> = {
  limited_history: "limited history",
  no_history: "set manually",
  no_unit_cost: "no unit cost: set manually",
};

/** Reasons that mean the order needs a human look, shown in the warning color. */
const FLAGGED_REASONS = ["no feasible order", "partly covered: the solver stopped early"];

const when = (iso: string) => new Date(iso).toLocaleString("en-PH", { month: "short", day: "numeric", hour: "numeric", minute: "2-digit" });

/**
 * Quarterly replenishment dashboard (system-spec.md §7), on the three
 * situation-awareness levels: the recommendation with a reason per item
 * (understand) and the budget curve with "the next ₱10,000 goes to" (project).
 * Logistics only encodes (history upload, data-check flags); the Admin (Head
 * of Office) runs the solver and approves or rejects. The backtest and the
 * policy/history inputs are the other two subtabs; the report is its own page.
 */
export default function ReplenishmentIndex({ runs, run, items, flags, policies, defaults, can }: Props) {
  const names = useMemo(() => Object.fromEntries(items.map((i) => [i.id, i])), [items]);
  const reload = () => router.reload({ only: ["runs", "run", "flags"] });

  // Live refresh when a run completes or a decision is made (Reverb).
  useEffect(() => {
    const channel = window.Echo?.channel("replenishment");
    channel?.listen(".run.updated", reload);

    return () => window.Echo?.leave("replenishment");
  }, []);

  return (
    <div>
      <ReplenishmentHeader policies={policies} />

      <div className="grid gap-6 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div className="min-w-0 space-y-6">
          <FlagList flags={flags} canResolve={can.encode} onDone={reload} />
          {can.run && <RunForm defaults={defaults} blocked={flags.length > 0} onDone={reload} />}
          {run ? <RunDetail run={run} names={names} canDecide={can.run} onDone={reload} /> : (
            <p className="panel p-6 text-sm text-[var(--text-secondary)]">
              No recommendation yet. {can.run ? "Enter the quarter's budget above and run the solver." : "The Head of Office runs the solver once the quarter's budget is set."}
            </p>
          )}
        </div>

        <aside className="min-w-0">
          <RunList runs={runs} current={run?.id} />
        </aside>
      </div>
    </div>
  );
}

function RunList({ runs, current }: { runs: Props["runs"]; current?: string }) {
  return (
    <section className="panel">
      <h2 className="border-b border-line px-4 py-3 text-sm font-semibold">Past runs</h2>
      <ul className="divide-y divide-line text-sm">
        {runs.map((r) => (
          <li key={r.id}>
            <Link href={`/replenishment?run=${r.id}`} preserveScroll aria-current={r.id === current ? "true" : undefined}
              className={`block border-l-2 px-4 py-2.5 hover:bg-tint ${r.id === current ? "border-amber bg-tint" : "border-transparent"}`}>
              <span className="flex items-center justify-between gap-2">
                <span className="font-medium">{r.year} Q{r.quarter} · {peso(r.budget)}</span>
                <RunStatus status={r.status} />
              </span>
              <span className="mt-0.5 block text-xs text-[var(--text-secondary)]">
                {when(r.created_at)} · {RULE_LABELS[r.rule] ?? r.rule}
              </span>
            </Link>
          </li>
        ))}
        {runs.length === 0 && <li className="px-4 py-3 text-[var(--text-secondary)]">None yet.</li>}
      </ul>
    </section>
  );
}

function RunForm({ defaults, blocked, onDone }: { defaults: Props["defaults"]; blocked: boolean; onDone: () => void }) {
  const [form, setForm] = useState({ year: defaults.year, quarter: defaults.quarter, budget: defaults.budget ?? "", rule: "per_item_multiplier" });
  const [busy, setBusy] = useState(false);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    try {
      await http.post("/api/replenishment/runs", form);
      toast.success("Solver started. The recommendation appears here when it finishes.");
    } catch (err) {
      toast.error(errorMessage(err));
    } finally {
      setBusy(false);
      onDone();
    }
  };

  return (
    <form onSubmit={submit} className="panel p-4">
      <h2 className="mb-3 text-sm font-semibold">Run the solver</h2>
      <div className="grid grid-cols-2 gap-3 sm:grid-cols-[100px_90px_minmax(140px,1fr)_minmax(160px,1fr)_auto] sm:items-end">
        <label className={label}>Year
          <input className={field} type="number" min={2020} max={2100} required value={form.year}
            onChange={(e) => setForm({ ...form, year: Number(e.target.value) })} />
        </label>
        <label className={label}>Quarter
          <select className={field} value={form.quarter} onChange={(e) => setForm({ ...form, quarter: Number(e.target.value) })}>
            {[1, 2, 3, 4].map((q) => <option key={q} value={q}>Q{q}</option>)}
          </select>
        </label>
        <label className={`${label} col-span-2 sm:col-span-1`}>Quarterly budget (₱)
          <input className={field} type="number" min={0} step="0.01" required placeholder="e.g. 400000" value={form.budget}
            onChange={(e) => setForm({ ...form, budget: e.target.value })} />
        </label>
        <label className={`${label} col-span-2 sm:col-span-1`}>Model
          <select className={field} value={form.rule} onChange={(e) => setForm({ ...form, rule: e.target.value })}>
            <option value="per_item_multiplier">Per-item multiplier</option>
            <option value="scenario">Scenario model</option>
          </select>
        </label>
        <Button type="submit" disabled={busy || blocked} title={blocked ? "Resolve the data checks first" : undefined} className="col-span-2 sm:col-span-1">
          {busy ? "Starting…" : "Run solver"}
        </Button>
      </div>
    </form>
  );
}

function FlagList({ flags, canResolve, onDone }: { flags: DataCheckFlag[]; canResolve: boolean; onDone: () => void }) {
  const [busy, setBusy] = useState<string | null>(null);

  if (flags.length === 0) {
    return null;
  }

  const resolve = async (flag: DataCheckFlag, resolution: "confirmed" | "corrected") => {
    if (busy) return;
    setBusy(flag.id);
    try {
      await http.post(`/api/replenishment/flags/${flag.id}/resolve`, { resolution });
    } catch (err) {
      toast.error(errorMessage(err));
    }
    setBusy(null);
    onDone();
  };

  return (
    <section className="panel border-amber p-4" role="alert">
      <h2 className="text-sm font-semibold text-amber">{flags.length} data check{flags.length === 1 ? "" : "s"} to resolve</h2>
      <p className="mb-3 mt-1 text-xs text-[var(--text-secondary)]">
        The solver can't run until Logistics confirms each entry is right or corrects it in the records.
      </p>
      <ul className="divide-y divide-line text-sm">
        {flags.map((f) => (
          <li key={f.id} className="flex flex-wrap items-center justify-between gap-2 py-2">
            <span><strong>{f.resource_type?.name}</strong>, {f.year} Q{f.quarter}: {f.detail}</span>
            {canResolve && (
              <span className="flex gap-2">
                <Button variant="ghost" disabled={busy !== null} onClick={() => resolve(f, "confirmed")}>Entry is right</Button>
                <Button variant="ghost" disabled={busy !== null} onClick={() => resolve(f, "corrected")}>Corrected</Button>
              </span>
            )}
          </li>
        ))}
      </ul>
    </section>
  );
}

function RunDetail({ run, names, canDecide, onDone }: { run: ReplenishmentRun; names: Names; canDecide: boolean; onDone: () => void }) {
  const result = run.result;
  const orders = result?.orders ?? [];
  const manual = orders.filter((o) => o.basis === "no_history" || o.basis === "no_unit_cost");
  const planned = orders.filter((o) => !manual.includes(o));
  const below = planned.filter((o) => o.shortfall).length;

  return (
    <section className="space-y-4">
      <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
        <h2 className="text-lg font-semibold">{run.year} Q{run.quarter} recommendation</h2>
        <RunStatus status={run.status} />
        <span className="text-xs text-[var(--text-secondary)]">
          {RULE_LABELS[run.rule] ?? run.rule} · run {when(run.created_at)}{result ? ` · solved in ${result.solve_ms} ms` : ""}
        </span>
      </div>

      {run.status === "queued" && <p className="panel p-4 text-sm text-[var(--text-secondary)]">The solver is working. This page updates when it finishes.</p>}
      {run.error && <p className="panel border-critical p-4 text-sm text-critical">{run.error}</p>}
      {run.decision_comment && <p className="text-sm"><span className="text-[var(--text-secondary)]">Head of Office's comment:</span> {run.decision_comment}</p>}

      {result && (
        <div className="panel grid gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-center md:gap-8">
          <BudgetMeter spent={result.spent} budget={run.budget} />
          <dl className="grid grid-cols-3 gap-6 text-sm">
            <div><dt className="text-xs text-[var(--text-secondary)]">Items planned</dt><dd className="text-lg font-semibold">{planned.length}</dd></div>
            <div><dt className="text-xs text-[var(--text-secondary)]">Below target</dt><dd className={`text-lg font-semibold ${below ? "text-amber" : ""}`}>{below}</dd></div>
            <div><dt className="text-xs text-[var(--text-secondary)]">Set manually</dt><dd className="text-lg font-semibold">{manual.length}</dd></div>
          </dl>
        </div>
      )}

      {run.status === "completed" && (canDecide
        ? <DecisionBar run={run} onDone={onDone} />
        : <p className="text-sm text-[var(--text-secondary)]">Waiting for the Head of Office to approve or reject this plan.</p>)}

      {planned.length > 0 && (
        <div className="panel overflow-x-auto">
          <table className="w-full min-w-[640px] text-sm">
            <thead className="text-left text-xs text-[var(--text-secondary)]">
              <tr className="border-b border-line">
                <th className="px-4 py-2.5 font-medium">Item</th>
                <th className="px-3 text-right font-medium">Order</th>
                <th className="px-3 text-right font-medium">Target stock</th>
                <th className="px-3 text-right font-medium">Below target</th>
                <th className="px-4 font-medium">Reason</th>
              </tr>
            </thead>
            <tbody>
              {planned.map((o) => (
                <tr key={o.id} className="border-b border-line last:border-0">
                  <td className="px-4 py-2.5">
                    {names[o.id]?.name ?? o.id}
                    {BASIS_NOTE[o.basis] && <span className="ml-2 text-xs text-amber">{BASIS_NOTE[o.basis]}</span>}
                  </td>
                  <td className="whitespace-nowrap px-3 text-right font-semibold">{o.x.toLocaleString()} {names[o.id]?.unit}</td>
                  <td className="whitespace-nowrap px-3 text-right">{o.target != null ? `${o.target.toLocaleString()} ${names[o.id]?.unit ?? ""}` : "–"}</td>
                  <td className={`whitespace-nowrap px-3 text-right ${o.shortfall ? "text-amber" : "text-[var(--text-secondary)]"}`}>{o.shortfall ? `${o.shortfall_pct}%` : "Met"}</td>
                  <td className={`px-4 ${FLAGGED_REASONS.includes(o.reason) ? "text-amber" : ""}`}>{o.reason}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {manual.length > 0 && (
        <details className="panel p-4 text-sm">
          <summary className="cursor-pointer font-medium">
            {manual.length} item{manual.length === 1 ? "" : "s"} for Logistics to set by hand
            <span className="ml-1 font-normal text-[var(--text-secondary)]">(no history or no unit cost, so the solver can't plan them)</span>
          </summary>
          <p className="mt-2 leading-relaxed text-[var(--text-secondary)]">{manual.map((o) => names[o.id]?.name ?? o.id).join(", ")}</p>
        </details>
      )}

      {result && !result.space_modeled && <p className="text-xs text-[var(--text-secondary)]">Depot space is not modeled: give every depot a free space and every item a space per unit to include it.</p>}

      {(result?.budget_curve || result?.next_step) && (
        <div className="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_320px]">
          {result.budget_curve && <BudgetCurve points={result.budget_curve} budget={run.budget} />}
          {result.next_step && <NextStep step={result.next_step} names={names} />}
        </div>
      )}
    </section>
  );
}

function BudgetMeter({ spent, budget }: { spent: number; budget: number }) {
  const pct = budget > 0 ? Math.min(100, (spent / budget) * 100) : 0;

  return (
    <div>
      <div className="flex flex-wrap items-baseline gap-x-2">
        <span className="text-2xl font-semibold tabular-nums">{peso(spent)}</span>
        <span className="text-sm text-[var(--text-secondary)]">of the {peso(budget)} budget ({Math.round(pct)}%)</span>
      </div>
      <div className="mt-2 h-2 bg-panel-raised" role="meter" aria-label="Budget spent" aria-valuemin={0} aria-valuemax={budget} aria-valuenow={spent}>
        <div className="h-full bg-amber" style={{ width: `${pct}%` }} />
      </div>
    </div>
  );
}

function DecisionBar({ run, onDone }: { run: ReplenishmentRun; onDone: () => void }) {
  const [comment, setComment] = useState("");
  const [busy, setBusy] = useState(false);

  const decide = async (action: "approve" | "reject") => {
    if (busy) return;
    setBusy(true);
    try {
      await http.post(`/api/replenishment/runs/${run.id}/${action}`, { comment: comment || undefined });
      toast.success(action === "approve" ? "Approved. This is now the quarter's purchase plan." : "Rejected.");
      setComment("");
    } catch (err) {
      toast.error(errorMessage(err));
    }
    setBusy(false);
    onDone();
  };

  return (
    <div className="panel flex flex-wrap items-end gap-3 border-l-2 border-l-amber p-4">
      <label className={`${label} min-w-[220px] flex-1`}>Comment (required to reject)
        <input className={field} value={comment} onChange={(e) => setComment(e.target.value)} />
      </label>
      <Button variant="ghost" onClick={() => decide("reject")} disabled={busy || !comment.trim()}>Reject</Button>
      <Button disabled={busy} onClick={() => decide("approve")}>Approve plan</Button>
    </div>
  );
}

function NextStep({ step, names }: { step: NonNullable<NonNullable<ReplenishmentRun["result"]>["next_step"]>; names: Names }) {
  return (
    <section className="panel p-4">
      <h3 className="text-sm font-semibold">The next {peso(step.amount)} goes to</h3>
      <div className="mt-2 text-sm">
        {step.solved === false ? (
          <p className="text-amber">No change could be computed: the solver ran out of time on the larger budget.</p>
        ) : step.changes.length === 0 ? (
          <p className="text-[var(--text-secondary)]">Nothing: every target is already met.</p>
        ) : (
          <ul className="space-y-1.5">
            {step.changes.map((c) => (
              <li key={c.id}>
                <span className="font-medium">{c.delta_x > 0 ? "+" : "−"}{Math.abs(c.delta_x).toLocaleString()} {names[c.id]?.unit}</span> {names[c.id]?.name}
                <span className="block text-xs text-[var(--text-secondary)]">{c.shortfall_pct_before}% → {c.shortfall_pct_after}% below target</span>
              </li>
            ))}
          </ul>
        )}
      </div>
    </section>
  );
}

function BudgetCurve({ points, budget }: { points: NonNullable<ReplenishmentRun["result"]>["budget_curve"] & object; budget: number }) {
  // Levels the solver didn't finish in time carry a null shortage: Recharts leaves a gap there, never a 0.
  const solved = points.filter((p): p is typeof p & { expected_shortage: number } => p.solved !== false && p.expected_shortage != null);
  const unsolved = points.length - solved.length;
  const first = solved[0];
  const lastPoint = solved[solved.length - 1];
  const atBudget = solved.reduce((a, b) => (Math.abs(b.budget - budget) < Math.abs(a.budget - budget) ? b : a), first);
  const summary = first && lastPoint && atBudget
    ? `Expected shortage goes from ${first.expected_shortage.toFixed(2)} quarters of average use at ${peso(first.budget)} to ${lastPoint.expected_shortage.toFixed(2)} at ${peso(lastPoint.budget)}; at the entered budget of ${peso(budget)} it is ${atBudget.expected_shortage.toFixed(2)}.`
    : "The solver could not compute any point of the budget curve.";

  return (
    <section className="panel min-w-0 p-4">
      <h3 className="text-sm font-semibold">Budget curve</h3>
      <p className="mt-1 text-xs text-[var(--text-secondary)]">
        Expected shortage at 50%–150% of the budget. Where the line flattens, more money stops reducing shortage.
      </p>
      <p className="mt-2 text-xs">{summary}</p>
      {unsolved > 0 && (
        <p className="mt-2 text-xs text-amber">
          {unsolved} of {points.length} budget levels could not be solved within the time limit and are left out of the line.
        </p>
      )}
      <div className="mt-3 h-64" role="img" aria-label={`Budget curve chart. ${summary}`}>
        <ResponsiveContainer width="100%" height="100%">
          <LineChart data={points} margin={{ left: 8, right: 16, top: 8, bottom: 8 }}>
            <CartesianGrid stroke="var(--line)" />
            <XAxis dataKey="budget" type="number" domain={["dataMin", "dataMax"]} tickFormatter={(v: number) => `₱${Math.round(v / 1000)}k`}
              stroke="var(--text-secondary)" fontSize={12} />
            <YAxis stroke="var(--text-secondary)" fontSize={12} width={48}
              label={{ value: "Quarters short", angle: -90, position: "insideLeft", offset: 8, fontSize: 11, fill: "var(--text-secondary)", style: { textAnchor: "middle" } }} />
            <ReferenceLine x={budget} stroke="var(--text-primary)" strokeDasharray="4 4"
              label={{ value: `Budget ${peso(budget)}`, position: "insideTopRight", fontSize: 11, fill: "var(--text-primary)" }} />
            <Tooltip formatter={(v: unknown) => (typeof v !== "number" ? "Not solved in time" : `${v.toFixed(2)} quarters of average use`)} labelFormatter={(v: number) => peso(v)}
              contentStyle={{ background: "var(--bg-panel-raised)", border: "1px solid var(--line-strong)", color: "var(--text-primary)" }} />
            <Line type="monotone" dataKey="expected_shortage" name="Expected shortage" stroke="var(--amber)" strokeWidth={2} dot={{ r: 2.5 }} />
          </LineChart>
        </ResponsiveContainer>
      </div>
    </section>
  );
}

ReplenishmentIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
