import { getDb, type OutboxItem } from "./db";

/** Fired on window whenever the outbox or the last sync outcome changes (useOfflineSync listens). */
export const OUTBOX_EVENT = "outbox-changed";
const changed = () => window.dispatchEvent(new Event(OUTBOX_EVENT));

type SharedUser = { id: string; role: string; is_system_admin: boolean } | null;

/** The signed-in user for code outside React (app boot, background sync); React code reads usePage(). */
let currentUser: SharedUser = readInitialUser();

function readInitialUser(): SharedUser {
  try {
    const page = JSON.parse(document.getElementById("app")?.dataset.page ?? "null");
    return page?.props?.auth?.user ?? null;
  } catch {
    return null;
  }
}

/** Kept current by app.tsx from each Inertia navigation (login and logout don't reload the page). */
export function setCurrentUser(user: SharedUser): void {
  currentUser = user;
}

export const currentUserId = (): string | null => currentUser?.id ?? null;

/** Mirrors Web\StockController's `can.record` (the Admin or Logistics). */
export const currentUserCanRecord = (): boolean => !!currentUser && (currentUser.is_system_admin || currentUser.role === "logistics");

/** Items queued before outbox v2 have no owner; the first signed-in user adopts them (see db.ts). */
const ownedBy = (i: OutboxItem, userId: string) => (i.userId ?? userId) === userId;

/** This user's queued stock entries, oldest first. */
export async function listAll(userId: string): Promise<OutboxItem[]> {
  const db = await getDb();

  return (await db.getAll("outbox")).filter((i) => i.kind === "stock" && ownedBy(i, userId)).sort((a, b) => a.createdAt - b.createdAt);
}

/**
 * This user's pending items, plus any left `syncing` when the browser closed
 * mid-upload — those would otherwise never retry. Re-sending is safe: every
 * item carries its clientRef as an idempotency key the server deduplicates on.
 * Adopted ownerless items are stamped with the user here.
 */
export async function listPending(userId: string): Promise<OutboxItem[]> {
  const db = await getDb();
  const items = (await listAll(userId)).filter((i) => i.status !== "failed");
  for (const i of items.filter((i) => !i.userId)) {
    i.userId = userId;
    await db.put("outbox", i);
  }

  return items;
}

/** Queues a stock entry for this user; its clientRef is the device-made ID the server deduplicates on. */
export async function enqueueStock(payload: Record<string, unknown>, userId: string): Promise<OutboxItem> {
  const db = await getDb();
  const item: OutboxItem = {
    clientRef: crypto.randomUUID(),
    kind: "stock",
    payload: { ...payload, created_at_device: new Date().toISOString(), device: deviceLabel() },
    status: "pending",
    createdAt: Date.now(),
    userId,
  };
  await db.put("outbox", item);
  changed();

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

async function setStatus(clientRef: string, status: OutboxItem["status"], error?: string): Promise<void> {
  const db = await getDb();
  const item = await db.get("outbox", clientRef);
  if (!item) return;

  item.status = status;
  item.error = error;
  await db.put("outbox", item);
  changed();
}

/** Also the Stock page's Retry for a failed item. */
export const markPending = (clientRef: string) => setStatus(clientRef, "pending");
export const markFailed = (clientRef: string, error: string) => setStatus(clientRef, "failed", error);

/** Drops an item (synced, or discarded by the user). */
export async function remove(clientRef: string): Promise<void> {
  const db = await getDb();
  await db.delete("outbox", clientRef);
  changed();
}
