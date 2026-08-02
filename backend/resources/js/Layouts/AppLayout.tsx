import { PropsWithChildren } from "react";
import { UserProfileSection } from "@/Components/UserProfileSection";

/**
 * Outermost shell for non-admin/developer roles: brand bar + notification
 * bell + theme toggle. Admin/developer roles use AdminLayout instead (full
 * sidebar) — see Dashboard.tsx's `.layout`.
 */
export default function AppLayout({ children }: PropsWithChildren) {
  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="flex items-center justify-between border-b border-white/10 px-7 py-3">
        <div className="flex items-center gap-2 font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </div>
        <div className="w-64">
          <UserProfileSection variant="header" />
        </div>
      </header>
      <main className="min-h-0 flex-1 overflow-y-auto">{children}</main>
    </div>
  );
}
