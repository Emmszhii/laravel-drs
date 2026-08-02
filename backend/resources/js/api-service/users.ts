import type { AdminUser } from '@/Types/models';
import { createCrudResource } from './http';

export const usersService = createCrudResource<AdminUser>({ baseUrl: '/api/settings/users' });
