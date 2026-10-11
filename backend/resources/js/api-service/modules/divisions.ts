import type { Division } from "@/Types/models";
import type { FieldOption } from "@/Types/reusable/fields";
import { createModuleResource } from "../http";

export const divisionsService = createModuleResource<Division>({
  baseUrl: "/api/divisions",
});

/** Async-select options: divisions and teams matching `search` (the server also matches the parent division's name). */
export async function searchDivisions(search: string, { divisionsOnly = false } = {}): Promise<FieldOption[]> {
  const { data } = await divisionsService.getPaginatedFilterSearch({ search, per_page: 50 });
  return data.filter((d) => !divisionsOnly || !d.parent_id).map((d) => ({ value: divisionsOnly ? d.id : d.label, label: d.label }));
}
