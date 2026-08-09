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

/**
 * Processes every queued outbox item in order. A 4xx response (validation
 * failure, permission change since queuing) is terminal — the item is
 * marked `failed` and surfaced to the user rather than retried forever. A
 * network failure (still offline) leaves the item `pending` and stops the
 * loop, since later items would fail the same way.
 */
export async function syncOutbox(): Promise<void> {
  const pending = await listPending();

  for (const item of pending) {
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
