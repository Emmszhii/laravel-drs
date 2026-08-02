import { useCallback, useEffect, useState } from 'react';

export type Theme = 'light' | 'dark';

const THEME_KEY = 'disasterresource_theme';

function detectSystemTheme(): Theme {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function getStoredTheme(): Theme | null {
    try {
        const value = localStorage.getItem(THEME_KEY);
        return value === 'light' || value === 'dark' ? value : null;
    } catch {
        return null;
    }
}

function setStoredTheme(theme: Theme): void {
    try {
        localStorage.setItem(THEME_KEY, theme);
    } catch {
        // ignore
    }
}

/** Light/dark theme, backed by `<html data-theme>` + localStorage (falls back to prefers-color-scheme). */
export function useTheme() {
    const [theme, setTheme] = useState<Theme>(() => getStoredTheme() ?? detectSystemTheme());

    useEffect(() => {
        document.documentElement.setAttribute('data-theme', theme);
        setStoredTheme(theme);
    }, [theme]);

    const toggle = useCallback(() => {
        setTheme((prev) => (prev === 'dark' ? 'light' : 'dark'));
    }, []);

    return { theme, toggle };
}
