import { openDB, type DBSchema, type IDBPDatabase } from "idb";

export type OutboxItemKind = "incident" | "area" | "request" | "stock";
export type OutboxItemStatus = "pending" | "syncing" | "failed";

export interface OutboxItem {
  /** Also sent to the server as the idempotency `id` — see Api\RequestController::store()
   * and Api\IncidentController::store()/storeArea(), which treat a repeat of the same
   * client-supplied id as a no-op instead of creating a duplicate row. */
  clientRef: string;
  kind: OutboxItemKind;
  payload: Record<string, unknown>;
  /** Only set for `area` kind — the incident (disaster) id it belongs to. */
  disasterId?: string;
  status: OutboxItemStatus;
  error?: string;
  createdAt: number;
}

interface DrsOfflineDB extends DBSchema {
  outbox: {
    key: string;
    value: OutboxItem;
    indexes: { "by-status": OutboxItemStatus };
  };
}

let dbPromise: Promise<IDBPDatabase<DrsOfflineDB>> | null = null;

/** Lazily opens (and caches) the `drs-offline` IndexedDB database. */
export function getDb(): Promise<IDBPDatabase<DrsOfflineDB>> {
  if (!dbPromise) {
    dbPromise = openDB<DrsOfflineDB>("drs-offline", 1, {
      upgrade(db) {
        const store = db.createObjectStore("outbox", { keyPath: "clientRef" });
        store.createIndex("by-status", "status");
      },
    });
  }

  return dbPromise;
}
