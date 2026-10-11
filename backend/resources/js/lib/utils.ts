import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { ApiError } from '@/api-service/client';

/** Merges conditional class names, resolving conflicting Tailwind utilities. */
export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}

/** The plain bordered input/select used across the domain pages (Stock, ICS 213-RR, Replenishment). */
export const field =
  'h-9 w-full border border-line-strong bg-[var(--bg-void)] px-2.5 text-sm text-[var(--text-primary)] focus:border-amber focus:outline-none disabled:opacity-50';

/** A visible label wrapping its control: small secondary text, the control just under it. */
export const label = 'flex flex-col gap-1 text-xs font-medium text-[var(--text-secondary)]';

/** The API's own message for a failed request, or a generic retry hint. */
export function errorMessage(err: unknown): string {
  return err instanceof ApiError ? err.message : 'Something went wrong. Try again.';
}
