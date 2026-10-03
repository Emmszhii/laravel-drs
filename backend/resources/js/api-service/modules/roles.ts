/**
 * @file api-service/roles.ts
 * Single source of truth for DisasterResource+'s roles, mirroring
 * EnsureUserHasRole's `role:...` gating (see routes/web.php, routes/api.php)
 * and RoleSeeder's role/permission definitions.
 *
 * The roles follow the CDRRMO San Pablo City office structure confirmed in the
 * 2026-09-03 requirements interview (system-spec.md §2): Research and Planning,
 * Operations and Warning, Administration and Training and Administrative, with
 * Logistics as a section under Administration and Training, plus `viewer` for
 * unassigned accounts. ERT teams sit inside Operations, so they are not roles,
 * and the Assistant Department Head is a `position`, not a role. The Head of
 * Office is the Admin, a separate flag (`is_system_admin`) that passes every
 * role check.
 */

export const APP_ROLES = [
  'viewer',
  'administrative',
  'logistics',
  'operations',
  'research_planning',
  'admin_training',
] as const;

export type AppRole = (typeof APP_ROLES)[number];

export const ROLE_LABELS: Record<AppRole, string> = {
  viewer: 'Viewer',
  administrative: 'Administrative',
  logistics: 'Logistics',
  operations: 'Operations and Warning',
  research_planning: 'Research and Planning',
  admin_training: 'Administration and Training',
};

export const ADMIN_LABEL = 'Admin (Head of Office)';

/** One line per role for the Settings > Users note, in the order the office chart lists them. */
export const ROLE_DESCRIPTIONS: { label: string; text: string }[] = [
  { label: ADMIN_LABEL, text: 'The Department Head. Full access: enters the budget, runs the solver, approves or rejects plans and requests, manages users and settings.' },
  { label: ROLE_LABELS.logistics, text: 'A section under Administration and Training. Encodes history, stock, counts and the borrowed-item logbook, resolves flags, releases requests and files its own.' },
  { label: ROLE_LABELS.operations, text: 'Files ICS 213-RR requests for its response teams (e.g. Team Delta).' },
  { label: ROLE_LABELS.research_planning, text: 'Reads the recommendations and exports the quarterly report.' },
  { label: ROLE_LABELS.admin_training, text: 'Keeps the item catalog and the depots.' },
  { label: ROLE_LABELS.administrative, text: 'Read-only access to every page.' },
  { label: ROLE_LABELS.viewer, text: 'Unassigned account. Read-only access to every page.' },
];

export function normalizeRole(role: string | null | undefined): AppRole {
  return (APP_ROLES as readonly string[]).includes(role ?? '') ? (role as AppRole) : 'viewer';
}
