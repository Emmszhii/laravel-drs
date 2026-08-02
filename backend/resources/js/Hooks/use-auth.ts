import { usePage } from '@inertiajs/react';

export interface AuthUser {
    id: string;
    name: string;
    email: string;
    role: string;
    is_active?: boolean;
}

function initialsFor(name: string): string {
    const parts = name.trim().split(/\s+/);
    return parts
        .slice(0, 2)
        .map((p) => p[0]?.toUpperCase() ?? '')
        .join('');
}

/**
 * The logged-in user, shared by App\Http\Middleware\HandleInertiaRequests
 * (raw `auth()->user()` model — `{id, name, email, role, is_active}`).
 */
export function useAuth() {
    const { props } = usePage();
    const user = (props.auth?.user ?? null) as AuthUser | null;

    return {
        user,
        role: user?.role ?? 'viewer',
        displayName: user?.name ?? 'Guest',
        email: user?.email ?? '',
        initials: user ? initialsFor(user.name) : '—',
    };
}
