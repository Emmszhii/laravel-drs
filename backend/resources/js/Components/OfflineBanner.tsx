import useOfflineSync from "@/Hooks/useOfflineSync";

/**
 * Shown in AppLayout/AdminLayout whenever the browser is offline or the
 * IndexedDB outbox (Offline/) has items waiting to sync — surfaces the
 * offline-first incident/request logging feature's state to the user.
 */
export function OfflineBanner() {
  const { online, pendingCount, failedCount } = useOfflineSync();

  if (online && pendingCount === 0 && failedCount === 0) return null;

  return (
    <div className="border-b border-white/10 bg-[var(--amber)]/10 px-4 py-1.5 text-xs text-[var(--amber)]">
      {!online && "You're offline — new incidents/requests will sync automatically. "}
      {pendingCount > 0 &&
        `${pendingCount} item${pendingCount === 1 ? "" : "s"} waiting to sync. `}
      {failedCount > 0 && (
        <span className="text-[var(--critical)]">
          {failedCount} item{failedCount === 1 ? "" : "s"} failed to sync.
        </span>
      )}
    </div>
  );
}
