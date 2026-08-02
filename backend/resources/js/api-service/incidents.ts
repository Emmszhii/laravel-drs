import type { Disaster, Zone } from '@/Types/models';
import { createCrudResource } from './http';
import { http, unwrap } from './client';

export const incidentsService = {
    ...createCrudResource<Disaster>({ baseUrl: '/api/incidents' }),

    addArea: async (
        incidentId: string,
        data: { name: string; population_affected: number; severity_level: 'High' | 'Medium'; lat?: number; lng?: number },
    ): Promise<Zone> => unwrap<Zone>(await http.post(`/api/incidents/${incidentId}/areas`, data)),

    removeArea: async (incidentId: string, areaId: string): Promise<null> =>
        unwrap<null>(await http.delete(`/api/incidents/${incidentId}/areas/${areaId}`)),

    optimize: async (incidentId: string): Promise<void> => {
        await http.post(`/incidents/${incidentId}/optimize`);
    },
};
