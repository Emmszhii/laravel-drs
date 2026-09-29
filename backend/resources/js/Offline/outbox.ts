import { getDb, type OutboxItem } from "./db";

/**
 * Pending items, plus any left `syncing` when the browser closed mid-upload —
 * those would otherwise never retry. Re-sending is safe: every item carries
 * its clientRef as an idempotency key the server deduplicates on. Items left
 * from older app versions (not stock) are ignored; syncOutbox() discards them.
 */
export async function listPending(): Promise<OutboxItem[]> {
  const db = await getDb();
  const items = await db.getAll("outbox");

  return items.filter((i) => i.kind === "stock" && i.status !== "failed").sort((a, b) => a.createdAt - b.createdAt);
}

/** Queues a stock entry; its clientRef is the device-made ID the server deduplicates on. */
export async function enqueueStock(payload: Record<string, unknown>): Promise<OutboxItem> {
  const db = await getDb();
  const item: OutboxItem = {
    clientRef: crypto.randomUUID(),
    kind: "stock",
    payload: { ...payload, created_at_device: new Date().toISOString(), device: deviceLabel() },
    status: "pending",
    createdAt: Date.now(),
  };
  await db.put("outbox", item);

  return item;
}

/** A stable per-browser label, so conflicts can tell which device made an entry. */
export function deviceLabel(): string {
  try {
    let label = localStorage.getItem("drs-device");
    if (!label) {
      label = `device-${crypto.randomUUID().slice(0, 8)}`;
      localStorage.setItem("drs-device", label);
    }

    return label;
  } catch {
    return "device-unknown";
  }
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

export const markPending = (clientRef: string) => setStatus(clientRef, "pending");
export const markFailed = (clientRef: string, error: string) =>
  setStatus(clientRef, "failed", error);

export async function remove(clientRef: string): Promise<void> {
  const db = await getDb();
  await db.delete("outbox", clientRef);
}
