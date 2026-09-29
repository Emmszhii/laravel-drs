/**
 * @file api-service/index.ts
 * Public entry point for DisasterResource+'s API service layer — transport,
 * the CRUD factory, role helpers, and every resource service.
 */

export { http, createHttpClient, unwrap, ApiError } from "./client";
export type { ApiEnvelope } from "./client";
export { createModuleResource } from "./http";
export type {
  CrudResource,
  CrudResourceOptions,
  LookupOption,
  WriteOptions,
} from "./http";
export { buildFormData, buildQueryString, hasBinaryFiles } from "./form-data";

export { APP_ROLES, ROLE_LABELS, normalizeRole } from "./modules/roles";
export type { AppRole } from "./modules/roles";

export { depotsService } from "./modules/depots";
export { resourceTypesService } from "./modules/resourceTypes";
export { usersService } from "./modules/users";
