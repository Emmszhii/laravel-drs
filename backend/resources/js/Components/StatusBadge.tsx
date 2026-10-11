import type { ReactNode } from "react";

export type Tone = "neutral" | "amber" | "info" | "ok" | "critical";

const TONE: Record<Tone, string> = {
  neutral: "text-[var(--text-secondary)]",
  amber: "text-amber",
  info: "text-info",
  ok: "text-ok",
  critical: "text-critical",
};

/** A status as a colored dot plus its word, so the state never rests on color alone. */
export function StatusBadge({ tone, children }: { tone: Tone; children: ReactNode }) {
  return (
    <span className={`inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium ${TONE[tone]}`}>
      <span aria-hidden className="h-1.5 w-1.5 shrink-0 rounded-full bg-current" />
      {children}
    </span>
  );
}
