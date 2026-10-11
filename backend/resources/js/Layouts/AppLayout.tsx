import { PropsWithChildren } from "react";
import { UserProfileSection } from "@/Components/UserProfileSection";
import { NotificationBell } from "@/Components/NotificationBell";
import { OfflineBanner } from "@/Components/OfflineBanner";

/**
 * Outermost shell for non-admin/developer roles: brand bar + notification
 * bell + theme toggle. Admin/developer roles use AdminLayout instead (full
 * sidebar); every current page sets AdminLayout as its `.layout`.
 */
export default function AppLayout({ children }: PropsWithChildren) {
  return (
    <div className="flex h-screen flex-col overflow-hidden bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="flex flex-wrap items-center justify-between gap-2.5 border-b border-line px-4 py-2.5 sm:px-6">
        <div className="text-[15px] font-bold">
          Disaster<span className="text-amber">Resource+</span>
        </div>
        <div className="ml-auto flex items-center gap-3">
          <UserProfileSection variant="header" />
          <NotificationBell />
        </div>
      </header>
      <OfflineBanner />
      <main className="min-h-0 flex-1 overflow-y-auto">{children}</main>
    </div>
  );
}
