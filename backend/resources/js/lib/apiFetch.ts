/**
 * @file lib/apiFetch.ts
 * Native-fetch counterpart to api-service/client.ts's Axios instance — used by
 * the reusable useModule/FormModal/DataTableCardField system. Same CSRF/
 * credentials handling, normalized into the app's `{success, message, data}`
 * envelope and a typed ApiError on failure.
 */

export class ApiError extends Error {
  readonly status: number;
  readonly errors?: Record<string, string[]>;

  constructor(message: string, status: number, errors?: Record<string, string[]>) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

export interface ApiEnvelope<T> {
  success?: boolean;
  message?: string;
  data: T;
}

function readCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
  return match ? decodeURIComponent(match[1]) : null;
}

function getCsrfToken(): string | null {
  return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? null;
}

export interface ApiFetchOptions extends Omit<RequestInit, 'body'> {
  body?: BodyInit | null;
  onUploadProgress?: (percent: number) => void;
}

/** XHR-based request so file uploads can report real progress (fetch can't). */
function xhrRequest(url: string, options: ApiFetchOptions): Promise<Response> {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open(options.method ?? 'GET', url, true);
    xhr.withCredentials = true;

    const headers = new Headers(options.headers);
    headers.forEach((value, key) => xhr.setRequestHeader(key, value));

    if (options.onUploadProgress) {
      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
          options.onUploadProgress?.(Math.round((e.loaded / e.total) * 100));
        }
      };
    }

    xhr.onload = () => {
      resolve(
        new Response(xhr.response, {
          status: xhr.status,
          statusText: xhr.statusText,
          headers: { 'Content-Type': xhr.getResponseHeader('Content-Type') ?? 'application/json' },
        }),
      );
    };
    xhr.onerror = () => reject(new Error('Network request failed'));
    xhr.send(options.body as XMLHttpRequestBodyInit | null | undefined);
  });
}

/** Raw fetch wrapper — CSRF header, same-origin credentials, no envelope handling. */
export async function apiFetch(url: string, options: ApiFetchOptions = {}): Promise<Response> {
  const headers = new Headers(options.headers);
  headers.set('Accept', 'application/json');
  headers.set('X-Requested-With', 'XMLHttpRequest');

  const isFormData = options.body instanceof FormData;
  if (!isFormData && options.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const xsrf = readCookie('XSRF-TOKEN');
  if (xsrf) {
    headers.set('X-XSRF-TOKEN', xsrf);
  } else {
    const csrf = getCsrfToken();
    if (csrf) {
      headers.set('X-CSRF-TOKEN', csrf);
    }
  }

  const init: ApiFetchOptions = { ...options, headers, credentials: 'include' };

  return options.onUploadProgress ? xhrRequest(url, init) : fetch(url, init as RequestInit);
}

/** Fetch + envelope unwrap + typed ApiError on failure. */
export async function apiFetchJson<T>(url: string, options: ApiFetchOptions = {}): Promise<ApiEnvelope<T>> {
  const response = await apiFetch(url, options);

  if (response.status === 204) {
    return { success: true, data: null as T };
  }

  const body = (await response.json().catch(() => null)) as
    | (ApiEnvelope<T> & { errors?: Record<string, string[]> })
    | null;

  if (!response.ok) {
    throw new ApiError(
      body?.message || `Request failed with status ${response.status}`,
      response.status,
      body?.errors,
    );
  }

  return body ?? { success: true, data: null as T };
}
