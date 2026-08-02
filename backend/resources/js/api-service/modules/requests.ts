import type { ResourceRequest } from "@/Types/models";
import { createModuleResource } from "../http";

export const requestsService = createModuleResource<ResourceRequest>({
  baseUrl: "/api/requests",
});
