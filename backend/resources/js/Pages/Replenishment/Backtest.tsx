import { useState, type FormEvent } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { cn, errorMessage, field, label } from "@/lib/utils";
import type { Backtest, BacktestComparisons, BacktestForm } from "@/Types/models";
import { ReplenishmentHeader, RULE_LABELS, peso } from "./shared";

type Props = {
  backtest: Backtest | null;
  eventTags: string[];
  used: { typhoon_tags: string[] | null; items: number; opening_stock_items: number } | null;
  defaults: { year: number; quarter: number; budget: number | null };
  can: { run: boolean; encode: boolean; export: boolean };
};

const FORMS: BacktestForm[] = ["per_item_multiplier", "scenario"];

const BUDGET_SOURCE: Record<string, string> = {
  recorded: "recorded budget",
  actual_order_cost: "cost of the office's order",
  entered: "budget entered here",
};

/**
 * Rolling-origin backtest (Chapter III, Model Evaluation): per-quarter
 * measures for each rule, typhoon and understated-use quarters marked, each
 * model form's wins against every comparison rule (also with equal weights),
 * and each rule's averages with and without the understated quarters.
 */
export default function ReplenishmentBacktest({ backtest, eventTags, used, defaults, can }: Props) {
  const [budget, setBudget] = useState(String(defaults.budget ?? backtest?.budget ?? ""));
  const [typhoons, setTyphoons] = useState<string[]>(used?.typhoon_tags ?? eventTags);
  const [busy, setBusy] = useState(false);

  const run = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    try {
      await http.post("/api/replenishment/backtest", { budget: Number(budget), typhoon_tags: typhoons });
      toast.success("Backtest finished.");
    } catch (err) {
      toast.error(errorMessage(err));
    } finally {
      setBusy(false);
      router.reload({ only: ["backtest", "used"] });
    }
  };

  const toggle = (tag: string) => setTyphoons(typhoons.includes(tag) ? typhoons.filter((t) => t !== tag) : [...typhoons, tag]);

  const rules = backtest ? Object.keys(backtest.result.quarters[0]?.rules ?? {}) : [];
  const s = backtest?.result.summary;

  return (
    <div>
      <ReplenishmentHeader />

      <div className="space-y-6 p-4 sm:p-6">
        <div className="flex flex-wrap items-end justify-between gap-4">
          <p className="max-w-3xl text-sm text-[var(--text-secondary)]">
            From the 5th encoded quarter on, each rule orders using only earlier quarters and is scored on what was actually used.
            Each cell shows the weighted shortage (lower is better, best in the row highlighted), the share of need filled,
            how many items ran out, and the value of the stock left at the quarter's end.
          </p>
          {can.run && (
            <form onSubmit={run} className="space-y-3">
              {eventTags.length > 0 && (
                <fieldset>
                  <legend className="text-xs text-[var(--text-secondary)]">Typhoon quarters are those tagged with:</legend>
                  <div className="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    {eventTags.map((tag) => (
                      <label key={tag} className="flex items-center gap-1.5 text-sm">
                        <input type="checkbox" checked={typhoons.includes(tag)} onChange={() => toggle(tag)} />
                        {tag}
                      </label>
                    ))}
                  </div>
                </fieldset>
              )}
              <div className="flex items-end gap-2">
                <label className={label}>Budget for quarters with none recorded (₱)
                  <input className={cn(field, "w-40")} type="number" min={0} required value={budget} onChange={(e) => setBudget(e.target.value)} />
                </label>
                <Button type="submit" variant="ghost" disabled={busy}>{busy ? "Running…" : "Run backtest"}</Button>
              </div>
            </form>
          )}
        </div>

        {!backtest && <p className="panel p-6 text-sm text-[var(--text-secondary)]">No backtest yet.{can.run ? " Enter a budget and run one." : ""}</p>}

        {backtest && s && !s.comparisons && (
          <p className="panel p-4 text-sm">This backtest was run before every comparison rule was counted. Run it again to see the comparisons.</p>
        )}

        {backtest && s?.comparisons && (
          <>
            <p className="text-sm">
              Over {s.backtested_quarters} quarters,{" "}
              {FORMS.map((f, i) => (
                <span key={f}>
                  {i > 0 && "; "}the {RULE_LABELS[f].toLowerCase()} <strong>{s.target_met[f] ? "beat" : "did not beat"}</strong> every comparison rule in a majority of quarters
                </span>
              ))}.
              {used && (
                <span className="text-[var(--text-secondary)]">
                  {" "}Opening stock was recorded for {used.opening_stock_items} of {used.items} items; the rest started at zero.
                </span>
              )}
            </p>

            <div className="grid gap-6 lg:grid-cols-2">
              <Comparisons title="Wins by weighted shortage" comparisons={s.comparisons} equal={backtest.result.equal_weights} />
              {s.means && <Means means={s.means} rules={rules} />}
            </div>

            <div className="panel table-scroll">
              <table className="w-full min-w-[760px] text-sm">
                <thead className="text-left text-xs text-[var(--text-secondary)]">
                  <tr className="border-b border-line">
                    <th className="px-4 py-2.5 font-medium">Quarter</th>
                    {rules.map((r) => <th key={r} className="px-3 text-right font-medium">{RULE_LABELS[r] ?? r}</th>)}
                  </tr>
                </thead>
                <tbody>
                  {backtest.result.quarters.map((row) => {
                    const [y, q] = backtest.quarters[row.t] ?? [0, 0];
                    const best = Math.min(...rules.flatMap((r) => (row.rules[r] ? [row.rules[r]!.weighted_shortage] : [])));

                    return (
                      <tr key={row.t} className="border-b border-line align-top last:border-0">
                        <td className="whitespace-nowrap px-4 py-2.5">
                          {y} Q{q}
                          {row.typhoon && <span className="block text-xs text-amber">typhoon quarter</span>}
                          {row.stockout && <span className="block text-xs text-[var(--text-secondary)]">use understated</span>}
                          <span className="block text-xs text-[var(--text-secondary)]">
                            {peso(row.budget)}{row.budget_source ? ` · ${BUDGET_SOURCE[row.budget_source]}` : ""}
                          </span>
                        </td>
                        {rules.map((r) => {
                          const c = row.rules[r];
                          if (!c) {
                            return <td key={r} className="px-3 py-2.5 text-right text-xs text-[var(--text-secondary)]">not recorded</td>;
                          }
                          return (
                            <td key={r} className={`px-3 py-2.5 text-right ${c.weighted_shortage === best ? "bg-tint" : ""}`}>
                              <span className={c.weighted_shortage === best ? "font-semibold text-amber" : ""}>{c.weighted_shortage.toFixed(2)}</span>
                              <span className="block text-xs text-[var(--text-secondary)]">
                                {Math.round(c.fill_rate * 100)}% filled · {c.items_ran_out} ran out
                              </span>
                              <span className="block text-xs text-[var(--text-secondary)]">{peso(c.stock_left_value)} left</span>
                            </td>
                          );
                        })}
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </>
        )}
      </div>
    </div>
  );
}

/** Each model form against each comparison rule, over the quarters both were scored in; equal weights beside it. */
function Comparisons({ title, comparisons, equal }: { title: string; comparisons: BacktestComparisons; equal?: BacktestComparisons }) {
  return (
    <div className="panel table-scroll">
      <table className="w-full text-sm">
        <caption className="px-4 pt-3 text-left text-sm font-semibold">{title}</caption>
        <thead className="text-left text-xs text-[var(--text-secondary)]">
          <tr className="border-b border-line">
            <th className="px-4 py-2.5 font-medium">Model against</th>
            <th className="px-3 text-right font-medium">Won / tied / lost</th>
            <th className="px-3 text-right font-medium">Equal weights</th>
          </tr>
        </thead>
        <tbody>
          {FORMS.flatMap((f) => Object.entries(comparisons[f]).map(([other, c]) => {
            const e = equal?.[f]?.[other];
            return (
              <tr key={`${f}-${other}`} className="border-b border-line last:border-0">
                <td className="px-4 py-2">{RULE_LABELS[f]} <span className="text-[var(--text-secondary)]">vs {RULE_LABELS[other] ?? other}</span></td>
                <td className="px-3 text-right">
                  {c.wins} / {c.ties} / {c.losses} <span className="text-xs text-[var(--text-secondary)]">of {c.scored}</span>
                  {c.majority && <span className="ml-1 text-xs font-semibold text-ok">majority</span>}
                </td>
                <td className="px-3 text-right text-[var(--text-secondary)]">{e ? `${e.wins} / ${e.ties} / ${e.losses}` : "—"}</td>
              </tr>
            );
          }))}
        </tbody>
      </table>
    </div>
  );
}

/** Mean weighted shortage and fill rate per rule, with and without the quarters whose use is understated. */
function Means({ means, rules }: { means: NonNullable<Backtest["result"]["summary"]["means"]>; rules: string[] }) {
  const fmt = (v: number | null | undefined, pct = false) => (v == null ? "—" : pct ? `${Math.round(v * 100)}%` : v.toFixed(2));

  return (
    <div className="panel table-scroll">
      <table className="w-full text-sm">
        <caption className="px-4 pt-3 text-left text-sm font-semibold">Averages, all quarters / without understated ones</caption>
        <thead className="text-left text-xs text-[var(--text-secondary)]">
          <tr className="border-b border-line">
            <th className="px-4 py-2.5 font-medium">Rule</th>
            <th className="px-3 text-right font-medium">Weighted shortage</th>
            <th className="px-3 text-right font-medium">Filled</th>
          </tr>
        </thead>
        <tbody>
          {rules.map((r) => {
            const m = means[r];
            return (
              <tr key={r} className="border-b border-line last:border-0">
                <td className="px-4 py-2">{RULE_LABELS[r] ?? r}</td>
                <td className="px-3 text-right">{fmt(m?.all.weighted_shortage)} / {fmt(m?.without_stockout.weighted_shortage)}</td>
                <td className="px-3 text-right">{fmt(m?.all.fill_rate, true)} / {fmt(m?.without_stockout.fill_rate, true)}</td>
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}

ReplenishmentBacktest.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
