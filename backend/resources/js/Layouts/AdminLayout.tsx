import { PropsWithChildren } from "react";
import { Link, usePage } from "@inertiajs/react";

const navItems = [
  { href: "/", label: "Dashboard" },
  { href: "/incidents", label: "Incidents" },
  { href: "/resources", label: "Resources" },
  { href: "/depots", label: "Depots" },
  { href: "/reports", label: "Reports" },
  { href: "/settings/users", label: "Settings" },
];

/**
 * Sidebar shell for admin/developer roles — mirrors the design prototype's
 * `.gnav` (Dashboard/Incidents/Resources/Depots/Reports/Settings). Non-admin
 * roles keep the plain AppLayout instead; see Dashboard.tsx's `.layout`.
 */
export default function AdminLayout({ children }: PropsWithChildren) {
  const { url } = usePage();

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="flex items-center justify-between border-b border-white/10 px-7 py-3">
        <div className="flex items-center gap-2 font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </div>
      </header>
      <div className="flex flex-1 overflow-hidden">
        <nav className="w-[180px] shrink-0 overflow-y-auto border-r border-white/10 py-2">
          {navItems.map((item) => {
            const active = item.href === "/" ? url === "/" : url.startsWith(item.href);
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`block border-l-2 px-5 py-2.5 text-sm ${
                  active
                    ? "border-[var(--amber)] bg-white/5 text-[var(--text-primary)]"
                    : "border-transparent text-[var(--text-secondary)] hover:bg-white/5 hover:text-[var(--text-primary)]"
                }`}
              >
                {item.label}
              </Link>
            );
          })}
        </nav>
        <main className="min-h-0 flex-1 overflow-y-auto">{children}</main>
      </div>
    </div>
  );
}
