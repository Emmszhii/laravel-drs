import type { ResourceType } from "@/Types/models";
import { createModuleResource } from "../http";

export const resourceTypesService = createModuleResource<ResourceType>({
  baseUrl: "/api/resource-types",
});
