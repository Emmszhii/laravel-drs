/**
 * @file api-service/roles.ts
 * Single source of truth for DisasterResource+'s roles, mirroring
 * EnsureUserHasRole's `role:admin,developer` gating (see routes/web.php,
 * routes/api.php) and RoleSeeder's role/permission definitions.
 */

export const APP_ROLES = ['developer', 'admin', 'coordinator', 'viewer', 'respondent'] as const;

export type AppRole = (typeof APP_ROLES)[number];

/**
 * True when the role has access to at least one AdminLayout sidebar link
 * beyond Dashboard (see routes/web.php's role: groups) — `viewer` has no
 * other accessible page, so it's the only role excluded.
 */
export function sharesAdminAccess(role: string | null | undefined): boolean {
  return role === 'developer' || role === 'admin' || role === 'coordinator' || role === 'respondent';
}

export function normalizeRole(role: string | null | undefined): AppRole {
  return (APP_ROLES as readonly string[]).includes(role ?? '') ? (role as AppRole) : 'viewer';
}
