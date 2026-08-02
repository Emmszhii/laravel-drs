import type { ResourceStock } from "@/Types/models";
import { createModuleResource } from "../http";

export const resourcesService = createModuleResource<ResourceStock>({
  baseUrl: "/api/resources",
});
