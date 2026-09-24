import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const prefersDark = () => window.matchMedia('(prefers-color-scheme: dark)').matches;

function applyTheme(appearance: Appearance) {
    const dark = appearance === 'dark' || (appearance === 'system' && prefersDark());
    document.documentElement.classList.toggle('dark', dark);
}

function readStoredAppearance(): Appearance {
    try {
        const stored = localStorage.getItem('theme');
        return stored === 'dark' || stored === 'system' ? stored : 'light';
    } catch {
        return 'light';
    }
}

/** Light / dark / system theme, remembered per browser. */
export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>(readStoredAppearance);

    const updateAppearance = useCallback((value: Appearance) => {
        setAppearance(value);
        try {
            localStorage.setItem('theme', value);
        } catch {
            // Storage can be unavailable (private mode); the theme still applies for this page.
        }
        applyTheme(value);
    }, []);

    useEffect(() => {
        applyTheme(appearance);

        if (appearance !== 'system') return;

        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const onChange = () => applyTheme('system');
        media.addEventListener('change', onChange);

        return () => media.removeEventListener('change', onChange);
    }, [appearance]);

    return { appearance, updateAppearance } as const;
}
