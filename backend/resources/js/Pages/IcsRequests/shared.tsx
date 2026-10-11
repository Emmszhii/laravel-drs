import { RouteTabs } from "@/Components/RouteTabs";

/** Real routes (not local tab state) so each subtab is a proper navigable/bookmarkable URL. */
export function IcsTabs() {
  return <RouteTabs tabs={[{ href: "/ics-requests", label: "Requests" }, { href: "/ics-requests/borrowed", label: "Borrowed equipment" }]} />;
}

export type Can = { file: boolean; decide: boolean; release: boolean; userId: string };

export type StatusChange = {
  id: string;
  from_status: string | null;
  to_status: string;
  note: string | null;
  created_at: string;
  user?: { name: string; position: string | null } | null;
};

export { field, errorMessage as message } from "@/lib/utils";

/** Each change with who made it and their position: the digital stand-in for the form's signature lines. */
export function History({ items }: { items: StatusChange[] }) {
  return (
    <ol className="space-y-0.5 text-xs text-[var(--text-secondary)]">
      {items.map((h) => (
        <li key={h.id}>
          {new Date(h.created_at).toLocaleString("en-PH")}: {h.from_status ? `${h.from_status} → ` : ""}{h.to_status}
          {h.user ? ` by ${h.user.name}${h.user.position ? `, ${h.user.position}` : ""}` : ""}
          {h.note ? ` (${h.note})` : ""}
        </li>
      ))}
    </ol>
  );
}

/** Division suggestions for the free-text division/team and borrower-office inputs. */
export function DivisionList({ divisions }: { divisions: string[] }) {
  return <datalist id="divisions">{divisions.map((d) => <option key={d} value={d} />)}</datalist>;
}
