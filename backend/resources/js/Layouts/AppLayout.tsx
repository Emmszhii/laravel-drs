import { PropsWithChildren } from "react";
import { UserProfileSection } from "@/Components/UserProfileSection";
import { DispatchTicker } from "@/Components/DispatchTicker";
import { NotificationBell } from "@/Components/NotificationBell";
import { OfflineBanner } from "@/Components/OfflineBanner";

/**
 * Outermost shell for non-admin/developer roles: brand bar + notification
 * bell + theme toggle. Admin/developer roles use AdminLayout instead (full
 * sidebar) — see Dashboard.tsx's `.layout`.
 */
export default function AppLayout({ children }: PropsWithChildren) {
  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="flex flex-wrap items-center justify-between gap-2.5 border-b border-white/10 px-4 py-3 sm:px-7">
        <div className="flex items-center gap-2 font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </div>
        <div className="ml-auto flex items-center gap-3">
          <UserProfileSection variant="header" />
          <NotificationBell />
        </div>
      </header>
      <OfflineBanner />
      <DispatchTicker />
      <main className="min-h-0 flex-1 overflow-y-auto">{children}</main>
    </div>
  );
}
