import type { ReactNode } from "react";

/**
 * Shared form-field atoms: the single source of truth for label / input / error
 * styling used by the FormModal record forms (Items, Depots, Users).
 */
export const labelCls =
  "mb-1.5 block text-[11px] font-medium text-[var(--text-secondary)]";

/** Single-line input / select styling — matches the design system's `.field input/select` (sharp corners, void bg, amber focus). */
export const inputCls =
  "w-full border border-line-strong bg-[var(--bg-void)] px-2.5 h-9 text-sm text-[var(--text-primary)] placeholder:text-[var(--text-secondary)] transition-colors focus:border-amber focus:outline-none disabled:cursor-not-allowed disabled:opacity-50";

/** Multi-line textarea styling (same look, no fixed height, no resize handle). */
export const textareaCls =
  "w-full border border-line-strong bg-[var(--bg-void)] px-2.5 py-2 text-sm text-[var(--text-primary)] placeholder:text-[var(--text-secondary)] transition-colors focus:border-amber focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 resize-none";

export function Field({
  label,
  error,
  required,
  helpText,
  children,
}: {
  label: string;
  error?: string;
  required?: boolean;
  helpText?: string;
  children: ReactNode;
}) {
  return (
    <div data-cy="field-div-1" className="relative">
      <label className={labelCls} data-cy="field-label-2">
        {label}
        {required && (
          <span className="ml-0.5 text-critical" data-cy="field-span-3">
            *
          </span>
        )}
      </label>
      {children}
      {helpText && !error && (
        <p
          className="mt-1 text-xs text-[var(--text-secondary)]"
          data-cy="field-p-help"
        >
          {helpText}
        </p>
      )}
      {error && (
        <p className="mt-1 text-xs text-critical" data-cy="field-p-5">
          {error}
        </p>
      )}
    </div>
  );
}
