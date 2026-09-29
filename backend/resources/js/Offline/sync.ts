import { http, ApiError } from "@/api-service";
import { listAll, listPending, markFailed, markPending, remove } from "./outbox";
import type { OutboxItem } from "./db";

type StockSyncResult = { device_id: string; status: "accepted" | "duplicate" | "deferred"; conflicts: string[] };

/**
 * Stock entries go up as one batch: the server orders them (priority first,
 * then creation time, dependencies after what they depend on) and ignores any
 * device-made ID it already holds, so re-sending after a dropped connection
 * never double counts. A `deferred` entry waits for its dependency and stays
 * queued for the next sync.
 */
async function syncStock(items: OutboxItem[]): Promise<void> {
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
    if (err instanceof ApiError && err.status >= 400 && err.status < 500 && err.status !== 419) {
      await Promise.all(items.map((i) => markFailed(i.clientRef, err.message)));
    }
    // Otherwise still offline (or session expired): keep everything queued.
  }
}

/** Uploads the queued stock entries, and discards items older app versions queued for removed features. */
export async function syncOutbox(): Promise<void> {
  for (const item of await listAll()) {
    if ((item.kind as string) !== "stock") {
      await remove(item.clientRef);
    }
  }

  await syncStock(await listPending());
}
