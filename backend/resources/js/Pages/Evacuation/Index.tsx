import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { evacuationCentersService } from "@/api-service/modules/evacuationCenters";
import { incidentsService } from "@/api-service/modules/incidents";
import { usePermission } from "@/Hooks/use-permissions";
import type { ColumnDef, CardActions, FieldDef } from "@/Components/table";
import type { EvacuationCenter } from "@/Types/models";

const columns: ColumnDef<EvacuationCenter>[] = [
  { key: "name", label: "Name" },
  {
    key: "disaster",
    label: "Incident",
    render: (v) => (v as EvacuationCenter["disaster"])?.name ?? "—",
  },
  {
    key: "status",
    label: "Status",
    filterable: true,
    type: "select",
    typeData: [
      { value: "OPEN", label: "Open" },
      { value: "CLOSED", label: "Closed" },
    ],
  },
  { key: "families_served", label: "Families" },
  { key: "individuals_served", label: "Individuals" },
];

function EvacuationCard(row: EvacuationCenter, actions: CardActions) {
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
        {row.disaster?.name ?? "—"} · {row.address ?? "No address"}
      </div>
      <span
        className={`inline-block px-2 py-0.5 font-mono text-xs ${
          row.status === "OPEN"
            ? "bg-[var(--ok-dim,rgba(63,169,140,0.15))] text-[var(--ok,#3FA98C)]"
            : "bg-white/10 text-[var(--text-secondary)]"
        }`}
      >
        {row.status} · {row.families_served} families · {row.individuals_served} individuals
      </span>
    </div>
  );
}

const fields: FieldDef<EvacuationCenter>[] = [
  {
    key: "disaster_id",
    label: "Incident",
    type: "async-select",
    required: true,
    loadOptions: async (q: string) =>
      (await incidentsService.getAll())
        .filter((d) => d.name.toLowerCase().includes(q.toLowerCase()))
        .map((d) => ({ value: d.id, label: d.name })),
  },
  { key: "name", label: "Name", type: "text", required: true },
  { key: "address", label: "Address", type: "text" },
  { key: "lat", label: "Latitude", type: "number", required: true },
  { key: "lng", label: "Longitude", type: "number", required: true },
  { key: "capacity", label: "Capacity", type: "number" },
  { key: "families_served", label: "Families served", type: "number" },
  { key: "individuals_served", label: "Individuals served", type: "number" },
  {
    key: "status",
    label: "Status",
    type: "select",
    options: [
      { value: "OPEN", label: "Open" },
      { value: "CLOSED", label: "Closed" },
    ],
  },
];

function EvacuationIndex() {
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<EvacuationCenter | null>(null);
  const { can } = usePermission();
  const canManage = can("manage-evacuation-centers");

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6 py-3">
        <h1 className="text-lg font-semibold uppercase">Evacuation centers</h1>
        {canManage && (
          <Button
            onClick={() => {
              setEditing(null);
              setModalOpen(true);
            }}
          >
            + Log evacuation center
          </Button>
        )}
      </div>

      <div className="p-6">
        <DataTableCardField<EvacuationCenter>
          apiUrl="/api/evacuation-centers"
          apiQueryKey="evacuation-centers"
          columns={columns}
          renderCard={EvacuationCard}
          editPermission="manage-evacuation-centers"
          deletePermission="manage-evacuation-centers"
          onEditRow={(row) => {
            setEditing(row);
            setModalOpen(true);
          }}
        />
      </div>

      <FormModal<EvacuationCenter>
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editing ? "Edit evacuation center" : "Log evacuation center"}
        fields={fields}
        mode={editing ? "edit" : "create"}
        initialValues={editing ?? undefined}
        invalidateKeys={tableListInvalidateKeys("evacuation-centers")}
        mutationFn={async (payload) =>
          editing
            ? evacuationCentersService.update(editing.id, payload)
            : evacuationCentersService.create(payload)
        }
        onSuccess={() => setModalOpen(false)}
      />
    </div>
  );
}

EvacuationIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default EvacuationIndex;
