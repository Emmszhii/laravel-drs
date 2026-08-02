import type { AdminUser } from "@/Types/models";
import { createModuleResource } from "../http";

export const usersService = createModuleResource<AdminUser>({
  baseUrl: "/api/settings/users",
});
