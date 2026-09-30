import { ApiError, http } from "@/api-service";
import { OUTBOX_EVENT, currentUserId, listPending, markFailed, markPending, remove } from "./outbox";

type StockSyncResult = {
  device_id: string;
  status: "accepted" | "duplicate" | "deferred" | "rejected";
  seq: number | null;
  conflicts: string[];
  errors?: string[];
};

/** How the last upload went: `sign-in` = session expired, `forbidden` = this user may not record stock. */
export type SyncOutcome = "ok" | "offline" | "sign-in" | "forbidden" | "error";

let inFlight: Promise<SyncOutcome> | null = null;
let last: SyncOutcome = "ok";

export const lastSyncOutcome = () => last;

/**
 * Uploads this user's queued entries as one batch: the server orders them
 * (priority first, then creation time, dependencies after what they depend
 * on) and ignores any device-made ID it already holds, so re-sending after a
 * dropped connection never double counts. Each entry is judged on its own:
 * `deferred` waits for its dependency and stays queued, `rejected` is marked
 * failed with the server's reasons. Runs on app open, on reconnect and after
 * each new entry; while offline the POST fails and the service worker's
 * Background Sync queue replays it (Chromium), see vite.config.ts. With no
 * signed-in user nothing is sent. Concurrent callers share one upload.
 */
export function syncOutbox(userId: string | null = currentUserId()): Promise<SyncOutcome> {
  if (!userId) {
    return Promise.resolve(last);
  }
  inFlight ??= upload(userId)
    .then((outcome) => {
      last = outcome;
      window.dispatchEvent(new Event(OUTBOX_EVENT));
      return outcome;
    })
    .finally(() => {
      inFlight = null;
    });

  return inFlight;
}

async function upload(userId: string): Promise<SyncOutcome> {
  const items = await listPending(userId);
  if (items.length === 0) {
    return "ok";
  }

  try {
    const res = await http.post("/api/stock/sync", {
      entries: items.map((i) => ({ ...i.payload, device_id: i.clientRef })),
    });
    const results: StockSyncResult[] = res.data.results ?? [];
    for (const r of results) {
      if (r.status === "deferred") {
        await markPending(r.device_id);
      } else if (r.status === "rejected") {
        await markFailed(r.device_id, r.errors?.join("; ") || "Rejected by the server.");
      } else {
        await remove(r.device_id);
      }
    }

    return "ok";
  } catch (err) {
    if (!(err instanceof ApiError) || err.status === 0) return "offline";
    // Session expired: keep everything queued until this user signs in again.
    if (err.status === 401 || err.status === 419) return "sign-in";
    // Not allowed to record stock (role changed since queuing): keep them; an Admin can sort it out.
    if (err.status === 403) return "forbidden";
    // Any other 4xx means the request body itself was malformed: retrying can't help.
    if (err.status < 500) {
      await Promise.all(items.map((i) => markFailed(i.clientRef, err.message)));
    }

    return "error";
  }
}

/** True when a request failed for lack of a connection, not because the server refused it. */
export const isNetworkError = (err: unknown) => err instanceof ApiError && err.status === 0;
