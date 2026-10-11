import { useState } from "react";
import { Check, Copy } from "lucide-react";

/**
 * A stock-records fingerprint (64-character SHA-256) shown short: the first 12 characters,
 * enough to compare by eye with a printed report. The full value is in the tooltip and
 * one tap copies it; the PDF report still prints it in full.
 */
export function Fingerprint({ hash }: { hash: string }) {
  const [copied, setCopied] = useState(false);
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(hash);
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch {
      // No clipboard (insecure context): the tooltip still shows the full value.
    }
  };

  return (
    <button
      type="button"
      onClick={copy}
      title={`${hash}\nClick to copy`}
      aria-label={copied ? "Fingerprint copied" : `Copy fingerprint ${hash}`}
      className="inline-flex items-center gap-1 py-1 font-mono text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
    >
      {hash.slice(0, 12)}…
      {copied ? <Check size={12} className="text-ok" /> : <Copy size={12} />}
    </button>
  );
}
