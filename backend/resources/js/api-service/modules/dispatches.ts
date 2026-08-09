import type { Dispatch } from "@/Types/models";
import { createModuleResource } from "../http";
import { http, unwrap } from "../client";

export const dispatchesService = {
  ...createModuleResource<Dispatch>({ baseUrl: "/api/dispatches" }),

  /** GET /api/dispatches/drivers — minimal {id, name} list for the driver picker. */
  getDrivers: async (): Promise<{ id: string; name: string }[]> =>
    unwrap<{ id: string; name: string }[]>(await http.get("/api/dispatches/drivers")),

  /** POST /api/dispatches/{id}/position — driver-reported live GPS position. */
  updatePosition: async (
    id: string,
    lat: number,
    lng: number,
  ): Promise<Dispatch> =>
    unwrap<Dispatch>(
      await http.post(`/api/dispatches/${id}/position`, { lat, lng }),
    ),

  /** POST /api/dispatches/{id}/status — mark en_route/delivered/cancelled. */
  updateStatus: async (
    id: string,
    status: Dispatch["status"],
  ): Promise<Dispatch> =>
    unwrap<Dispatch>(
      await http.post(`/api/dispatches/${id}/status`, { status }),
    ),
};
