import { PropsWithChildren } from "react";
import { Link, usePage } from "@inertiajs/react";
import { Boxes, ClipboardList, FileBarChart, Package, Settings, TrendingUp, Warehouse, type LucideIcon } from "lucide-react";
import { UserProfileSection } from "@/Components/UserProfileSection";
import { NotificationBell } from "@/Components/NotificationBell";
import { OfflineBanner } from "@/Components/OfflineBanner";

type NavItem = { href: string; label: string; icon: LucideIcon };

// Every role can open these pages (routes/web.php); what each role can do on
// them comes from the page's own `can` props. Settings is Admin only.
const navGroups: { label: string; items: NavItem[] }[] = [
  {
    label: "Planning",
    items: [
      { href: "/replenishment", label: "Replenishment", icon: TrendingUp },
      { href: "/report", label: "Report", icon: FileBarChart },
    ],
  },
  {
    label: "Records",
    items: [
      { href: "/stock", label: "Stock", icon: Package },
      { href: "/ics-requests", label: "ICS 213-RR", icon: ClipboardList },
    ],
  },
  {
    label: "Catalog",
    items: [
      { href: "/resources/types", label: "Items", icon: Boxes },
      { href: "/depots", label: "Depots", icon: Warehouse },
    ],
  },
];

const settingsGroup = { label: "Admin", items: [{ href: "/settings/users", label: "Settings", icon: Settings }] };

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
  const groups = isSystemAdmin ? [...navGroups, settingsGroup] : navGroups;
  const isActive = (href: string) => url.startsWith(href.startsWith("/settings") ? "/settings" : href);

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)] supports-[height:100dvh]:h-dvh">
      <header className="flex items-center justify-between gap-2.5 border-b border-line px-4 py-2.5 sm:px-6">
        <Link href="/replenishment" className="text-[15px] font-bold">
          Disaster<span className="text-amber">Resource+</span>
        </Link>
        <div className="flex items-center gap-2">
          <NotificationBell />
          <div className="md:hidden">
            <UserProfileSection variant="header" />
          </div>
        </div>
      </header>
      <OfflineBanner />
      <div className="flex min-h-0 flex-1 flex-col md:flex-row">
        <nav aria-label="Main" className="flex shrink-0 flex-col border-b border-line md:w-52 md:border-r md:border-b-0">
          <div className="flex flex-1 flex-row overflow-x-auto md:flex-col md:overflow-y-auto md:py-3">
            {groups.map((group) => (
              <div key={group.label} className="flex flex-row md:mb-3 md:flex-col">
                <div className="hidden px-5 pb-1 text-xs text-[var(--text-secondary)] md:block">{group.label}</div>
                {group.items.map((item) => {
                  const active = isActive(item.href);
                  const Icon = item.icon;
                  return (
                    <Link
                      key={item.href}
                      href={item.href}
                      aria-current={active ? "page" : undefined}
                      className={`flex items-center gap-2.5 whitespace-nowrap border-b-2 px-4 py-2.5 text-sm md:border-b-0 md:border-l-2 md:px-5 md:py-2 ${
                        active
                          ? "border-amber bg-tint font-medium text-[var(--text-primary)]"
                          : "border-transparent text-[var(--text-secondary)] hover:bg-tint hover:text-[var(--text-primary)]"
                      }`}
                    >
                      <Icon size={16} aria-hidden className={active ? "text-amber" : ""} />
                      {item.label}
                    </Link>
                  );
                })}
              </div>
            ))}
          </div>
          <div className="hidden md:block">
            <UserProfileSection />
          </div>
        </nav>
        <main className="min-h-0 flex-1 overflow-y-auto">
          <div className="mx-auto max-w-[1440px] pb-10">{children}</div>
        </main>
      </div>
    </div>
  );
}
