const GREEN = 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400';
const AMBER = 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400';
const RED = 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400';
const SKY = 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400';

/** Badge colours for leave statuses (Approved, Pending, Rejected, ...). */
export function leaveStatusClass(status: string | null | undefined): string {
    const value = (status ?? '').toLowerCase();

    if (value.includes('approv') && !value.includes('dis')) return GREEN;
    if (value.includes('pend') || value.includes('review')) return AMBER;
    if (value.includes('reject') || value.includes('disapprov') || value.includes('cancel') || value.includes('declin')) return RED;

    return 'text-muted-foreground';
}

/** Badge colours for stock levels (in stock / low / out). */
export function stockLevelClass(level: 'ok' | 'low' | 'out' | string): string {
    if (level === 'out') return RED;
    if (level === 'low') return AMBER;

    return GREEN;
}

export const tones = { GREEN, AMBER, RED, SKY };
