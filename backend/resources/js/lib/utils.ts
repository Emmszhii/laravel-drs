import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { ApiError } from '@/api-service/client';

/** Merges conditional class names, resolving conflicting Tailwind utilities. */
export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}

/** Normalizes an Inertia `href` prop (string, URL, or `{ url, method }`) to a plain path string. */
export function toUrl(href: NonNullable<InertiaLinkProps['href']>): string {
  const value = href as unknown;

  if (typeof value === 'string') {
    return value;
  }

  if (value instanceof URL) {
    return value.toString();
  }

  return String((value as { url?: string })?.url ?? value);
}

/** The plain bordered input/select used across the domain pages (Stock, ICS 213-RR, Replenishment). */
export const field = 'w-full border border-white/20 bg-transparent px-2 py-1.5 text-sm';

/** The API's own message for a failed request, or a generic retry hint. */
export function errorMessage(err: unknown): string {
  return err instanceof ApiError ? err.message : 'Something went wrong. Try again.';
}
