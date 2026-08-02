/** Query params accepted by a module's /pagination-search endpoint. */
export interface CrudQueryParams {
  search?: string;
  filters?: Record<string, unknown>;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
  [key: string]: unknown;
}

/** Mirrors Laravel's paginator meta block. */
export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

/** Canonical envelope every BaseController::paginationSearch() returns. */
export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
}

/** Raw shape of a `pagination-search` JSON response body. */
export interface TableApiResponse<T> {
  data: T[];
  meta: PaginationMeta;
  filterable?: string[];
  searchable?: string[];
}
