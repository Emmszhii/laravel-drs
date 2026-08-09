import { useState } from "react";
import { router } from "@inertiajs/react";
import Button from "../../Components/Common/Button";
import type { OptimizationRun } from "@/Types/models";

type Props = { disasterId: string; latestRun?: OptimizationRun | null };

const ALGORITHM_LABELS: Record<string, string> = {
  pulp_linear_programming: "Linear programming (PuLP)",
  greedy_nearest_depot_fallback: "Greedy nearest-depot (fallback)",
};

/**
 * Lets a coordinator manually re-run the optimizer for a given incident
 * (in addition to the automatic trigger on incident creation/escalation —
 * see App\Jobs\RunOptimizationJob and the Automation recommendations in SUMMARY.md).
 */
export default function TriggerSolver({ disasterId, latestRun }: Props) {
  const [loading, setLoading] = useState(false);

  const runOptimization = () => {
    setLoading(true);
    router.post(
      `/incidents/${disasterId}/optimize`,
      {},
      { onFinish: () => setLoading(false) }
    );
  };

  const isFallback = latestRun?.algorithm_used === "greedy_nearest_depot_fallback";

  return (
    <div className="p-6">
      <h2 className="mb-4 text-lg font-semibold uppercase">Run optimization</h2>
      <Button onClick={runOptimization} disabled={loading}>
        {loading ? "Running…" : "Re-run allocation solver"}
      </Button>

      {latestRun && (
        <div className="mt-4 text-sm text-[var(--text-secondary)]">
          <p>
            Latest run:{" "}
            <span className="text-[var(--text-primary)]">
              {ALGORITHM_LABELS[latestRun.algorithm_used] ?? latestRun.algorithm_used}
            </span>
            {latestRun.objective_value != null && (
              <> · unmet demand {latestRun.objective_value}</>
            )}
          </p>
          {isFallback && (
            <p className="mt-1 text-[var(--amber)]">
              Solver service unavailable — used fallback heuristic instead of the
              linear-programming solver.
            </p>
          )}
        </div>
      )}
    </div>
  );
}
