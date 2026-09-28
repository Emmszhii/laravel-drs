import { Link, usePage } from "@inertiajs/react";
import { ApiError } from "@/api-service";

/** Real routes (not local tab state) so each subtab is a proper navigable/bookmarkable URL. */
export function IcsTabs() {
  const { url } = usePage();
  const onBorrowed = url.startsWith("/ics-requests/borrowed");
  const tabClass = (active: boolean) =>
    `border-b-2 py-2.5 text-sm font-semibold uppercase tracking-wide ${
      active ? "border-[var(--amber)]" : "border-transparent text-[var(--text-secondary)]"
    }`;

  return (
    <div className="flex gap-5 border-b border-white/10 px-6">
      <Link href="/ics-requests" className={tabClass(!onBorrowed)}>ICS 213-RR requests</Link>
      <Link href="/ics-requests/borrowed" className={tabClass(onBorrowed)}>Borrowed equipment</Link>
    </div>
  );
}

export type Can = { file: boolean; decide: boolean; release: boolean; userId: string };

export type StatusChange = { id: string; from_status: string | null; to_status: string; note: string | null; created_at: string };

export const field = "w-full border border-white/20 bg-transparent px-2 py-1.5 text-sm";

export function message(err: unknown): string {
  return err instanceof ApiError ? err.message : "Something went wrong. Try again.";
}

export function History({ items }: { items: StatusChange[] }) {
  return (
    <ol className="space-y-0.5 text-xs text-[var(--text-secondary)]">
      {items.map((h) => (
        <li key={h.id}>
          {new Date(h.created_at).toLocaleString("en-PH")}: {h.from_status ? `${h.from_status} → ` : ""}{h.to_status}
          {h.note ? ` (${h.note})` : ""}
        </li>
      ))}
    </ol>
  );
}
