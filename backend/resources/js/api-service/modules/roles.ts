/**
 * @file api-service/roles.ts
 * Single source of truth for DisasterResource+'s roles, mirroring
 * EnsureUserHasRole's `role:...` gating (see routes/web.php, routes/api.php)
 * and RoleSeeder's role/permission definitions.
 *
 * These are the 6 CDRRMO San Pablo City functional divisions confirmed by the
 * 2026-09-03 stakeholder interview (Incident Management, ERT, Logistics,
 * Operations, Research and Planning, Admin & Training — see CLAUDE.md),
 * plus `viewer` for unassigned/minimal accounts. System-configuration access
 * (Settings, user management, routing, etc.) is a separate flag
 * (`is_system_admin`), not one of these roles — it's independent of which
 * division a user belongs to.
 */

export const APP_ROLES = [
  'viewer',
  'incident_management',
  'ert',
  'logistics',
  'operations',
  'research_planning',
  'admin_training',
] as const;

export type AppRole = (typeof APP_ROLES)[number];

export const ROLE_LABELS: Record<AppRole, string> = {
  viewer: 'Viewer',
  incident_management: 'Incident Management',
  ert: 'ERT',
  logistics: 'Logistics',
  operations: 'Operations',
  research_planning: 'Research and Planning',
  admin_training: 'Admin & Training',
};

/**
 * True when the user has access to at least one AdminLayout sidebar link
 * beyond Dashboard (see routes/web.php's role: groups) — `viewer` (with no
 * system-admin flag) has no other accessible page, so it's the only case
 * excluded.
 */
export function sharesAdminAccess(role: string | null | undefined, isSystemAdmin = false): boolean {
  return isSystemAdmin || (role !== 'viewer' && (APP_ROLES as readonly string[]).includes(role ?? ''));
}

export function normalizeRole(role: string | null | undefined): AppRole {
  return (APP_ROLES as readonly string[]).includes(role ?? '') ? (role as AppRole) : 'viewer';
}
