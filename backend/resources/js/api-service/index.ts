/**
 * @file api-service/index.ts
 * Public entry point for DisasterResource+'s API service layer — transport,
 * the CRUD factory, role helpers, and every resource service.
 */

export { http, createHttpClient, unwrap, ApiError } from './client';
export type { ApiEnvelope } from './client';
export { createCrudResource } from './http';
export type { CrudResource, CrudResourceOptions, LookupOption, WriteOptions } from './http';
export { buildFormData, buildQueryString, hasBinaryFiles } from './form-data';

export { APP_ROLES, normalizeRole, sharesAdminAccess } from './roles';
export type { AppRole } from './roles';

export { incidentsService } from './incidents';
export { depotsService } from './depots';
export { resourceTypesService } from './resourceTypes';
export { resourcesService } from './resources';
export { usersService } from './users';
export { reportsService } from './reports';
