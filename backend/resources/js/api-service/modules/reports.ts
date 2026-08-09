import type { Report } from "@/Types/models";
import { createModuleResource } from "../http";

const crud = createModuleResource<
  Report,
  { disaster_id: string; type: Report["type"] }
>({ baseUrl: "/api/reports" });

export const reportsService = {
  ...crud,

  /** Native browser download (Content-Disposition: attachment) — no axios needed. */
  downloadUrl: (id: string, format?: "pdf" | "csv") =>
    `/api/reports/${id}/download${format ? `?format=${format}` : ""}`,
};
