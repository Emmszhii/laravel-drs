import { useState } from "react";
import { router } from "@inertiajs/react";
import Button from "../../Components/Common/Button";

type Props = { disasterId: string };

/**
 * Lets a coordinator manually re-run the optimizer for a given incident
 * (in addition to the automatic trigger on incident creation/escalation —
 * see App\Jobs\RunOptimizationJob and the Automation recommendations in SUMMARY.md).
 */
export default function TriggerSolver({ disasterId }: Props) {
  const [loading, setLoading] = useState(false);

  const runOptimization = () => {
    setLoading(true);
    router.post(
      `/incidents/${disasterId}/optimize`,
      {},
      { onFinish: () => setLoading(false) }
    );
  };

  return (
    <div className="p-6">
      <h2 className="mb-4 text-lg font-semibold uppercase">Run optimization</h2>
      <Button onClick={runOptimization} disabled={loading}>
        {loading ? "Running…" : "Re-run allocation solver"}
      </Button>
      {/* Results table / suggested allocations render here once the job completes */}
    </div>
  );
}
