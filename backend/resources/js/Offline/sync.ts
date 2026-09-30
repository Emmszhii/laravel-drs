import { ApiError, http } from "@/api-service";
import { listPending, markFailed, markPending, remove } from "./outbox";

type StockSyncResult = { device_id: string; status: "accepted" | "duplicate" | "deferred"; conflicts: string[] };

let inFlight: Promise<void> | null = null;

/**
 * Uploads the queued entries as one batch: the server orders them (priority
 * first, then creation time, dependencies after what they depend on) and
 * ignores any device-made ID it already holds, so re-sending after a dropped
 * connection never double counts. A `deferred` entry waits for its dependency
 * and stays queued. Runs on app open, on reconnect and after each new entry;
 * while offline the POST fails and the service worker's Background Sync
 * queue replays it (Chromium), see vite.config.ts. Concurrent callers share
 * one upload.
 */
export function syncOutbox(): Promise<void> {
  inFlight ??= upload().finally(() => {
    inFlight = null;
  });

  return inFlight;
}

async function upload(): Promise<void> {
  const items = await listPending();
  if (items.length === 0) {
    return;
  }

  try {
    const res = await http.post("/api/stock/sync", {
      entries: items.map((i) => ({ ...i.payload, device_id: i.clientRef })),
    });
    const results: StockSyncResult[] = res.data.results;
    for (const r of results) {
      if (r.status === "deferred") {
        await markPending(r.device_id);
      } else {
        await remove(r.device_id);
      }
    }
  } catch (err) {
    // A 4xx (validation failure, permission change since queuing) is terminal:
    // mark the entries failed and show them rather than retrying forever.
    if (err instanceof ApiError && err.status >= 400 && err.status < 500 && err.status !== 401 && err.status !== 419) {
      await Promise.all(items.map((i) => markFailed(i.clientRef, err.message)));
    }
    // Otherwise still offline, logged out or the session expired: keep everything queued.
  }
}

/** True when a request failed for lack of a connection, not because the server refused it. */
export const isNetworkError = (err: unknown) => err instanceof ApiError && err.status === 0;
