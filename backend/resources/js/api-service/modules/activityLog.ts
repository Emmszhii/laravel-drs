import { http, unwrap } from "../client";

export interface ActivityLogEntry {
  id: number;
  ts: string | null;
  tag: string;
  message: string;
}

export const activityLogService = {
  list: (limit = 30) =>
    http
      .get("/api/activity-log", { params: { limit } })
      .then((res) => unwrap<ActivityLogEntry[]>(res)),
};
