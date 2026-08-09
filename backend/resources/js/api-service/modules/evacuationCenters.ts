import type { EvacuationCenter } from "@/Types/models";
import { createModuleResource } from "../http";

export const evacuationCentersService = createModuleResource<EvacuationCenter>({
  baseUrl: "/api/evacuation-centers",
});
