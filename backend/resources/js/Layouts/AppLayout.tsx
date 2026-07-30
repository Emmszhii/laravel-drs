import { PropsWithChildren } from "react";

/**
 * Outermost shell: brand bar + notification bell + theme toggle.
 * Mirrors the prototype's `.brandbar` — see disasterresource-design.html
 * for the exact visual reference this should be rebuilt from.
 */
export default function AppLayout({ children }: PropsWithChildren) {
  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="flex items-center justify-between border-b border-white/10 px-7 py-3">
        <div className="flex items-center gap-2 font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </div>
        {/* Role toggle, theme toggle, notifications bell go here */}
      </header>
      <main className="min-h-0 flex-1 overflow-y-auto">{children}</main>
    </div>
  );
}
