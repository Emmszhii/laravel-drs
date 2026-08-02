import { Link, usePage } from "@inertiajs/react";
import { depotsService } from "@/api-service/modules/depots";
import { resourceTypesService } from "@/api-service/modules/resourceTypes";
import type { CardActions, ColumnDef, FieldDef } from "@/Components/table";
import type { ResourceStock, ResourceType } from "@/Types/models";

/** Real routes (not local tab state) so each subtab is a proper navigable/bookmarkable URL. */
export function ResourcesTabs() {
  const { url } = usePage();
  const onTypes = url.startsWith("/resources/types");

  return (
    <div className="flex gap-5 border-b border-white/10 px-6">
      <Link
        href="/resources"
        className={`border-b-2 py-2.5 text-sm font-semibold uppercase tracking-wide ${
          onTypes
            ? "border-transparent text-[var(--text-secondary)]"
            : "border-[var(--amber)]"
        }`}
      >
        Stock
      </Link>
      <Link
        href="/resources/types"
        className={`border-b-2 py-2.5 text-sm font-semibold uppercase tracking-wide ${
          onTypes
            ? "border-[var(--amber)]"
            : "border-transparent text-[var(--text-secondary)]"
        }`}
      >
        Resource types
      </Link>
    </div>
  );
}

export function StockCard(row: ResourceStock, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">{row.resource_type?.name ?? "—"}</div>
        <div className="text-xs text-[var(--text-secondary)]">
          {row.depot?.name ?? "—"} · Available {row.quantity_available} ·
          Reserved {row.quantity_reserved}
          {row.expiry_date ? ` · Expires ${row.expiry_date}` : ""}
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

export function TypeCard(row: ResourceType, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">{row.name}</div>
        <div className="text-xs text-[var(--text-secondary)]">
          Unit: {row.unit}
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

export const stockColumns: ColumnDef<ResourceStock>[] = [
  { key: "resource_type", label: "Type" },
  { key: "depot", label: "Depot" },
  { key: "quantity_available", label: "Available" },
  { key: "quantity_reserved", label: "Reserved" },
];

export const typeColumns: ColumnDef<ResourceType>[] = [
  { key: "name", label: "Name", filterable: false },
  { key: "unit", label: "Unit" },
];

export const stockFields: FieldDef<ResourceStock>[] = [
  {
    key: "depot_id",
    label: "Depot",
    type: "async-select",
    required: true,
    colSpan: 2,
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
    colSpan: 2,
    loadOptions: async (q: string) =>
      (await resourceTypesService.getAll())
        .filter((t) => t.name.toLowerCase().includes(q.toLowerCase()))
        .map((t) => ({ value: t.id, label: t.name })),
  },
  {
    key: "quantity_available",
    label: "Quantity available",
    type: "number",
    required: true,
  },
  {
    key: "quantity_reserved",
    label: "Reserved",
    type: "number",
    defaultValue: 0,
  },
  {
    key: "expiry_date",
    label: "Expiry date (optional)",
    type: "date",
    colSpan: 2,
  },
];

export const typeFields: FieldDef<ResourceType>[] = [
  {
    key: "name",
    label: "Name",
    required: true,
    colSpan: 2,
    placeholder: "e.g. Hygiene kits",
  },
  {
    key: "unit",
    label: "Unit",
    required: true,
    colSpan: 2,
    placeholder: "e.g. kits, packs, containers",
  },
];
