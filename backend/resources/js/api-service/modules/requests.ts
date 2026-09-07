import type { ResourceRequest } from "@/Types/models";
import { createModuleResource } from "../http";
import { http, unwrap } from "../client";

const base = createModuleResource<ResourceRequest>({
  baseUrl: "/api/requests",
});

export const requestsService = {
  ...base,

  /** PATCH /{id}/approve — department-head sign-off, status/audit only. */
  approve: async (id: string): Promise<ResourceRequest> =>
    unwrap<ResourceRequest>(await http.patch(`/api/requests/${id}/approve`)),

  /** PATCH /{id}/reject — requires a reason. */
  reject: async (id: string, rejectionReason: string): Promise<ResourceRequest> =>
    unwrap<ResourceRequest>(
      await http.patch(`/api/requests/${id}/reject`, { rejection_reason: rejectionReason }),
    ),
};
