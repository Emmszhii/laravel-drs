import type { CardActions, ColumnDef, FieldDef } from "@/Components/table";
import type { ResourceType } from "@/Types/models";

const VED_LABELS: Record<string, string> = { V: "Vital", E: "Essential", D: "Desirable" };

/** Empty optional number inputs are sent as null, so the item keeps "not set". */
const optionalNumber = (value: unknown) => (value === "" || value === undefined ? null : value);

export function TypeCard(row: ResourceType, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">{row.name}</div>
        <div className="text-xs text-[var(--text-secondary)]">
          Unit: {row.unit} · {row.is_consumable ? "Consumable" : "Borrowed/equipment"} ·{" "}
          {VED_LABELS[row.ved_class] ?? row.ved_class} ·{" "}
          {row.unit_cost != null ? `₱${Number(row.unit_cost).toLocaleString()} each` : "No unit cost yet"}
          {row.space_per_unit != null ? ` · ${row.space_per_unit} m³ each` : ""}
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

export const typeColumns: ColumnDef<ResourceType>[] = [
  { key: "name", label: "Name", filterable: false },
  {
    key: "unit",
    label: "Unit",
    filterable: true,
  },
  {
    key: "ved_class",
    label: "VED class",
    filterable: true,
    type: "select",
    typeData: [
      { value: "V", label: "Vital" },
      { value: "E", label: "Essential" },
      { value: "D", label: "Desirable" },
    ],
  },
  {
    key: "is_consumable",
    label: "Kind",
    filterable: true,
    type: "select",
    typeData: [
      { value: "1", label: "Consumable" },
      { value: "0", label: "Borrowed/equipment" },
    ],
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
    label: "Standard unit",
    required: true,
    colSpan: 2,
    placeholder: "e.g. kits, packs, containers",
  },
  {
    key: "unit_cost",
    label: "Unit cost (₱)",
    type: "number",
    placeholder: "Latest purchase price",
    helpText: "The model skips items without a unit cost.",
    transform: optionalNumber,
  },
  {
    key: "ved_class",
    label: "VED class",
    type: "select",
    defaultValue: "E",
    required: true,
    options: [
      { value: "V", label: "Vital" },
      { value: "E", label: "Essential" },
      { value: "D", label: "Desirable" },
    ],
  },
  {
    key: "space_per_unit",
    label: "Space per unit (m³, optional)",
    type: "number",
    colSpan: 2,
    helpText: "Depot space is modeled only when every item and every depot has a space figure.",
    transform: optionalNumber,
  },
  {
    key: "is_consumable",
    label: "Consumable (used up, not returned)",
    type: "checkbox",
    defaultValue: true,
    colSpan: 2,
    helpText: "Uncheck for borrowed tools/equipment that need sign-in/sign-out and a return date.",
  },
];
