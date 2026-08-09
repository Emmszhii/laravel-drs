import { useCallback, useEffect, useState } from "react";
import useOnlineStatus from "./useOnlineStatus";
import { syncOutbox } from "@/Offline/sync";
import { listAll } from "@/Offline/outbox";
import type { OutboxItem } from "@/Offline/db";

const POLL_MS = 15000;

/**
 * Drives the offline outbox: refreshes its contents, and triggers
 * syncOutbox() on mount, on reconnect, and on a periodic poll while items
 * are pending. See Offline/sync.ts for the actual sync logic.
 */
export default function useOfflineSync() {
  const online = useOnlineStatus();
  const [items, setItems] = useState<OutboxItem[]>([]);

  const refresh = useCallback(async () => {
    setItems(await listAll());
  }, []);

  const sync = useCallback(async () => {
    if (!navigator.onLine) return;
    await syncOutbox();
    await refresh();
  }, [refresh]);

  useEffect(() => {
    refresh();
  }, [refresh]);

  useEffect(() => {
    if (online) sync();
  }, [online, sync]);

  useEffect(() => {
    const id = setInterval(() => {
      if (navigator.onLine) sync();
    }, POLL_MS);

    return () => clearInterval(id);
  }, [sync]);

  const pendingCount = items.filter((i) => i.status !== "failed").length;
  const failedCount = items.filter((i) => i.status === "failed").length;

  return { online, items, pendingCount, failedCount, sync, refresh };
}
