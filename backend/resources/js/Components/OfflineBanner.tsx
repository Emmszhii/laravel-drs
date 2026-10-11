import useOfflineSync from "@/Hooks/useOfflineSync";

/**
 * Shown in AppLayout/AdminLayout whenever the browser is offline or the
 * signed-in user's IndexedDB outbox (Offline/) has items waiting to sync —
 * surfaces the offline-first stock records' state to the user.
 */
export function OfflineBanner() {
  const { online, pendingCount, failedCount, needsSignIn, outcome } = useOfflineSync();

  if (online && pendingCount === 0 && failedCount === 0) return null;

  return (
    <div role="status" className="border-b border-line bg-[var(--amber)]/10 px-4 py-1.5 text-xs text-[var(--amber)]">
      {!online && "You're offline — new stock entries will sync automatically. "}
      {pendingCount > 0 &&
        `${pendingCount} item${pendingCount === 1 ? "" : "s"} waiting to sync. `}
      {needsSignIn && (
        <span className="font-semibold">
          Your session has ended: <a href="/login" className="underline">sign in again</a> to sync them.{" "}
        </span>
      )}
      {outcome === "forbidden" && pendingCount > 0 && "Your account can't record stock right now, so they stay on this device. "}
      {failedCount > 0 && (
        <span className="text-[var(--critical)]">
          {failedCount} item{failedCount === 1 ? "" : "s"} failed to sync; see the Stock page.
        </span>
      )}
    </div>
  );
}
