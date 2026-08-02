import type { InUseEntry } from '@/Types/reusable/data-table';

export interface ParsedApiError {
  message: string;
  errors?: Record<string, string[]>;
  inUse?: InUseEntry[];
}

/** Reads a failed fetch Response's JSON body into a normalized error shape. */
export async function parseApiError(response: Response): Promise<ParsedApiError> {
  try {
    const body = await response.json();
    const errors = body?.errors as Record<string, unknown> | undefined;
    const inUse = errors?.in_use as InUseEntry[] | undefined;

    return {
      message: body?.message || `Request failed with status ${response.status}`,
      errors: errors && !inUse ? (errors as Record<string, string[]>) : undefined,
      inUse,
    };
  } catch {
    return { message: `Request failed with status ${response.status}` };
  }
}
