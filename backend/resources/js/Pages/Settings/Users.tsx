import { Fragment, useState } from "react";
import { useQueryClient } from "@tanstack/react-query";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { SettingsTabs } from "@/Components/settings/SettingsTabs";
import { PageHeader } from "@/Components/PageHeader";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { useToast } from "@/Components/Toast";
import { ApiError } from "@/api-service/client";
import { usersService } from "@/api-service/modules/users";
import { ADMIN_LABEL, ROLE_DESCRIPTIONS, ROLE_LABELS, APP_ROLES } from "@/api-service/modules/roles";
import type { ColumnDef, CardActions, FieldDef } from "@/Components/table";
import type { AdminUser } from "@/Types/models";

const roleOptions = APP_ROLES.map((value) => ({ value, label: ROLE_LABELS[value] }));

const columns: ColumnDef<AdminUser>[] = [
  { key: "name", label: "Name" },
  { key: "email", label: "Email" },
  {
    key: "role",
    label: "Role",
    filterable: true,
    type: "select",
    typeData: roleOptions,
  },
  {
    key: "is_system_admin",
    label: ADMIN_LABEL,
    filterable: true,
    type: "select",
    typeData: [
      { value: "1", label: "Yes" },
      { value: "0", label: "No" },
    ],
  },
];

/**
 * Factory (rather than a module-level component) so the row buttons can
 * close over the page's showToast/invalidate handlers without calling
 * hooks from inside a function that's invoked once per row — DataTableCardField
 * calls `renderCard(row, actions)` directly, not as a mounted React component.
 */
function makeUserCard(handlers: {
  onSuspend: (row: AdminUser) => void;
  onActivate: (row: AdminUser) => void;
  onResetPassword: (row: AdminUser) => void;
}) {
  return function UserCard(row: AdminUser, actions: CardActions) {
    return (
      <div className="flex items-center justify-between border-b border-line p-3 text-sm">
        <div>
          <div className="flex items-center gap-2 font-semibold">
            {row.name}
            <span
              className={`h-1.5 w-1.5 rounded-full ${row.is_active ? "bg-[var(--ok,#3FA98C)]" : "bg-[var(--text-secondary)]"}`}
              title={row.is_active ? "Active" : "Inactive"}
            />
          </div>
          <div className="text-xs text-[var(--text-secondary)]">
            {row.email} · {ROLE_LABELS[row.role] ?? row.role}
            {row.position ? ` · ${row.position}` : ""}
            {row.is_system_admin ? ` · ${ADMIN_LABEL}` : ""}
          </div>
        </div>
        <div className="flex gap-3 text-xs">
          {actions.canEdit && (
            <button
              onClick={actions.onEdit}
              className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]"
            >
              Edit
            </button>
          )}
          <button
            onClick={() => handlers.onResetPassword(row)}
            className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]"
          >
            Reset password
          </button>
          {row.is_active ? (
            <button
              onClick={() => handlers.onSuspend(row)}
              className="underline text-[var(--text-secondary)] hover:text-[var(--pending)]"
            >
              Suspend
            </button>
          ) : (
            <button
              onClick={() => handlers.onActivate(row)}
              className="underline text-[var(--text-secondary)] hover:text-[var(--ok)]"
            >
              Activate
            </button>
          )}
          {actions.canDelete && (
            <button
              onClick={actions.onDelete}
              className="underline text-[var(--text-secondary)] hover:text-[var(--critical)]"
            >
              Remove
            </button>
          )}
        </div>
      </div>
    );
  };
}

const userFields: FieldDef<AdminUser>[] = [
  { key: "first_name", label: "First name", required: true },
  { key: "last_name", label: "Last name", required: true },
  { key: "email", label: "Email", type: "email", required: true },
  {
    key: "role",
    label: "Role",
    type: "select",
    required: true,
    options: roleOptions,
  },
  {
    key: "position",
    label: "Position / title",
    colSpan: 2,
    placeholder: "e.g. Assistant Department Head, Logistics Officer",
    helpText: "Shown beside this person's name on requests they file, approve or release.",
    transform: (value: unknown) => (value === "" || value === undefined ? null : value),
  },
  { key: "is_active", label: "Active", type: "checkbox", defaultValue: true },
  {
    key: "is_system_admin",
    label: "Head of Office (Admin: full access, final approval)",
    type: "checkbox",
    defaultValue: false,
  },
];

/** Read-only reminder of what each role can do; the roles come from system-spec.md §2. */
function RolesNote() {
  return (
    <section className="space-y-2 border border-line p-4 text-xs text-[var(--text-secondary)]">
      <p>
        Roles follow the office structure confirmed in the 2026-09-03 requirements interview. The Admin is the Head of
        Office (the Department Head); the Assistant Department Head is a position title, not a role.
      </p>
      <dl className="grid gap-x-4 gap-y-1 sm:grid-cols-[220px_1fr]">
        {ROLE_DESCRIPTIONS.map(({ label, text }) => (
          <Fragment key={label}>
            <dt className="font-semibold text-[var(--text-primary)]">{label}</dt>
            <dd>{text}</dd>
          </Fragment>
        ))}
      </dl>
    </section>
  );
}

function SettingsUsersIndex() {
  const [modal, setModal] = useState<{
    mode: "create" | "edit";
    row?: AdminUser;
  } | null>(null);
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  const invalidateUsers = () =>
    tableListInvalidateKeys("users").forEach((queryKey) =>
      queryClient.invalidateQueries({ queryKey }),
    );

  const withErrorToast = async (action: () => Promise<unknown>, fallback: string) => {
    try {
      await action();
      invalidateUsers();
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : fallback, "error");
    }
  };

  const renderCard = makeUserCard({
    onSuspend: (row) =>
      withErrorToast(async () => {
        await usersService.suspend(row.id);
        showToast("User suspended.");
      }, "Failed to suspend user."),
    onActivate: (row) =>
      withErrorToast(async () => {
        await usersService.activate(row.id);
        showToast("User activated.");
      }, "Failed to activate user."),
    onResetPassword: (row) =>
      withErrorToast(async () => {
        await usersService.resetPassword(row.id);
        showToast("Password reset email sent.");
      }, "Failed to send password reset email."),
  });

  return (
    <div>
      <PageHeader title="Settings" actions={<Button onClick={() => setModal({ mode: "create" })}>+ Add user</Button>} />
      <SettingsTabs />

      <div className="px-4 py-4 sm:px-6">
        <RolesNote />
      </div>

      <div className="px-4 pb-6 sm:px-6">
        <DataTableCardField<AdminUser>
          apiUrl="/api/settings/users"
          apiQueryKey="users"
          columns={columns}
          renderCard={renderCard}
          onEditRow={(row) => setModal({ mode: "edit", row })}
        />
      </div>

      {modal && (
        <FormModal<AdminUser>
          open
          onClose={() => setModal(null)}
          title={modal.mode === "create" ? "Add user" : "Edit user"}
          fields={userFields}
          mode={modal.mode}
          row={modal.row}
          invalidateKeys={tableListInvalidateKeys("users")}
          mutationFn={async (payload) =>
            modal.mode === "create"
              ? usersService.create(payload as Partial<AdminUser>)
              : usersService.update(
                  modal.row!.id,
                  payload as Partial<AdminUser>,
                )
          }
          onSuccess={() => {
            if (modal.mode === "create") {
              showToast("User created — an email has been sent to set their password.");
            }
            setModal(null);
          }}
        />
      )}
    </div>
  );
}

SettingsUsersIndex.layout = (page: JSX.Element) => (
  <AdminLayout>{page}</AdminLayout>
);

export default SettingsUsersIndex;
