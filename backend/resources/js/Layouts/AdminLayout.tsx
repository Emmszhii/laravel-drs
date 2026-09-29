import { PropsWithChildren } from "react";
import { Link, usePage } from "@inertiajs/react";
import { UserProfileSection } from "@/Components/UserProfileSection";
import { NotificationBell } from "@/Components/NotificationBell";
import { OfflineBanner } from "@/Components/OfflineBanner";

// Every role can open these pages (routes/web.php); what each role can do on
// them comes from the page's own `can` props. Settings is Admin only.
const navItems = [
  { href: "/replenishment", label: "Replenishment" },
  { href: "/stock", label: "Stock" },
  { href: "/ics-requests", label: "ICS 213-RR" },
  { href: "/resources/types", label: "Items" },
  { href: "/depots", label: "Depots" },
];

/**
 * Sidebar shell used by every page.
 *
 * Below `md`, the vertical sidebar collapses into a horizontally scrollable
 * top tab bar (active indicator moves from a left border to a bottom
 * border), matching the design prototype's `@media (max-width:720px)` `.gnav`
 * treatment.
 */
export default function AdminLayout({ children }: PropsWithChildren) {
  const page = usePage();
  const { url } = page;
  const isSystemAdmin = Boolean((page.props as { auth?: { user?: { is_system_admin?: boolean } | null } }).auth?.user?.is_system_admin);
  const visibleNavItems = isSystemAdmin ? [...navItems, { href: "/settings/users", label: "Settings" }] : navItems;

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="flex flex-wrap items-center justify-between gap-2.5 border-b border-white/10 px-4 py-3 sm:px-7">
        <div className="flex items-center gap-2 font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </div>
        <div className="ml-auto flex items-center gap-3 md:hidden">
          <UserProfileSection variant="header" />
          <NotificationBell />
        </div>
        <div className="ml-auto hidden items-center gap-3 md:flex">
          <NotificationBell />
        </div>
      </header>
      <OfflineBanner />
      <div className="flex flex-1 flex-col overflow-hidden md:flex-row">
        <nav className="flex shrink-0 flex-row overflow-x-auto border-b border-white/10 md:w-[180px] md:flex-col md:overflow-visible md:border-r md:border-b-0">
          <div className="flex flex-1 flex-row overflow-x-auto md:flex-col md:overflow-y-auto md:py-2">
            {visibleNavItems.map((item) => {
              const active = url.startsWith(item.href.startsWith("/settings") ? "/settings" : item.href);
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`block border-b-2 px-4 py-2.5 text-sm whitespace-nowrap md:border-b-0 md:border-l-2 md:px-5 ${
                    active
                      ? "border-[var(--amber)] bg-white/5 text-[var(--text-primary)]"
                      : "border-transparent text-[var(--text-secondary)] hover:bg-white/5 hover:text-[var(--text-primary)]"
                  }`}
                >
                  {item.label}
                </Link>
              );
            })}
          </div>
          <div className="hidden md:block">
            <UserProfileSection />
          </div>
        </nav>
        <main className="min-h-0 flex-1 overflow-y-auto">{children}</main>
      </div>
    </div>
  );
}
