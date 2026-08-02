import { Link, usePage } from "@inertiajs/react";

const TABS = [
  { href: "/settings/users", label: "Users" },
  { href: "/settings/routing", label: "Routing" },
  { href: "/settings/disaster-feed", label: "Disaster feed" },
];

/** Shared tab bar for the Settings section — each subtab is a real route (see CLAUDE.md). */
export function SettingsTabs() {
  const { url } = usePage();

  return (
    <div className="flex gap-5 border-b border-white/10 px-6">
      {TABS.map((tab) => {
        const active = url.startsWith(tab.href);

        return active ? (
          <button
            key={tab.href}
            className="border-b-2 border-[var(--amber)] py-2.5 text-sm font-semibold uppercase tracking-wide"
          >
            {tab.label}
          </button>
        ) : (
          <Link
            key={tab.href}
            href={tab.href}
            className="border-b-2 border-transparent py-2.5 text-sm font-semibold uppercase tracking-wide text-[var(--text-secondary)]"
          >
            {tab.label}
          </Link>
        );
      })}
    </div>
  );
}
