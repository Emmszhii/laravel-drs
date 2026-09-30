import { openDB, type DBSchema, type IDBPDatabase } from "idb";

/** Every offline record is a stock entry: receipts, releases, borrowed-item
 * issues and returns (system-spec.md §5.1). */
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
  /**
   * The signed-in user who recorded it; only that user's session uploads it, so
   * a shared device never files one person's entries under another's name.
   * Missing on items queued before v2 (see getDb()).
   */
  userId?: string;
}

interface DrsOfflineDB extends DBSchema {
  outbox: {
    key: string;
    value: OutboxItem;
    indexes: { "by-status": OutboxItemStatus };
  };
}

let dbPromise: Promise<IDBPDatabase<DrsOfflineDB>> | null = null;

/**
 * Lazily opens (and caches) the `drs-offline` IndexedDB database.
 * v2 added OutboxItem.userId. The upgrade leaves v1 items untouched: the owner
 * can't be known here, so the first signed-in user to sync adopts them
 * (outbox.ts ownedBy()). Before v2 every item was uploaded by whoever was
 * signed in anyway, and adopting keeps the entries instead of losing them.
 */
export function getDb(): Promise<IDBPDatabase<DrsOfflineDB>> {
  if (!dbPromise) {
    dbPromise = openDB<DrsOfflineDB>("drs-offline", 2, {
      upgrade(db, oldVersion) {
        if (oldVersion < 1) {
          const store = db.createObjectStore("outbox", { keyPath: "clientRef" });
          store.createIndex("by-status", "status");
        }
      },
    });
  }

  return dbPromise;
}
