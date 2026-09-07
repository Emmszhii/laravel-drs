import type { ResourceLoan } from "@/Types/models";
import { createModuleResource } from "../http";

export const resourceLoansService = createModuleResource<ResourceLoan>({
  baseUrl: "/api/resource-loans",
});
