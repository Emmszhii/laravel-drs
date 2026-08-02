import type { Depot } from "@/Types/models";
import { createModuleResource } from "../http";

export const depotsService = createModuleResource<Depot>({
  baseUrl: "/api/depots",
});
