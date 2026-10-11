import { useState, type FormEvent } from "react";
import { router } from "@inertiajs/react";
import { toast } from "sonner";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { http } from "@/api-service";
import { cn, errorMessage, field, label } from "@/lib/utils";
import type { Backtest } from "@/Types/models";
import { ReplenishmentHeader, RULE_LABELS, peso } from "./shared";

type Props = {
  backtest: Backtest | null;
  defaults: { year: number; quarter: number; budget: number | null };
  can: { run: boolean; encode: boolean; export: boolean };
};

/**
 * Rolling-origin backtest (Chapter III, Model Evaluation): per-quarter
 * weighted shortage for each rule, typhoon quarters marked, and how many
 * quarters each model beat the flat 1.5 rule.
 */
export default function ReplenishmentBacktest({ backtest, defaults, can }: Props) {
  const [budget, setBudget] = useState(String(defaults.budget ?? backtest?.budget ?? ""));
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
      router.reload({ only: ["backtest"] });
    }
  };

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
            <form onSubmit={run} className="flex items-end gap-2">
              <label className={label}>Budget per quarter (₱)
                <input className={cn(field, "w-40")} type="number" min={0} required value={budget} onChange={(e) => setBudget(e.target.value)} />
              </label>
              <Button type="submit" variant="ghost" disabled={busy}>{busy ? "Running…" : "Run backtest"}</Button>
            </form>
          )}
        </div>

        {!backtest && <p className="panel p-6 text-sm text-[var(--text-secondary)]">No backtest yet.{can.run ? " Enter a budget and run one." : ""}</p>}

        {backtest && s && (
          <>
            <p className="text-sm">
              At {peso(backtest.budget)} a quarter, over {s.backtested_quarters} quarters: the per-item multiplier beat flat 1.5 in{" "}
              <strong>{s.wins_vs_flat.per_item_multiplier}</strong>{s.target_met.per_item_multiplier ? " (a majority)" : ""}; the scenario model in{" "}
              <strong>{s.wins_vs_flat.scenario}</strong>{s.target_met.scenario ? " (a majority)" : ""}.
            </p>
            <div className="panel overflow-x-auto">
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
                    const best = Math.min(...rules.map((r) => row.rules[r].weighted_shortage));

                    return (
                      <tr key={row.t} className="border-b border-line align-top last:border-0">
                        <td className="whitespace-nowrap px-4 py-2.5">
                          {y} Q{q}
                          {row.typhoon && <span className="block text-xs text-amber">typhoon event</span>}
                          {row.stockout && <span className="block text-xs text-[var(--text-secondary)]">use understated</span>}
                        </td>
                        {rules.map((r) => {
                          const c = row.rules[r];
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

ReplenishmentBacktest.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
