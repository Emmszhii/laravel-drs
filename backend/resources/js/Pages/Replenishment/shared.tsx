import { Link } from "@inertiajs/react";
import { PageHeader } from "@/Components/PageHeader";
import { RouteTabs } from "@/Components/RouteTabs";
import { StatusBadge, type Tone } from "@/Components/StatusBadge";
import type { VedPolicy } from "@/Types/models";

export const peso = (n: number) => `₱${Math.round(n).toLocaleString("en-PH")}`;

export const RULE_LABELS: Record<string, string> = {
  per_item_multiplier: "Per-item multiplier",
  scenario: "Scenario model",
  flat_1_5: "Flat 1.5 rule",
  safety_stock: "Safety stock (mean + 1.65 sd)",
  office_actual: "Office's own orders",
};

export const VED_LABEL: Record<string, string> = { V: "Vital", E: "Essential", D: "Desirable" };

const RUN_STATUS: Record<string, { tone: Tone; label: string }> = {
  queued: { tone: "neutral", label: "Solving" },
  completed: { tone: "amber", label: "Awaiting decision" },
  approved: { tone: "ok", label: "Approved" },
  rejected: { tone: "critical", label: "Rejected" },
  failed: { tone: "critical", label: "Failed" },
  superseded: { tone: "neutral", label: "Superseded" },
};

export function RunStatus({ status }: { status: string }) {
  const s = RUN_STATUS[status] ?? { tone: "neutral" as Tone, label: status };
  return <StatusBadge tone={s.tone}>{s.label}</StatusBadge>;
}

/** Title, the policy's state, and the three subtabs every Replenishment page shares. */
export function ReplenishmentHeader({ policies }: { policies?: VedPolicy[] }) {
  const confirmed = policies?.find((p) => p.confirmed_at);

  return (
    <>
      <PageHeader
        title="Replenishment"
        description="Plan the quarter's purchases from the office's own history, within the budget, with a reason for every item."
        actions={policies && (
          <Link href="/replenishment/inputs" className="inline-block py-2.5 text-xs text-[var(--text-secondary)] underline-offset-2 hover:underline">
            {confirmed
              ? `VED policy confirmed ${confirmed.confirmed_at!.slice(0, 10)}`
              : <span className="text-amber">VED policy not yet confirmed</span>}
          </Link>
        )}
      />
      <RouteTabs tabs={[
        { href: "/replenishment", label: "Recommendation" },
        { href: "/replenishment/backtest", label: "Backtest" },
        { href: "/replenishment/inputs", label: "Policy and history" },
      ]} />
    </>
  );
}
