import { useEffect, useMemo, useRef, useState, type FormEvent } from "react";
import { Link, router } from "@inertiajs/react";
import { toast } from "sonner";
import { CartesianGrid, Line, LineChart, ReferenceLine, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http, ApiError } from "@/api-service";
import { errorMessage, field } from "@/lib/utils";
import type { Backtest, DataCheckFlag, ReplenishmentRun, ResourceType, VedPolicy } from "@/Types/models";

type Props = {
  runs: Pick<ReplenishmentRun, "id" | "year" | "quarter" | "budget" | "status" | "created_at" | "decided_at">[];
  run: ReplenishmentRun | null;
  items: Pick<ResourceType, "id" | "name" | "unit">[];
  flags: DataCheckFlag[];
  backtest: Backtest | null;
  policies: VedPolicy[];
  defaults: { year: number; quarter: number; budget: number | null };
  can: { run: boolean; encode: boolean; export: boolean };
};

const peso = (n: number) => `₱${Math.round(n).toLocaleString("en-PH")}`;

const STATUS_STYLE: Record<string, string> = {
  queued: "text-[var(--text-secondary)]",
  completed: "text-[var(--amber)]",
  approved: "text-emerald-400",
  rejected: "text-[var(--critical)]",
  failed: "text-[var(--critical)]",
  superseded: "text-[var(--text-secondary)] line-through",
};

const BASIS_NOTE: Record<string, string> = {
  limited_history: "limited history",
  no_history: "set manually",
  no_unit_cost: "no unit cost: set manually",
};

/** Reasons that mean the order needs a human look, shown in the warning color. */
const FLAGGED_REASONS = ["no feasible order", "partly covered: the solver stopped early"];

const VED_LABEL: Record<string, string> = { V: "Vital", E: "Essential", D: "Desirable" };

/** "Vital: covers 3 of the last 4 quarters, weight 3" — the VED policy in plain words. */
const policyText = (p: VedPolicy) =>
  `${VED_LABEL[p.ved_class] ?? p.ved_class}: covers ${p.coverage_k} of the last ${p.coverage_m} quarters, weight ${p.weight}`;

/**
 * Quarterly replenishment dashboard (system-spec.md §7), on the three
 * situation-awareness levels: the recommendation with a reason per item
 * (understand) and the budget curve with "the next ₱10,000 goes to" (project).
 * Logistics only encodes (history upload, data-check flags); the Admin (Head
 * of Office) runs the solver and approves or rejects.
 */
const RULE_LABELS: Record<string, string> = {
  per_item_multiplier: "Per-item multiplier",
  scenario: "Scenario model",
  flat_1_5: "Flat 1.5 rule",
  safety_stock: "Safety stock (mean + 1.65 sd)",
  office_actual: "Office's own orders",
};

export default function ReplenishmentIndex({ runs, run, items, flags, backtest, policies, defaults, can }: Props) {
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
      <div className="flex items-center justify-between border-b border-white/10 px-6 py-3">
        <h1 className="text-lg font-semibold uppercase">Replenishment</h1>
        <span className="text-xs text-[var(--text-secondary)]">
          VED policy: {policies.map(policyText).join(" · ")}
          {policies.every((p) => !p.confirmed_at) && " (proposal, not yet confirmed)"}
        </span>
      </div>

      <div className="grid gap-6 p-6 xl:grid-cols-[1fr_320px]">
        <div className="space-y-6">
          {can.run && <RunForm defaults={defaults} onDone={reload} />}
          <FlagList flags={flags} canResolve={can.encode} onDone={reload} />
          {run ? <RunDetail run={run} names={names} canDecide={can.run} onDone={reload} /> : (
            <p className="text-sm text-[var(--text-secondary)]">No solver run yet.</p>
          )}
          <BacktestPanel backtest={backtest} canRun={can.run} defaultBudget={defaults.budget} onDone={() => router.reload({ only: ["backtest"] })} />
        </div>

        <aside className="space-y-6">
          {can.run && <VedPolicyForm policies={policies} onDone={() => router.reload({ only: ["policies"] })} />}
          {can.encode && <HistoryUpload onDone={reload} />}
          {can.export && <ReportExport year={run?.year ?? defaults.year} quarter={run?.quarter ?? defaults.quarter} />}
          <section>
            <h2 className="mb-2 text-sm font-semibold uppercase">Runs</h2>
            <ul className="divide-y divide-white/10 border border-white/10 text-sm">
              {runs.map((r) => (
                <li key={r.id}>
                  <Link href={`/replenishment?run=${r.id}`} preserveScroll
                    className={`flex justify-between px-3 py-2 hover:bg-white/5 ${r.id === run?.id ? "bg-white/5" : ""}`}>
                    <span>{r.year} Q{r.quarter} · {peso(r.budget)}</span>
                    <span className={STATUS_STYLE[r.status]}>{r.status}</span>
                  </Link>
                </li>
              ))}
              {runs.length === 0 && <li className="px-3 py-2 text-[var(--text-secondary)]">None yet</li>}
            </ul>
          </section>
        </aside>
      </div>
    </div>
  );
}

/** Pick any year and a quarter range (Q1 only, Q1–Q4, …); the file has one section per quarter. */
function ReportExport({ year: y0, quarter: q0 }: { year: number; quarter: number }) {
  const [year, setYear] = useState(y0);
  const [from, setFrom] = useState(q0);
  const [to, setTo] = useState(q0);
  const setRange = (f: number, t: number) => { setFrom(f); setTo(Math.max(f, t)); };

  return (
    <section className="space-y-2 border border-white/10 p-4 text-sm">
      <h2 className="font-semibold uppercase">Report</h2>
      <p className="text-xs text-[var(--text-secondary)]">
        Burn rate, cover and the approved reorder per item, with the stock records' latest fingerprint printed.
      </p>
      <div className="grid grid-cols-3 gap-2">
        <label className="text-xs uppercase text-[var(--text-secondary)]">Year
          <input className={field} type="number" min={2020} max={2100} value={year} onChange={(e) => setYear(Number(e.target.value))} />
        </label>
        <label className="text-xs uppercase text-[var(--text-secondary)]">From
          <select className={field} value={from} onChange={(e) => setRange(Number(e.target.value), to)}>
            {[1, 2, 3, 4].map((q) => <option key={q} value={q} className="bg-black">Q{q}</option>)}
          </select>
        </label>
        <label className="text-xs uppercase text-[var(--text-secondary)]">To
          <select className={field} value={to} onChange={(e) => setTo(Number(e.target.value))}>
            {[1, 2, 3, 4].filter((q) => q >= from).map((q) => <option key={q} value={q} className="bg-black">Q{q}</option>)}
          </select>
        </label>
      </div>
      <div className="flex gap-3">
        {(["pdf", "csv"] as const).map((f) => (
          <a key={f} className="underline hover:text-[var(--amber)]"
            href={`/api/replenishment/report?format=${f}&year=${year}&quarter=${from}&quarter_to=${to}`}>
            Download {f.toUpperCase()}
          </a>
        ))}
      </div>
    </section>
  );
}

function RunForm({ defaults, onDone }: { defaults: Props["defaults"]; onDone: () => void }) {
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
    <form onSubmit={submit} className="grid grid-cols-2 gap-3 border border-white/10 p-4 sm:grid-cols-[100px_90px_1fr_180px_auto] sm:items-end">
      <label className="text-xs uppercase text-[var(--text-secondary)]">Year
        <input className={field} type="number" min={2020} max={2100} required value={form.year}
          onChange={(e) => setForm({ ...form, year: Number(e.target.value) })} />
      </label>
      <label className="text-xs uppercase text-[var(--text-secondary)]">Quarter
        <select className={field} value={form.quarter} onChange={(e) => setForm({ ...form, quarter: Number(e.target.value) })}>
          {[1, 2, 3, 4].map((q) => <option key={q} value={q} className="bg-black">Q{q}</option>)}
        </select>
      </label>
      <label className="col-span-2 text-xs uppercase text-[var(--text-secondary)] sm:col-span-1">Quarterly budget (₱)
        <input className={field} type="number" min={0} step="0.01" required value={form.budget}
          onChange={(e) => setForm({ ...form, budget: e.target.value })} />
      </label>
      <label className="col-span-2 text-xs uppercase text-[var(--text-secondary)] sm:col-span-1">Model
        <select className={field} value={form.rule} onChange={(e) => setForm({ ...form, rule: e.target.value })}>
          <option value="per_item_multiplier" className="bg-black">Per-item multiplier</option>
          <option value="scenario" className="bg-black">Scenario model</option>
        </select>
      </label>
      <Button type="submit" disabled={busy} className="col-span-2 sm:col-span-1">{busy ? "Starting…" : "Run solver"}</Button>
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
    <section className="border border-[var(--amber)]/40 p-4">
      <h2 className="mb-1 text-sm font-semibold uppercase text-[var(--amber)]">Data checks to resolve ({flags.length})</h2>
      <p className="mb-3 text-xs text-[var(--text-secondary)]">
        The solver can't run until Logistics confirms each entry is right or corrects it in the records.
      </p>
      <ul className="space-y-2 text-sm">
        {flags.map((f) => (
          <li key={f.id} className="flex flex-wrap items-center justify-between gap-2">
            <span><strong>{f.resource_type?.name}</strong>, {f.year} Q{f.quarter}: {f.detail}</span>
            {canResolve && (
              <span className="flex gap-2">
                <Button variant="ghost" disabled={busy !== null} onClick={() => resolve(f, "confirmed")}>Confirm</Button>
                <Button variant="ghost" disabled={busy !== null} onClick={() => resolve(f, "corrected")}>Corrected</Button>
              </span>
            )}
          </li>
        ))}
      </ul>
    </section>
  );
}

function RunDetail({ run, names, canDecide, onDone }: {
  run: ReplenishmentRun;
  names: Record<string, Pick<ResourceType, "id" | "name" | "unit">>;
  canDecide: boolean;
  onDone: () => void;
}) {
  const [comment, setComment] = useState("");
  const [busy, setBusy] = useState(false);
  const result = run.result;
  const orders = result?.orders ?? [];
  const manual = orders.filter((o) => o.basis === "no_history" || o.basis === "no_unit_cost");
  const planned = orders.filter((o) => !manual.includes(o));

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
    <section className="space-y-6">
      <div className="flex flex-wrap items-baseline justify-between gap-2">
        <h2 className="text-base font-semibold uppercase">
          {run.year} Q{run.quarter} recommendation · <span className={STATUS_STYLE[run.status]}>{run.status}</span>
          <span className="ml-2 text-xs normal-case text-[var(--text-secondary)]">{RULE_LABELS[run.rule] ?? run.rule}</span>
        </h2>
        {result && (
          <span className="text-sm text-[var(--text-secondary)]">
            Spends {peso(result.spent)} of {peso(run.budget)} · solved in {result.solve_ms} ms
          </span>
        )}
      </div>

      {run.status === "queued" && <p className="text-sm text-[var(--text-secondary)]">The solver is working…</p>}
      {run.error && <p className="text-sm text-[var(--critical)]">{run.error}</p>}
      {run.decision_comment && <p className="text-sm">Admin's comment: {run.decision_comment}</p>}
      {result && !result.space_modeled && <p className="text-xs text-[var(--text-secondary)]">Depot space not modeled.</p>}

      {planned.length > 0 && (
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="text-left text-xs uppercase text-[var(--text-secondary)]">
              <tr className="border-b border-white/10">
                <th className="py-2 pr-3">Item</th><th className="pr-3 text-right">Order</th>
                <th className="pr-3 text-right">Target stock</th><th className="pr-3 text-right">% below target</th><th>Reason</th>
              </tr>
            </thead>
            <tbody>
              {planned.map((o) => (
                <tr key={o.id} className="border-b border-white/5">
                  <td className="py-2 pr-3">
                    {names[o.id]?.name ?? o.id}
                    {BASIS_NOTE[o.basis] && <span className="ml-2 text-xs text-[var(--amber)]">{BASIS_NOTE[o.basis]}</span>}
                  </td>
                  <td className="pr-3 text-right font-semibold">{o.x.toLocaleString()} {names[o.id]?.unit}</td>
                  <td className="pr-3 text-right">{o.target != null ? `${o.target.toLocaleString()} ${names[o.id]?.unit ?? ""}` : "–"}</td>
                  <td className="pr-3 text-right">{o.shortfall ? `${o.shortfall_pct}% below` : "Target met"}</td>
                  <td className={FLAGGED_REASONS.includes(o.reason) ? "text-[var(--amber)]" : ""}>{o.reason}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {manual.length > 0 && (
        <p className="text-sm">
          <span className="text-[var(--amber)]">Set manually by Logistics:</span>{" "}
          {manual.map((o) => names[o.id]?.name ?? o.id).join(", ")}
        </p>
      )}

      {result?.budget_curve && <BudgetCurve points={result.budget_curve} budget={run.budget} />}

      {result?.next_step && (
        <div>
          <h3 className="mb-1 text-sm font-semibold uppercase">The next {peso(result.next_step.amount)} goes to</h3>
          {result.next_step.solved === false ? (
            <p className="text-sm text-[var(--amber)]">No change could be computed: the solver ran out of time on the larger budget.</p>
          ) : result.next_step.changes.length === 0 ? (
            <p className="text-sm text-[var(--text-secondary)]">Nothing: every target is already met.</p>
          ) : (
            <ul className="text-sm">
              {result.next_step.changes.map((c) => (
                <li key={c.id}>
                  {c.delta_x > 0 ? "+" : "−"}{Math.abs(c.delta_x).toLocaleString()} {names[c.id]?.unit} {names[c.id]?.name}
                  <span className="text-[var(--text-secondary)]"> ({c.shortfall_pct_before}% → {c.shortfall_pct_after}% below target)</span>
                </li>
              ))}
            </ul>
          )}
        </div>
      )}

      {canDecide && run.status === "completed" && (
        <div className="flex flex-wrap items-center gap-3 border-t border-white/10 pt-4">
          <input className="min-w-[240px] flex-1 border border-white/20 bg-transparent px-2 py-1.5 text-sm"
            placeholder="Comment (required to reject)" aria-label="Comment (required to reject)" value={comment} onChange={(e) => setComment(e.target.value)} />
          <Button disabled={busy} onClick={() => decide("approve")}>Approve</Button>
          <Button variant="ghost" onClick={() => decide("reject")} disabled={busy || !comment.trim()}>Reject</Button>
        </div>
      )}
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
    <div>
      <h3 className="mb-1 text-sm font-semibold uppercase">Budget curve</h3>
      <p className="mb-2 text-xs text-[var(--text-secondary)]">
        Expected shortage at 50%–150% of the {peso(budget)} budget (dashed line). Where the line flattens, more money stops reducing shortage.
      </p>
      <p className="mb-2 text-xs">{summary}</p>
      {unsolved > 0 && (
        <p className="mb-2 text-xs text-[var(--amber)]">
          {unsolved} of {points.length} budget levels could not be solved within the time limit and are left out of the line.
        </p>
      )}
      <div className="h-64" role="img" aria-label={`Budget curve chart. ${summary}`}>
        <ResponsiveContainer width="100%" height="100%">
          <LineChart data={points} margin={{ left: 16, right: 16, top: 8, bottom: 8 }}>
            <CartesianGrid stroke="rgba(255,255,255,0.08)" />
            <XAxis dataKey="budget" type="number" domain={["dataMin", "dataMax"]} tickFormatter={(v: number) => `₱${Math.round(v / 1000)}k`}
              stroke="var(--text-secondary)" fontSize={12} />
            <YAxis stroke="var(--text-secondary)" fontSize={12} width={56}
              label={{ value: "Expected shortage (quarters of average use)", angle: -90, position: "insideLeft", offset: 0, fontSize: 11, fill: "var(--text-secondary)", style: { textAnchor: "middle" } }} />
            <ReferenceLine x={budget} stroke="var(--text-primary)" strokeDasharray="4 4"
              label={{ value: `Entered budget ${peso(budget)}`, position: "insideTopRight", fontSize: 11, fill: "var(--text-primary)" }} />
            <Tooltip formatter={(v: unknown) => (typeof v !== "number" ? "Not solved in time" : `${v.toFixed(2)} quarters of average use`)} labelFormatter={(v: number) => peso(v)}
              contentStyle={{ background: "#111", border: "1px solid rgba(255,255,255,0.15)" }} />
            <Line type="monotone" dataKey="expected_shortage" name="Expected shortage" stroke="var(--amber)" strokeWidth={2} dot />
          </LineChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}

/**
 * Rolling-origin backtest (Chapter III, Model Evaluation): per-quarter
 * weighted shortage for each rule, typhoon quarters marked, and how many
 * quarters each model beat the flat 1.5 rule.
 */
function BacktestPanel({ backtest, canRun, defaultBudget, onDone }: {
  backtest: Backtest | null;
  canRun: boolean;
  defaultBudget: number | null;
  onDone: () => void;
}) {
  const [budget, setBudget] = useState(String(defaultBudget ?? backtest?.budget ?? ""));
  const [busy, setBusy] = useState(false);

  const run = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    try {
      await http.post("/api/replenishment/backtest", { budget: Number(budget) });
      toast.success("Backtest finished.");
    } catch (err) {
      toast.error(errorMessage(err));
    } finally {
      setBusy(false);
      onDone();
    }
  };

  const rules = backtest ? Object.keys(backtest.result.quarters[0]?.rules ?? {}) : [];
  const s = backtest?.result.summary;

  return (
    <section className="space-y-3 border-t border-white/10 pt-6">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h2 className="text-base font-semibold uppercase">Backtest on past quarters</h2>
          <p className="text-xs text-[var(--text-secondary)]">
            From the 5th encoded quarter on, each rule orders using only earlier quarters and is scored on what was actually used.
            Each cell shows the weighted shortage (lower is better), the share of need that was filled, how many items ran out,
            and the value of the stock left on the shelves at the quarter's end.
          </p>
        </div>
        {canRun && (
          <form onSubmit={run} className="flex items-end gap-2">
            <label className="text-xs uppercase text-[var(--text-secondary)]">Budget per quarter (₱)
              <input className="w-36 border border-white/20 bg-transparent px-2 py-1.5 text-sm" type="number" min={0} required
                value={budget} onChange={(e) => setBudget(e.target.value)} />
            </label>
            <Button type="submit" variant="ghost" disabled={busy}>{busy ? "Running…" : "Run backtest"}</Button>
          </form>
        )}
      </div>

      {backtest && s && (
        <>
          <p className="text-sm">
            Over {s.backtested_quarters} quarters: per-item multiplier beat flat 1.5 in {s.wins_vs_flat.per_item_multiplier}
            {s.target_met.per_item_multiplier ? " (majority ✓)" : ""}; scenario model in {s.wins_vs_flat.scenario}
            {s.target_met.scenario ? " (majority ✓)" : ""}.
          </p>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="text-left text-xs uppercase text-[var(--text-secondary)]">
                <tr className="border-b border-white/10">
                  <th className="py-2 pr-3">Quarter</th>
                  {rules.map((r) => <th key={r} className="pr-3 text-right">{RULE_LABELS[r] ?? r}</th>)}
                </tr>
              </thead>
              <tbody>
                {backtest.result.quarters.map((row) => {
                  const [y, q] = backtest.quarters[row.t] ?? [0, 0];
                  const best = Math.min(...rules.map((r) => row.rules[r].weighted_shortage));

                  return (
                    <tr key={row.t} className="border-b border-white/5">
                      <td className="py-2 pr-3">
                        {y} Q{q}
                        {row.typhoon && <span className="ml-2 text-xs text-[var(--amber)]">event</span>}
                        {row.stockout && <span className="ml-2 text-xs text-[var(--text-secondary)]">use understated</span>}
                      </td>
                      {rules.map((r) => (
                        <td key={r} className={`pr-3 text-right ${row.rules[r].weighted_shortage === best ? "font-semibold text-[var(--amber)]" : ""}`}
                          title={`Fill rate ${Math.round(row.rules[r].fill_rate * 100)}% · ${row.rules[r].items_ran_out} ran out · budget used ${Math.round(row.rules[r].budget_used * 100)}%`}>
                          {row.rules[r].weighted_shortage.toFixed(2)}
                          <span className="block text-xs font-normal text-[var(--text-secondary)]">
                            {Math.round(row.rules[r].fill_rate * 100)}% filled · {row.rules[r].items_ran_out} {row.rules[r].items_ran_out === 1 ? "item" : "items"} ran out
                            · ₱{Math.round(row.rules[r].stock_left_value).toLocaleString()} left over
                          </span>
                        </td>
                      ))}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </>
      )}
    </section>
  );
}

/** Admin: confirm (or edit) Table 4's coverage and weight per VED class, recording who confirmed it and when. */
function VedPolicyForm({ policies, onDone }: { policies: VedPolicy[]; onDone: () => void }) {
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

  const vedField = "w-full border border-white/20 bg-transparent px-1.5 py-1 text-sm";

  return (
    <form onSubmit={save} className="space-y-2 border border-white/10 p-4 text-sm">
      <h2 className="text-sm font-semibold uppercase">VED policy</h2>
      <p className="text-xs text-[var(--text-secondary)]">
        Per class (V = Vital, E = Essential, D = Desirable): the covering level covers k of the last M quarters; a higher weight makes its shortage count more.
      </p>
      <div className="grid grid-cols-[24px_1fr_1fr_1fr] items-center gap-2 text-xs text-[var(--text-secondary)]">
        <span />
        <span>k</span>
        <span>M</span>
        <span>Weight</span>
        {rows.map((r, i) => [
          <span key={`${r.ved_class}-c`} className="font-semibold text-[var(--text-primary)]" title={VED_LABEL[r.ved_class]}>{r.ved_class}</span>,
          <input key={`${r.ved_class}-k`} aria-label={`${VED_LABEL[r.ved_class]}: quarters covered (k)`} className={vedField} type="number" min={1} max={r.coverage_m} required value={r.coverage_k} onChange={(e) => set(i, "coverage_k", e.target.value)} />,
          <input key={`${r.ved_class}-m`} aria-label={`${VED_LABEL[r.ved_class]}: of the last M quarters`} className={vedField} type="number" min={1} max={12} required value={r.coverage_m} onChange={(e) => set(i, "coverage_m", e.target.value)} />,
          <input key={`${r.ved_class}-w`} aria-label={`${VED_LABEL[r.ved_class]}: weight`} className={vedField} type="number" min={0.1} step="0.1" required value={r.weight} onChange={(e) => set(i, "weight", e.target.value)} />,
        ])}
      </div>
      <label className="block text-xs uppercase text-[var(--text-secondary)]">Confirmed by (position)
        <input className={vedField} required value={position} placeholder="e.g. Logistics Officer" onChange={(e) => setPosition(e.target.value)} />
      </label>
      <label className="block text-xs uppercase text-[var(--text-secondary)]">Date confirmed
        <input className={vedField} type="date" required value={date} onChange={(e) => setDate(e.target.value)} />
      </label>
      <Button type="submit" disabled={busy}>{busy ? "Saving…" : "Confirm policy"}</Button>
      {policies[0]?.confirmed_at && (
        <p className="text-xs text-[var(--text-secondary)]">
          Last confirmed {policies[0].confirmed_at.slice(0, 10)} by {policies[0].confirmed_by_position}.
        </p>
      )}
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
    <form onSubmit={upload} className="space-y-2 border border-white/10 p-4">
      <h2 className="text-sm font-semibold uppercase">Encode history (CSV)</h2>
      <p className="text-xs text-[var(--text-secondary)]">
        One row per request line: date, item, standard_unit, quantity_requested, quantity_released, purpose, event_tag,
        requesting_division. A file with any bad row is rejected whole.
      </p>
      <input ref={input} type="file" accept=".csv,text/csv" required aria-label="History CSV file" className="w-full text-xs" />
      <Button type="submit" disabled={busy}>{busy ? "Importing…" : "Import"}</Button>
      {errors.length > 0 && (
        <ul className="max-h-40 overflow-y-auto text-xs text-[var(--critical)]">
          {errors.map((e, i) => <li key={i}>{e}</li>)}
        </ul>
      )}
    </form>
  );
}

ReplenishmentIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
