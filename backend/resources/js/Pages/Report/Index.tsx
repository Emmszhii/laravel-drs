import { useState, type FormEvent } from "react";
import { router } from "@inertiajs/react";
import { Download } from "lucide-react";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@/Components/Common/Button";
import { PageHeader } from "@/Components/PageHeader";
import { cn, field, label } from "@/lib/utils";
import { RULE_LABELS, peso } from "@/Pages/Replenishment/shared";

type Row = {
  resource_type: string;
  unit: string;
  on_hand: number;
  reserved: number;
  burn_rate: number;
  quarters_of_cover: number | null;
  target: number | null;
  reorder: number | null;
  reason: string | null;
};
type Report = {
  year: number;
  quarter: number;
  rows: Row[];
  generated_at: string;
  plan: { budget: number; spent: number | null; rule: string; approved_at: string | null } | null;
  fingerprint: string;
  records_ok: boolean;
};
type Props = { year: number; from: number; to: number; reports: Report[]; can: { export: boolean } };

const num = (n: number | null) => (n == null ? "–" : n.toLocaleString("en-PH"));

/**
 * The quarterly burn-rate and reorder report on screen: the same rows the PDF
 * and CSV export print (InventoryBurnRateReportBuilder), for a year and a
 * quarter range, one section per quarter. Downloads follow what's shown.
 */
export default function ReportIndex({ year, from, to, reports, can }: Props) {
  const [form, setForm] = useState({ year, from, to });
  const show = (e: FormEvent) => {
    e.preventDefault();
    router.get("/report", form, { preserveScroll: true });
  };
  const href = (format: "pdf" | "csv") => `/api/replenishment/report?format=${format}&year=${year}&quarter=${from}&quarter_to=${to}`;
  const first = reports[0];
  // Most catalog items have no stock, use or order yet; the PDF prints them all, the screen hides them by default.
  const [activeOnly, setActiveOnly] = useState(true);

  return (
    <div>
      <PageHeader
        title="Report"
        description="Burn rate, quarters of cover and the approved reorder for every item: what the PDF and CSV print."
        actions={can.export && (
          <>
            <a href={href("pdf")} className="inline-flex h-9 items-center gap-1.5 bg-amber px-4 text-sm font-semibold text-[#1a1200] hover:brightness-110">
              <Download size={15} aria-hidden /> PDF
            </a>
            <a href={href("csv")} className="inline-flex h-9 items-center gap-1.5 border border-line-strong px-4 text-sm font-semibold hover:bg-tint">
              <Download size={15} aria-hidden /> CSV
            </a>
          </>
        )}
      />

      <div className="space-y-6 px-4 pb-6 sm:px-6">
        <form onSubmit={show} className="panel flex flex-wrap items-end gap-3 p-4">
          <label className={label}>Year
            <input className={cn(field, "w-28")} type="number" min={2020} max={2100} required value={form.year}
              onChange={(e) => setForm({ ...form, year: Number(e.target.value) })} />
          </label>
          <label className={label}>From
            <select className={cn(field, "w-24")} value={form.from} onChange={(e) => { const f = Number(e.target.value); setForm({ ...form, from: f, to: Math.max(f, form.to) }); }}>
              {[1, 2, 3, 4].map((q) => <option key={q} value={q}>Q{q}</option>)}
            </select>
          </label>
          <label className={label}>To
            <select className={cn(field, "w-24")} value={form.to} onChange={(e) => setForm({ ...form, to: Number(e.target.value) })}>
              {[1, 2, 3, 4].filter((q) => q >= form.from).map((q) => <option key={q} value={q}>Q{q}</option>)}
            </select>
          </label>
          <Button type="submit" variant="ghost">Show</Button>
          {!can.export && <p className="basis-full text-xs text-[var(--text-secondary)]">Downloads are for the Head of Office, Logistics, and Research and Planning.</p>}
        </form>

        {first && (
          <p className={`text-xs ${first.records_ok ? "text-[var(--text-secondary)]" : "text-critical"}`}>
            {first.records_ok ? "Stock records check passed." : "Stock records check failed: run Check records on the Stock page."}{" "}
            Latest fingerprint <span className="break-all font-mono">{first.fingerprint}</span>, generated {new Date(first.generated_at).toLocaleString("en-PH")}.
          </p>
        )}

        <label className="flex items-center gap-2 text-sm">
          <input type="checkbox" checked={activeOnly} onChange={(e) => setActiveOnly(e.target.checked)} />
          Only items with stock, use or a reorder
        </label>

        {reports.map((r) => <QuarterReport key={r.quarter} report={r} activeOnly={activeOnly} />)}
      </div>
    </div>
  );
}

// A reorder of 0 (an item the plan left to Logistics) isn't a reorder.
const active = (row: Row) => Boolean(row.on_hand || row.reserved || row.burn_rate || row.reorder);

function QuarterReport({ report: r, activeOnly }: { report: Report; activeOnly: boolean }) {
  const rows = activeOnly ? r.rows.filter(active) : r.rows;

  return (
    <section className="panel">
      <header className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 border-b border-line px-4 py-3">
        <h2 className="text-base font-semibold">
          {r.year} Q{r.quarter}
          {activeOnly && <span className="ml-2 text-xs font-normal text-[var(--text-secondary)]">{rows.length} of {r.rows.length} items</span>}
        </h2>
        {r.plan ? (
          <p className="text-sm text-[var(--text-secondary)]">
            Approved plan: {r.plan.spent != null ? `${peso(r.plan.spent)} of ` : ""}{peso(r.plan.budget)}
            {" · "}{RULE_LABELS[r.plan.rule] ?? r.plan.rule}
            {r.plan.approved_at ? ` · approved ${new Date(r.plan.approved_at).toLocaleDateString("en-PH")}` : ""}
          </p>
        ) : (
          <p className="text-sm text-amber">No approved plan for this quarter, so there is no reorder yet.</p>
        )}
      </header>
      <div className="overflow-x-auto">
        <table className="w-full min-w-[820px] text-sm">
          <thead className="text-left text-xs text-[var(--text-secondary)]">
            <tr className="border-b border-line">
              <th className="px-4 py-2.5 font-medium">Item</th>
              <th className="px-3 text-right font-medium">On hand</th>
              <th className="px-3 text-right font-medium">Reserved</th>
              <th className="px-3 text-right font-medium">Use per quarter</th>
              <th className="px-3 text-right font-medium">Quarters of cover</th>
              <th className="px-3 text-right font-medium">Target</th>
              <th className="px-3 text-right font-medium">Reorder</th>
              <th className="px-4 font-medium">Reason</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr><td colSpan={8} className="px-4 py-3 text-[var(--text-secondary)]">No item has stock, use or a reorder in this quarter.</td></tr>
            )}
            {rows.map((row) => (
              <tr key={row.resource_type} className="border-b border-line last:border-0">
                <td className="px-4 py-2">
                  {row.resource_type}
                  <span className="ml-1.5 text-xs text-[var(--text-secondary)]">{row.unit}</span>
                </td>
                <td className="px-3 text-right">{num(row.on_hand)}</td>
                <td className="px-3 text-right">{row.reserved ? num(row.reserved) : "–"}</td>
                <td className="px-3 text-right">{row.burn_rate ? num(row.burn_rate) : "–"}</td>
                <td className={`px-3 text-right ${row.quarters_of_cover != null && row.quarters_of_cover < 1 ? "font-semibold text-critical" : ""}`}>
                  {num(row.quarters_of_cover)}
                </td>
                <td className="px-3 text-right">{num(row.target)}</td>
                <td className="px-3 text-right font-semibold">{num(row.reorder)}</td>
                <td className="px-4 text-[var(--text-secondary)]">{row.reason ?? ""}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}

ReportIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;
