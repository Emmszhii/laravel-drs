import { http, ApiError } from "@/api-service";
import { listPending, markSyncing, markFailed, markPending, remove } from "./outbox";
import type { OutboxItem } from "./db";

async function postForItem(item: OutboxItem): Promise<void> {
  const payload = { ...item.payload, id: item.clientRef };

  switch (item.kind) {
    case "incident":
      await http.post("/api/incidents", payload);
      return;
    case "area":
      await http.post(`/api/incidents/${item.disasterId}/areas`, payload);
      return;
    case "request":
      await http.post("/api/requests", payload);
      return;
  }
}

type StockSyncResult = { device_id: string; status: "accepted" | "duplicate" | "deferred"; conflicts: string[] };

/**
 * Stock entries go up as one batch: the server orders them (priority first,
 * then creation time, dependencies after what they depend on) and ignores any
 * device-made ID it already holds, so re-sending after a dropped connection
 * never double counts. A `deferred` entry waits for its dependency and stays
 * queued for the next sync.
 */
async function syncStock(items: OutboxItem[]): Promise<boolean> {
  if (items.length === 0) {
    return true;
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

    return true;
  } catch (err) {
    if (err instanceof ApiError && err.status >= 400 && err.status < 500 && err.status !== 419) {
      await Promise.all(items.map((i) => markFailed(i.clientRef, err.message)));

      return true;
    }

    return false; // still offline (or session expired): keep everything queued
  }
}

/**
 * Processes every queued outbox item in order. A 4xx response (validation
 * failure, permission change since queuing) is terminal — the item is
 * marked `failed` and surfaced to the user rather than retried forever. A
 * network failure (still offline) leaves the item `pending` and stops the
 * loop, since later items would fail the same way.
 */
export async function syncOutbox(): Promise<void> {
  const pending = await listPending();

  if (!(await syncStock(pending.filter((i) => i.kind === "stock")))) {
    return;
  }

  for (const item of pending.filter((i) => i.kind !== "stock")) {
    await markSyncing(item.clientRef);

    try {
      await postForItem(item);
      await remove(item.clientRef);
    } catch (err) {
      if (err instanceof ApiError && err.status >= 400 && err.status < 500) {
        await markFailed(item.clientRef, err.message);
        continue;
      }

      // Network failure (still offline) — revert to pending so a later
      // syncOutbox() call retries it, and stop: later items would fail the
      // same way.
      await markPending(item.clientRef);
      break;
    }
  }
}
