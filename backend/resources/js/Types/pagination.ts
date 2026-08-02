/**
 * @file Types/pagination.ts
 * Re-exports the canonical pagination contracts from Types/reusable/pagination
 * so api-service/http.ts's plain-axios `createCrudResource` and the
 * useModule/DataTableCardField system agree on one envelope shape.
 */
export type { CrudQueryParams, PaginatedResponse, PaginationMeta, TableApiResponse } from './reusable/pagination';
