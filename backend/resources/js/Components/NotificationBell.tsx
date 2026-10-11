import { useEffect, useRef, useState } from "react";
import { Bell } from "lucide-react";
import {
  activityLogService,
  type ActivityLogEntry,
} from "@/api-service/modules/activityLog";

const POLL_MS = 30_000;
const LAST_SEEN_KEY = "disasterresource_notif_last_seen_id";

function getLastSeenId(): number {
  return Number(localStorage.getItem(LAST_SEEN_KEY) ?? 0);
}

/**
 * Header notification bell — the `app_loggers` audit feed, shown as a
 * dropdown of "what changed". Unread count is the number of entries newer than the last id the
 * user acknowledged (persisted in localStorage, since these are shared
 * system events, not per-user rows to mark read server-side).
 */
export function NotificationBell() {
  const [entries, setEntries] = useState<ActivityLogEntry[]>([]);
  const [open, setOpen] = useState(false);
  const [lastSeenId, setLastSeenId] = useState(getLastSeenId);
  const wrapRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    let cancelled = false;

    const load = () => {
      activityLogService.list(30).then((data) => {
        if (!cancelled) setEntries(data);
      });
    };

    load();
    const interval = setInterval(load, POLL_MS);
    return () => {
      cancelled = true;
      clearInterval(interval);
    };
  }, []);

  useEffect(() => {
    if (!open) return;
    const onClickOutside = (e: MouseEvent) => {
      if (wrapRef.current && !wrapRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", onClickOutside);
    return () => document.removeEventListener("mousedown", onClickOutside);
  }, [open]);

  const unreadCount = entries.filter((e) => e.id > lastSeenId).length;

  const clearAll = () => {
    const maxId = entries.reduce((max, e) => Math.max(max, e.id), lastSeenId);
    localStorage.setItem(LAST_SEEN_KEY, String(maxId));
    setLastSeenId(maxId);
  };

  return (
    <div className="relative" ref={wrapRef}>
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        className="relative flex h-9 w-9 items-center justify-center border border-line-strong text-xs font-semibold text-[var(--text-secondary)] hover:border-amber hover:text-amber"
        aria-label="Notifications"
      >
        <Bell size={16} />
        {unreadCount > 0 && (
          <span className="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-critical px-1 font-mono text-[10px] text-white">
            {unreadCount > 9 ? "9+" : unreadCount}
          </span>
        )}
      </button>

      {open && (
        <div className="absolute top-[calc(100%+8px)] right-0 z-[4000] max-h-96 w-80 max-w-[calc(100vw-24px)] overflow-y-auto border border-line-strong bg-[var(--bg-panel-raised)] shadow-lg">
          <div className="flex items-center justify-between border-b border-line px-3.5 py-2.5">
            <span className="text-sm font-semibold">
              Notifications
            </span>
            <button
              type="button"
              onClick={clearAll}
              className="text-[11px] text-[var(--text-secondary)] underline hover:text-amber"
            >
              Clear all
            </button>
          </div>
          {entries.length === 0 ? (
            <div className="p-5 text-center text-xs text-[var(--text-secondary)]">
              No notifications yet.
            </div>
          ) : (
            entries.map((e) => (
              <div
                key={e.id}
                className="border-b border-line px-3.5 py-2.5 text-xs leading-relaxed"
              >
                <span className="font-mono text-[10px] text-[var(--text-secondary)]">
                  {e.ts}
                </span>
                <span className="ml-1.5 font-mono text-[10px] text-amber">
                  {e.tag}
                </span>
                <div className="mt-0.5">{e.message}</div>
              </div>
            ))
          )}
        </div>
      )}
    </div>
  );
}

export default NotificationBell;
