import type { ResourceType } from '@/Types/models';
import { createCrudResource } from './http';

export const resourceTypesService = createCrudResource<ResourceType>({ baseUrl: '/api/resource-types' });
