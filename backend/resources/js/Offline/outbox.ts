import { getDb, type OutboxItem, type OutboxItemKind } from "./db";

/** Queues a write for later sync — used when a create fails while offline. */
export async function enqueue(
  kind: OutboxItemKind,
  payload: Record<string, unknown>,
  disasterId?: string,
): Promise<OutboxItem> {
  const db = await getDb();
  const item: OutboxItem = {
    clientRef: crypto.randomUUID(),
    kind,
    payload,
    disasterId,
    status: "pending",
    createdAt: Date.now(),
  };
  await db.put("outbox", item);

  return item;
}

export async function listPending(): Promise<OutboxItem[]> {
  const db = await getDb();

  return db.getAllFromIndex("outbox", "by-status", "pending");
}

export async function listAll(): Promise<OutboxItem[]> {
  const db = await getDb();

  return db.getAll("outbox");
}

async function setStatus(
  clientRef: string,
  status: OutboxItem["status"],
  error?: string,
): Promise<void> {
  const db = await getDb();
  const item = await db.get("outbox", clientRef);
  if (!item) return;

  item.status = status;
  item.error = error;
  await db.put("outbox", item);
}

export const markSyncing = (clientRef: string) => setStatus(clientRef, "syncing");
export const markPending = (clientRef: string) => setStatus(clientRef, "pending");
export const markFailed = (clientRef: string, error: string) =>
  setStatus(clientRef, "failed", error);

export async function remove(clientRef: string): Promise<void> {
  const db = await getDb();
  await db.delete("outbox", clientRef);
}
