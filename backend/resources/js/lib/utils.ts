import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { ApiError } from '@/api-service/client';

/** Merges conditional class names, resolving conflicting Tailwind utilities. */
export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}

/** The plain bordered input/select used across the domain pages (Stock, ICS 213-RR, Replenishment). */
export const field = 'w-full border border-white/20 bg-transparent px-2 py-1.5 text-sm';

/** The API's own message for a failed request, or a generic retry hint. */
export function errorMessage(err: unknown): string {
  return err instanceof ApiError ? err.message : 'Something went wrong. Try again.';
}
