import { useCallback, useEffect, useState } from "react";
import { usePage } from "@inertiajs/react";
import useOnlineStatus from "./useOnlineStatus";
import { lastSyncOutcome, syncOutbox } from "@/Offline/sync";
import { OUTBOX_EVENT, listAll } from "@/Offline/outbox";
import type { OutboxItem } from "@/Offline/db";

const POLL_MS = 15000;

/**
 * Drives the signed-in user's offline outbox: tracks its contents and the last
 * sync outcome (refreshed on every outbox change), and triggers syncOutbox()
 * on mount, on reconnect, and on a periodic poll. See Offline/sync.ts.
 */
export default function useOfflineSync() {
  const online = useOnlineStatus();
  const userId = usePage().props.auth?.user?.id ?? null;
  const [items, setItems] = useState<OutboxItem[]>([]);
  const [outcome, setOutcome] = useState(lastSyncOutcome());

  const refresh = useCallback(async () => {
    setItems(userId ? await listAll(userId) : []);
    setOutcome(lastSyncOutcome());
  }, [userId]);

  const sync = useCallback(async () => {
    if (!navigator.onLine || !userId) return;
    await syncOutbox(userId);
  }, [userId]);

  useEffect(() => {
    refresh();
    window.addEventListener(OUTBOX_EVENT, refresh);

    return () => window.removeEventListener(OUTBOX_EVENT, refresh);
  }, [refresh]);

  useEffect(() => {
    if (online) sync();
  }, [online, sync]);

  useEffect(() => {
    const id = setInterval(sync, POLL_MS);

    return () => clearInterval(id);
  }, [sync]);

  const pendingCount = items.filter((i) => i.status !== "failed").length;
  const failedCount = items.filter((i) => i.status === "failed").length;
  const needsSignIn = outcome === "sign-in" && pendingCount > 0;

  return { online, userId, items, pendingCount, failedCount, needsSignIn, outcome, sync, refresh };
}
