/**
 * The pages the service worker keeps for offline use (vite.config.ts): the
 * HTML cache (full page loads, plus the warm-up fetches below) and the Inertia
 * XHR cache (pages reached through <Link>). Names must match vite.config.ts.
 */
const PATHS = ["/stock", "/ics-requests", "/ics-requests/borrowed"];
const CACHES = ["offline-pages", "offline-pages-xhr"];

/**
 * Fetches the offline pages once per session per user so they open offline
 * even if this user never visited them online. The worker's NetworkFirst rule
 * stores the responses; failures (offline, no worker yet) are ignored.
 */
export async function warmOfflinePages(userId: string): Promise<void> {
  if (!import.meta.env.PROD || !("serviceWorker" in navigator)) return;
  const key = `drs-warmed-${userId}`;
  try {
    if (sessionStorage.getItem(key)) return;
    await navigator.serviceWorker.ready;
    if (!navigator.serviceWorker.controller) return; // first visit: the worker isn't controlling this page yet
    await Promise.all(PATHS.map((p) => fetch(p, { credentials: "same-origin" }).catch(() => undefined)));
    sessionStorage.setItem(key, "1");
  } catch {
    // Private mode or storage blocked: the pages still cache on the next online visit.
  }
}

/** On logout: drop the cached pages so the next person on a shared device can't open them (they hold borrower data). */
export async function clearOfflinePages(): Promise<void> {
  if (!("caches" in window)) return;
  await Promise.all(CACHES.map((c) => caches.delete(c).catch(() => false)));
}
