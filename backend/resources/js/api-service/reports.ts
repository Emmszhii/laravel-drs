import type { Report } from '@/Types/models';
import { createCrudResource } from './http';

const crud = createCrudResource<Report, { disaster_id: string; type: Report['type'] }>({ baseUrl: '/api/reports' });

export const reportsService = {
    ...crud,

    /** Native browser download (Content-Disposition: attachment) — no axios needed. */
    downloadUrl: (id: string) => `/api/reports/${id}/download`,
};
