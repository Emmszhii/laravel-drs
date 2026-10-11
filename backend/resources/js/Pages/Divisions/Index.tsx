import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import { PageHeader } from "@/Components/PageHeader";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { divisionsService, searchDivisions } from "@/api-service/modules/divisions";
import { usePermission } from "@/Hooks/use-permissions";
import type { CardActions, ColumnDef, FieldDef } from "@/Components/table";
import type { Division } from "@/Types/models";

const columns: ColumnDef<Division>[] = [{ key: "name", label: "Name" }];

const fields: FieldDef<Division>[] = [
  { key: "name", label: "Name", required: true, colSpan: 2, placeholder: "e.g. Team Delta" },
  {
    key: "parent_id",
    label: "Inside division (leave empty for a division)",
    type: "async-select",
    colSpan: 2,
    placeholder: "Search divisions…",
    loadOptions: (q) => searchDivisions(q, { divisionsOnly: true }),
    initialLabel: (row) => row.parent?.name,
    transform: (v) => (v === "" || v === undefined ? null : v),
  },
];

function DivisionCard(row: Division, actions: CardActions) {
  return (
    <div className="flex flex-col gap-1 border-b border-line p-3 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
      <div className="min-w-0">
        <div className="font-semibold">{row.name}</div>
        <div className="text-xs text-[var(--text-secondary)]">{row.parent ? `Team in ${row.parent.name}` : "Division"}</div>
      </div>
      <div className="-ml-2 flex flex-wrap text-xs sm:ml-0 sm:shrink-0">
        {actions.canEdit && (
          <button onClick={actions.onEdit} className="px-2 py-2.5 underline text-[var(--text-secondary)] hover:text-[var(--amber)]">
            Edit
          </button>
        )}
        {actions.canDelete && (
          <button onClick={actions.onDelete} className="px-2 py-2.5 underline text-[var(--text-secondary)] hover:text-[var(--critical)]">
            Remove
          </button>
        )}
      </div>
    </div>
  );
}

function DivisionsIndex() {
  const { can } = usePermission();
  const canManage = can("manage-depots");
  const [modal, setModal] = useState<{ mode: "create" | "edit"; row?: Division } | null>(null);

  return (
    <div>
      <PageHeader
        title="Divisions"
        description="The office's divisions and the teams inside them. An ICS 213-RR request picks its division or team from this list."
        actions={canManage && <Button onClick={() => setModal({ mode: "create" })}>+ Add division or team</Button>}
      />

      <div className="px-4 pb-6 sm:px-6">
        <DataTableCardField<Division>
          apiUrl="/api/divisions"
          apiQueryKey="divisions"
          columns={columns}
          renderCard={DivisionCard}
          editPermission="manage-depots"
          deletePermission="manage-depots"
          onEditRow={(row) => setModal({ mode: "edit", row })}
        />
      </div>

      {modal && (
        <FormModal<Division>
          open
          onClose={() => setModal(null)}
          title={modal.mode === "create" ? "Add division or team" : "Edit division or team"}
          submitLabel={modal.mode === "create" ? "Add" : "Save changes"}
          fields={fields}
          mode={modal.mode}
          row={modal.row}
          invalidateKeys={tableListInvalidateKeys("divisions")}
          mutationFn={async (payload) =>
            modal.mode === "create"
              ? divisionsService.create(payload as Partial<Division>)
              : divisionsService.update(modal.row!.id, payload as Partial<Division>)
          }
          onSuccess={() => setModal(null)}
        />
      )}
    </div>
  );
}

DivisionsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default DivisionsIndex;
