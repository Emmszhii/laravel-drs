import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { SettingsTabs } from "@/Components/settings/SettingsTabs";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { useToast } from "@/Components/Toast";
import { usersService } from "@/api-service/modules/users";
import type { ColumnDef, CardActions, FieldDef } from "@/Components/table";
import type { AdminUser } from "@/Types/models";

const roleOptions = [
  { value: "developer", label: "Developer" },
  { value: "admin", label: "Admin" },
  { value: "coordinator", label: "Coordinator" },
  { value: "viewer", label: "Viewer" },
  { value: "respondent", label: "Respondent" },
];

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
];

function UserCard(row: AdminUser, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="flex items-center gap-2 font-semibold">
          {row.name}
          <span
            className={`h-1.5 w-1.5 rounded-full ${row.is_active ? "bg-[var(--ok,#3FA98C)]" : "bg-[var(--text-secondary)]"}`}
            title={row.is_active ? "Active" : "Inactive"}
          />
        </div>
        <div className="text-xs text-[var(--text-secondary)]">
          {row.email} · {row.role}
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
  { key: "is_active", label: "Active", type: "checkbox", defaultValue: true },
];

function SettingsUsersIndex() {
  const [modal, setModal] = useState<{
    mode: "create" | "edit";
    row?: AdminUser;
  } | null>(null);
  const { showToast } = useToast();

  return (
    <div>
      <SettingsTabs />
      <div className="flex items-center justify-between px-6 py-3">
        <h2 className="text-lg font-semibold uppercase">Users</h2>
        <Button onClick={() => setModal({ mode: "create" })}>+ Add user</Button>
      </div>

      <div className="px-6 pb-6">
        <DataTableCardField<AdminUser>
          apiUrl="/api/settings/users"
          apiQueryKey="users"
          columns={columns}
          renderCard={UserCard}
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
