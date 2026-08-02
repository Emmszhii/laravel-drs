import type { AdminUser } from "@/Types/models";
import { createModuleResource } from "../http";
import { http, unwrap } from "../client";

const baseResource = createModuleResource<AdminUser>({
  baseUrl: "/api/settings/users",
});

export const usersService = {
  ...baseResource,

  /** POST /{id}/suspend — sets is_active=false; blocks login and force-logs-out any active session. */
  suspend: async (id: string | number): Promise<AdminUser> =>
    unwrap<AdminUser>(await http.post(`${baseResource.baseUrl}/${id}/suspend`)),

  /** POST /{id}/activate — sets is_active=true. */
  activate: async (id: string | number): Promise<AdminUser> =>
    unwrap<AdminUser>(await http.post(`${baseResource.baseUrl}/${id}/activate`)),

  /** POST /{id}/reset-password — emails the user a password-reset link. */
  resetPassword: async (id: string | number): Promise<null> =>
    unwrap<null>(await http.post(`${baseResource.baseUrl}/${id}/reset-password`)),
};
