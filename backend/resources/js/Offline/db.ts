import { openDB, type DBSchema, type IDBPDatabase } from "idb";

/** Only stock entries are queued now. Older app versions also queued
 * incident/area/request items; syncOutbox() discards those, since their
 * endpoints no longer exist. */
export type OutboxItemKind = "stock";
export type OutboxItemStatus = "pending" | "syncing" | "failed";

export interface OutboxItem {
  /** Also sent to the server as the stock entry's device-made ID, which it deduplicates on. */
  clientRef: string;
  kind: OutboxItemKind;
  payload: Record<string, unknown>;
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
