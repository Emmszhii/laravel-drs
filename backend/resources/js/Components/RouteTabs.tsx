import { Link, usePage } from "@inertiajs/react";

/**
 * Subtabs as real routes (see CLAUDE.md), not local state, so each one is bookmarkable.
 * The first tab is active on its exact URL; the others on their prefix.
 */
export function RouteTabs({ tabs }: { tabs: { href: string; label: string }[] }) {
  const path = usePage().url.split("?")[0];

  return (
    <nav className="flex gap-6 overflow-x-auto border-b border-line px-4 sm:px-6">
      {tabs.map((tab, i) => {
        const active = i === 0 ? path === tab.href : path.startsWith(tab.href);

        return (
          <Link
            key={tab.href}
            href={tab.href}
            aria-current={active ? "page" : undefined}
            className={`whitespace-nowrap border-b-2 py-2.5 text-sm font-medium ${
              active ? "border-amber text-[var(--text-primary)]" : "border-transparent text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
            }`}
          >
            {tab.label}
          </Link>
        );
      })}
    </nav>
  );
}
