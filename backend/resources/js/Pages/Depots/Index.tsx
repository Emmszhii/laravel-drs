import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { depotsService } from "@/api-service/modules/depots";
import { usePermission } from "@/Hooks/use-permissions";
import type { CardActions, ColumnDef, FieldDef } from "@/Components/table";
import type { Depot } from "@/Types/models";

const STATUS_OPTIONS = [
  { value: "OPERATIONAL", label: "Operational" },
  { value: "LIMITED CAPACITY", label: "Limited capacity" },
  { value: "OFFLINE", label: "Offline" },
];

const columns: ColumnDef<Depot>[] = [
  { key: "name", label: "Name" },
  { key: "address", label: "Address" },
  { key: "status", label: "Status", filterable: true, type: "select", typeData: STATUS_OPTIONS },
];

const fields: FieldDef<Depot>[] = [
  { key: "name", label: "Depot name", required: true, colSpan: 2 },
  { key: "status", label: "Status", type: "select", required: true, defaultValue: "OPERATIONAL", options: STATUS_OPTIONS },
  {
    key: "free_space",
    label: "Free space (m³, optional)",
    type: "number",
    helpText: "Depot space is modeled only when every depot and every item has a space figure.",
    transform: (v) => (v === "" || v === undefined ? null : v),
  },
  { key: "address", label: "Address", required: true, colSpan: 2, placeholder: "e.g. City Hall compound, San Pablo City" },
  { key: "contact_first_name", label: "Contact first name" },
  { key: "contact_last_name", label: "Contact last name" },
  { key: "contact_phone", label: "Contact phone" },
  { key: "contact_email", label: "Contact email", type: "email" },
];

function DepotCard(row: Depot, actions: CardActions) {
  return (
    <div className="bg-[var(--bg-void)] p-5">
      <div className="mb-1 flex items-start justify-between">
        <div className="text-[15px] font-medium">{row.name}</div>
        <div className="flex gap-2 text-xs">
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
      <div className="mb-2.5 text-xs text-[var(--text-secondary)]">
        {row.address} · {row.free_space != null ? `${row.free_space} m³ free` : "Free space not measured"}
      </div>
      <span className="inline-block bg-[var(--ok-dim,rgba(63,169,140,0.15))] px-2 py-0.5 font-mono text-xs text-[var(--ok,#3FA98C)]">
        {row.status}
      </span>
    </div>
  );
}

function DepotsIndex() {
  const { can } = usePermission();
  const canManage = can("manage-depots");
  const [modal, setModal] = useState<{ mode: "create" | "edit"; row?: Depot } | null>(null);

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6 py-3">
        <h1 className="text-lg font-semibold uppercase">Depots</h1>
        {canManage && <Button onClick={() => setModal({ mode: "create" })}>+ Add depot</Button>}
      </div>

      <div className="p-6">
        <DataTableCardField<Depot>
          apiUrl="/api/depots"
          apiQueryKey="depots"
          columns={columns}
          renderCard={DepotCard}
          editPermission="manage-depots"
          deletePermission="manage-depots"
          onEditRow={(row) => setModal({ mode: "edit", row })}
        />
      </div>

      {modal && (
        <FormModal<Depot>
          open
          onClose={() => setModal(null)}
          title={modal.mode === "create" ? "Add depot" : "Edit depot"}
          submitLabel={modal.mode === "create" ? "Add depot" : "Save changes"}
          fields={fields}
          mode={modal.mode}
          row={modal.row}
          invalidateKeys={tableListInvalidateKeys("depots")}
          mutationFn={async (payload) =>
            modal.mode === "create"
              ? depotsService.create(payload as Partial<Depot>)
              : depotsService.update(modal.row!.id, payload as Partial<Depot>)
          }
          onSuccess={() => setModal(null)}
        />
      )}
    </div>
  );
}

DepotsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default DepotsIndex;
