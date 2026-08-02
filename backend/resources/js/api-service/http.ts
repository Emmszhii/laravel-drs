/**
 * @file api-service/http.ts
 * Typed CRUD helper factory bound to a resource `baseUrl`, mirroring
 * BaseController's index / pagination-search / store / update / destroy
 * endpoint shape. Each concrete service (incidents.ts, depots.ts, ...) is a
 * thin, strongly-typed binding on top of this.
 *
 * Update always sends a real POST — BaseController only registers
 * `POST /{id}` for update (files can't ride a multipart PUT/PATCH body).
 */

import type { AxiosResponse } from 'axios';
import type { CrudQueryParams, PaginatedResponse } from '@/Types/pagination';
import { http, unwrap } from './client';
import { buildFormData, buildQueryString, hasBinaryFiles } from './form-data';

export interface WriteOptions {
    onUploadProgress?: (percent: number) => void;
}

export interface CrudResourceOptions {
    /** Resource route prefix, e.g. '/api/depots'. */
    baseUrl: string;
}

function query(params?: Record<string, unknown>): string {
    if (!params) {
        return '';
    }

    const qs = buildQueryString(params);

    return qs ? `?${qs}` : '';
}

function progressConfig(opts?: WriteOptions) {
    if (!opts?.onUploadProgress) {
        return {};
    }

    const cb = opts.onUploadProgress;

    return {
        onUploadProgress: (e: { loaded: number; total?: number }) => {
            if (e.total) {
                cb(Math.round((e.loaded / e.total) * 100));
            }
        },
    };
}

export function createCrudResource<T, TInput = Partial<T>>(options: CrudResourceOptions) {
    const { baseUrl } = options;

    const write = (url: string, method: 'POST', payload: unknown, opts?: WriteOptions): Promise<AxiosResponse> => {
        const useForm = hasBinaryFiles(payload);

        if (!useForm) {
            return http.request({ url, method, data: payload });
        }

        const body = buildFormData(payload as Record<string, unknown>);

        return http.request({ url, method, data: body, ...progressConfig(opts) });
    };

    return {
        baseUrl,

        /** GET / — the plain index payload (non-paginated). */
        getAll: async (params?: CrudQueryParams): Promise<T[]> => unwrap<T[]>(await http.get(`${baseUrl}${query(params)}`)),

        /** GET /pagination-search — paginated + filtered + searched list. */
        getPaginatedFilterSearch: async (params?: CrudQueryParams): Promise<PaginatedResponse<T>> =>
            unwrap<PaginatedResponse<T>>(await http.get(`${baseUrl}/pagination-search${query(params)}`)),

        /** POST / — create a record. */
        create: async (data: TInput, opts?: WriteOptions): Promise<T> => unwrap<T>(await write(baseUrl, 'POST', data, opts)),

        /** POST /{id} (also handles file uploads) — update a record. */
        update: async (id: string | number, data: TInput, opts?: WriteOptions): Promise<T> =>
            unwrap<T>(await write(`${baseUrl}/${id}`, 'POST', data, opts)),

        /** DELETE /{id} — hard delete. */
        delete: async (id: string | number): Promise<T> => unwrap<T>(await http.delete(`${baseUrl}/${id}`)),
    };
}

/** The shape returned by `createCrudResource`, for typing service objects. */
export type CrudResource<T, TInput = Partial<T>> = ReturnType<typeof createCrudResource<T, TInput>>;
export type LookupOption = { value: string | number; label: string };
