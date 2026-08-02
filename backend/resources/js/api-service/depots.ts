import type { Depot } from '@/Types/models';
import { createCrudResource } from './http';

export const depotsService = createCrudResource<Depot>({ baseUrl: '/api/depots' });
