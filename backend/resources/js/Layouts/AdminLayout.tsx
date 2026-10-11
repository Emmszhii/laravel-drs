import { PropsWithChildren, useEffect, useState } from "react";
import { Link, usePage } from "@inertiajs/react";
import { Boxes, ClipboardList, FileBarChart, Menu, Package, Settings, TrendingUp, Warehouse, X, type LucideIcon } from "lucide-react";
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
 * Below `lg` the sidebar is a drawer opened from the header's menu button
 * (a 208px rail leaves a tablet too little room for the forms and tables);
 * it closes on navigation, on Escape and on a tap outside it.
 */
export default function AdminLayout({ children }: PropsWithChildren) {
  const page = usePage();
  const { url } = page;
  const isSystemAdmin = Boolean((page.props as { auth?: { user?: { is_system_admin?: boolean } | null } }).auth?.user?.is_system_admin);
  const groups = isSystemAdmin ? [...navGroups, settingsGroup] : navGroups;
  const isActive = (href: string) => url.startsWith(href.startsWith("/settings") ? "/settings" : href);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => setMenuOpen(false), [url]);
  useEffect(() => {
    if (!menuOpen) return;
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && setMenuOpen(false);
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [menuOpen]);

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)] supports-[height:100dvh]:h-dvh">
      <header className="flex items-center justify-between gap-2.5 border-b border-line px-4 py-2.5 sm:px-6">
        <div className="flex items-center gap-1">
          <button
            type="button"
            onClick={() => setMenuOpen(true)}
            aria-label="Open menu"
            aria-expanded={menuOpen}
            aria-controls="main-nav"
            className="-ml-2 flex h-9 w-9 items-center justify-center text-[var(--text-secondary)] hover:text-[var(--text-primary)] lg:hidden"
          >
            <Menu size={20} />
          </button>
          <Link href="/replenishment" className="py-2 text-[15px] font-bold">
            Disaster<span className="text-amber">Resource+</span>
          </Link>
        </div>
        <NotificationBell />
      </header>
      <OfflineBanner />
      <div className="flex min-h-0 flex-1">
        {menuOpen && <div className="fixed inset-0 z-[3000] bg-black/40 lg:hidden" onClick={() => setMenuOpen(false)} aria-hidden />}
        <nav
          id="main-nav"
          aria-label="Main"
          className={`fixed inset-y-0 left-0 z-[3001] flex w-64 max-w-[85vw] shrink-0 flex-col border-r border-line bg-[var(--bg-void)] transition-transform lg:static lg:z-auto lg:w-52 lg:translate-x-0 ${
            menuOpen ? "translate-x-0" : "invisible -translate-x-full lg:visible"
          }`}
        >
          <div className="flex items-center justify-between border-b border-line px-4 py-2.5 lg:hidden">
            <span className="text-[15px] font-bold">
              Disaster<span className="text-amber">Resource+</span>
            </span>
            <button
              type="button"
              onClick={() => setMenuOpen(false)}
              aria-label="Close menu"
              className="-mr-2 flex h-9 w-9 items-center justify-center text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
            >
              <X size={20} />
            </button>
          </div>
          <div className="flex-1 overflow-y-auto py-3">
            {groups.map((group) => (
              <div key={group.label} className="mb-3">
                <div className="px-5 pb-1 text-xs text-[var(--text-secondary)]">{group.label}</div>
                {group.items.map((item) => {
                  const active = isActive(item.href);
                  const Icon = item.icon;
                  return (
                    <Link
                      key={item.href}
                      href={item.href}
                      aria-current={active ? "page" : undefined}
                      className={`flex items-center gap-2.5 whitespace-nowrap border-l-2 px-5 py-2.5 text-sm lg:py-2 ${
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
          <UserProfileSection />
        </nav>
        <main className="min-h-0 flex-1 overflow-y-auto">
          <div className="mx-auto max-w-[1440px] pb-10">{children}</div>
        </main>
      </div>
    </div>
  );
}
