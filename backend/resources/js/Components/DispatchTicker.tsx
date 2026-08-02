import { useEffect, useState } from "react";
import {
  activityLogService,
  type ActivityLogEntry,
} from "@/api-service/modules/activityLog";

const POLL_MS = 30_000;

/**
 * Scrolling "dispatch log" marquee — mirrors the design prototype's
 * `.ticker`/`.ticker-track` (docs/disasterresource-design.html), but sourced
 * from real activity (`app_loggers`, see ActivityLogController) instead of
 * canned demo strings.
 */
export function DispatchTicker() {
  const [entries, setEntries] = useState<ActivityLogEntry[]>([]);

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

  if (entries.length === 0) return null;

  const items = (
    <>
      {entries.map((e, i) => (
        <span
          key={`${e.id}-${i}`}
          className="inline-flex shrink-0 items-baseline gap-2 text-sm text-[var(--text-secondary)]"
        >
          <span className="font-mono text-xs text-[var(--text-secondary)] opacity-70">
            {e.ts}
          </span>
          <span className="font-mono text-[11px] text-[var(--amber)]">
            {e.tag}
          </span>
          {e.message}
        </span>
      ))}
    </>
  );

  return (
    <div className="group relative flex shrink-0 overflow-hidden border-b border-white/10 bg-[var(--bg-panel)]">
      <div className="z-10 flex shrink-0 items-center bg-[var(--amber)] px-3.5 text-xs font-bold tracking-wide text-[#1a1200] uppercase">
        Dispatch log
      </div>
      <div className="flex min-w-0 flex-1 overflow-hidden py-2 pl-10">
        <div className="animate-ticker flex shrink-0 gap-12 whitespace-nowrap group-hover:[animation-play-state:paused]">
          {items}
          {items}
        </div>
      </div>
    </div>
  );
}

export default DispatchTicker;
