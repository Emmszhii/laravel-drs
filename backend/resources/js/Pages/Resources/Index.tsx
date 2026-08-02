import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { depotsService } from "@/api-service/depots";
import { resourceTypesService } from "@/api-service/resourceTypes";
import { resourcesService } from "@/api-service/resources";
import type { ColumnDef, CardActions, FieldDef } from "@/Components/table";
import type { ResourceStock, ResourceType } from "@/Types/models";

function StockCard(row: ResourceStock, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">{row.resource_type?.name ?? "—"}</div>
        <div className="text-xs text-[var(--text-secondary)]">
          {row.depot?.name ?? "—"} · Available {row.quantity_available} · Reserved {row.quantity_reserved}
          {row.expiry_date ? ` · Expires ${row.expiry_date}` : ""}
        </div>
      </div>
      <div className="flex gap-3 text-xs">
        {actions.canEdit && (
          <button onClick={actions.onEdit} className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]">
            Edit
          </button>
        )}
        {actions.canDelete && (
          <button onClick={actions.onDelete} className="underline text-[var(--text-secondary)] hover:text-[var(--critical)]">
            Remove
          </button>
        )}
      </div>
    </div>
  );
}

function TypeCard(row: ResourceType, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">{row.name}</div>
        <div className="text-xs text-[var(--text-secondary)]">Unit: {row.unit}</div>
      </div>
      <div className="flex gap-3 text-xs">
        {actions.canEdit && (
          <button onClick={actions.onEdit} className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]">
            Edit
          </button>
        )}
        {actions.canDelete && (
          <button onClick={actions.onDelete} className="underline text-[var(--text-secondary)] hover:text-[var(--critical)]">
            Remove
          </button>
        )}
      </div>
    </div>
  );
}

const stockColumns: ColumnDef<ResourceStock>[] = [
  { key: "resource_type", label: "Type" },
  { key: "depot", label: "Depot" },
  { key: "quantity_available", label: "Available" },
  { key: "quantity_reserved", label: "Reserved" },
];

const typeColumns: ColumnDef<ResourceType>[] = [
  { key: "name", label: "Name", filterable: false },
  { key: "unit", label: "Unit" },
];

const stockFields: FieldDef<ResourceStock>[] = [
  {
    key: "depot_id",
    label: "Depot",
    type: "async-select",
    required: true,
    loadOptions: async (q: string) =>
      (await depotsService.getAll())
        .filter((d) => d.name.toLowerCase().includes(q.toLowerCase()))
        .map((d) => ({ value: d.id, label: d.name })),
  },
  {
    key: "resource_type_id",
    label: "Resource type",
    type: "async-select",
    required: true,
    loadOptions: async (q: string) =>
      (await resourceTypesService.getAll())
        .filter((t) => t.name.toLowerCase().includes(q.toLowerCase()))
        .map((t) => ({ value: t.id, label: t.name })),
  },
  { key: "quantity_available", label: "Available quantity", type: "number", required: true },
  { key: "quantity_reserved", label: "Reserved quantity", type: "number", defaultValue: 0 },
  { key: "expiry_date", label: "Expiry date (optional)", type: "date" },
];

const typeFields: FieldDef<ResourceType>[] = [
  { key: "name", label: "Name", required: true },
  { key: "unit", label: "Unit", required: true, placeholder: "e.g. boxes, liters, units" },
];

function ResourcesIndex() {
  const [subtab, setSubtab] = useState<"stock" | "types">("stock");
  const [stockModal, setStockModal] = useState<{ mode: "create" | "edit"; row?: ResourceStock } | null>(null);
  const [typeModal, setTypeModal] = useState<{ mode: "create" | "edit"; row?: ResourceType } | null>(null);

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6">
        <div className="flex gap-5">
          <button
            onClick={() => setSubtab("stock")}
            className={`border-b-2 py-2.5 text-sm font-semibold uppercase tracking-wide ${
              subtab === "stock" ? "border-[var(--amber)]" : "border-transparent text-[var(--text-secondary)]"
            }`}
          >
            Stock
          </button>
          <button
            onClick={() => setSubtab("types")}
            className={`border-b-2 py-2.5 text-sm font-semibold uppercase tracking-wide ${
              subtab === "types" ? "border-[var(--amber)]" : "border-transparent text-[var(--text-secondary)]"
            }`}
          >
            Resource types
          </button>
        </div>
        <Button
          className="my-2"
          onClick={() => (subtab === "stock" ? setStockModal({ mode: "create" }) : setTypeModal({ mode: "create" }))}
        >
          {subtab === "stock" ? "+ Add stock" : "+ Add type"}
        </Button>
      </div>

      <div className="p-6">
        {subtab === "stock" ? (
          <DataTableCardField<ResourceStock>
            key="stock"
            apiUrl="/api/resources"
            apiQueryKey="resources"
            columns={stockColumns}
            renderCard={StockCard}
            onEditRow={(row) => setStockModal({ mode: "edit", row })}
          />
        ) : (
          <DataTableCardField<ResourceType>
            key="types"
            apiUrl="/api/resource-types"
            apiQueryKey="resource-types"
            columns={typeColumns}
            renderCard={TypeCard}
            onEditRow={(row) => setTypeModal({ mode: "edit", row })}
          />
        )}
      </div>

      {stockModal && (
        <FormModal<ResourceStock>
          open
          onClose={() => setStockModal(null)}
          title={stockModal.mode === "create" ? "Add stock" : "Edit stock"}
          fields={stockFields}
          mode={stockModal.mode}
          row={stockModal.row}
          invalidateKeys={tableListInvalidateKeys("resources")}
          mutationFn={async (payload) =>
            stockModal.mode === "create"
              ? resourcesService.create(payload as Partial<ResourceStock>)
              : resourcesService.update(stockModal.row!.id, payload as Partial<ResourceStock>)
          }
          onSuccess={() => setStockModal(null)}
        />
      )}

      {typeModal && (
        <FormModal<ResourceType>
          open
          onClose={() => setTypeModal(null)}
          title={typeModal.mode === "create" ? "Add resource type" : "Edit resource type"}
          fields={typeFields}
          mode={typeModal.mode}
          row={typeModal.row}
          invalidateKeys={tableListInvalidateKeys("resource-types")}
          mutationFn={async (payload) =>
            typeModal.mode === "create"
              ? resourceTypesService.create(payload as Partial<ResourceType>)
              : resourceTypesService.update(typeModal.row!.id, payload as Partial<ResourceType>)
          }
          onSuccess={() => setTypeModal(null)}
        />
      )}
    </div>
  );
}

ResourcesIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default ResourcesIndex;
