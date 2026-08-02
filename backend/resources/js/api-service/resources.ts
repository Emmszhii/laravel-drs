import type { ResourceStock } from '@/Types/models';
import { createCrudResource } from './http';

export const resourcesService = createCrudResource<ResourceStock>({ baseUrl: '/api/resources' });
