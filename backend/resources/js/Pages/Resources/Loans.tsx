import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { resourceLoansService } from "@/api-service/modules/resourceLoans";
import { resourcesService } from "@/api-service/modules/resources";
import { resourceTypesService } from "@/api-service/modules/resourceTypes";
import { usePermission } from "@/Hooks/use-permissions";
import { ResourcesTabs } from "./shared";
import type { CardActions, ColumnDef, FieldDef } from "@/Components/table";
import type { ResourceLoan } from "@/Types/models";

const STATUS_LABELS: Record<ResourceLoan["status"], string> = {
  issued: "Issued",
  returned: "Returned",
  for_repair: "For repair",
  condemned: "Condemned",
  surrendered: "Surrendered",
};

const loanColumns: ColumnDef<ResourceLoan>[] = [
  {
    key: "resource",
    label: "Item",
    render: (v) => (v as ResourceLoan["resource"])?.resource_type?.name ?? "—",
  },
  { key: "borrower_name", label: "Borrower" },
  { key: "quantity", label: "Qty" },
  { key: "due_at", label: "Due" },
  {
    key: "status",
    label: "Status",
    filterable: true,
    type: "select",
    typeData: Object.entries(STATUS_LABELS).map(([value, label]) => ({ value, label })),
  },
];

function LoanCard(row: ResourceLoan, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">
          {row.resource?.resource_type?.name ?? "Item"} · {row.quantity}
        </div>
        <div className="text-xs text-[var(--text-secondary)]">
          Borrower: {row.borrower_name}
          {row.borrower_contact ? ` (${row.borrower_contact})` : ""}
          {row.due_at ? ` · Due ${row.due_at}` : ""} · {STATUS_LABELS[row.status]}
        </div>
      </div>
      {actions.canEdit && (
        <button
          onClick={actions.onEdit}
          className="text-xs underline text-[var(--text-secondary)] hover:text-[var(--amber)]"
        >
          Update status
        </button>
      )}
    </div>
  );
}

const createFields: FieldDef<ResourceLoan>[] = [
  {
    key: "resource_id",
    label: "Item (depot stock, non-consumable)",
    type: "async-select",
    required: true,
    colSpan: 2,
    loadOptions: async (q: string) => {
      const [resources, types] = await Promise.all([
        resourcesService.getAll(),
        resourceTypesService.getAll(),
      ]);
      const nonConsumableTypeIds = new Set(types.filter((t) => !t.is_consumable).map((t) => t.id));
      return resources
        .filter((r) => nonConsumableTypeIds.has(r.resource_type_id))
        .filter((r) => (r.resource_type?.name ?? "").toLowerCase().includes(q.toLowerCase()))
        .map((r) => ({
          value: r.id,
          label: `${r.resource_type?.name ?? "—"} · ${r.depot?.name ?? "—"} (${r.quantity_available} available)`,
        }));
    },
  },
  { key: "borrower_name", label: "Borrower name", required: true },
  { key: "borrower_contact", label: "Borrower contact (optional)" },
  { key: "quantity", label: "Quantity", type: "number", required: true },
  { key: "due_at", label: "Due date (optional)", type: "date" },
  { key: "notes", label: "Notes (optional)", type: "textarea", colSpan: 2 },
];

const editFields: FieldDef<ResourceLoan>[] = [
  {
    key: "status",
    label: "Status",
    type: "select",
    required: true,
    options: Object.entries(STATUS_LABELS).map(([value, label]) => ({ value, label })),
  },
  { key: "notes", label: "Notes (optional)", type: "textarea", colSpan: 2 },
];

function ResourcesLoansIndex() {
  const [modal, setModal] = useState<{ mode: "create" | "edit"; row?: ResourceLoan } | null>(null);
  const { can } = usePermission();
  const canManage = can("manage-depots");

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6">
        <ResourcesTabs />
        {canManage && (
          <Button className="my-2" onClick={() => setModal({ mode: "create" })}>
            + Log borrowed item
          </Button>
        )}
      </div>

      <div className="p-6">
        <DataTableCardField<ResourceLoan>
          apiUrl="/api/resource-loans"
          apiQueryKey="resource-loans"
          columns={loanColumns}
          renderCard={LoanCard}
          editPermission="manage-depots"
          onEditRow={(row) => setModal({ mode: "edit", row })}
        />
      </div>

      {modal && (
        <FormModal<ResourceLoan>
          open
          onClose={() => setModal(null)}
          title={modal.mode === "create" ? "Log borrowed item" : "Update loan status"}
          submitLabel={modal.mode === "create" ? "Log item" : "Save changes"}
          fields={modal.mode === "create" ? createFields : editFields}
          mode={modal.mode}
          row={modal.row}
          invalidateKeys={tableListInvalidateKeys("resource-loans")}
          mutationFn={async (payload) =>
            modal.mode === "create"
              ? resourceLoansService.create(payload as Partial<ResourceLoan>)
              : resourceLoansService.update(modal.row!.id, payload as Partial<ResourceLoan>)
          }
          onSuccess={() => setModal(null)}
        />
      )}
    </div>
  );
}

ResourcesLoansIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default ResourcesLoansIndex;
