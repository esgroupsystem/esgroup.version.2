/** Badge colours for IT ticket / CCTV concern statuses. */
export function ticketStatusClass(label: string | null | undefined): string {
    switch ((label ?? '').toLowerCase()) {
        case 'completed':
        case 'done':
            return 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400';
        case 'in progress':
            return 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400';
        case 'rejected':
        case 'disapproved':
            return 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400';
        default:
            return 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400';
    }
}

/** Badge colours for low / medium / high / urgent priorities. */
export function priorityClass(priority: string | null | undefined): string {
    switch ((priority ?? '').toLowerCase()) {
        case 'high':
        case 'urgent':
        case 'critical':
            return 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400';
        case 'medium':
            return 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400';
        default:
            return 'text-muted-foreground';
    }
}

/** Badge colours for CCTV concern statuses (Open / In Progress / Fixed / Closed). */
export function concernStatusClass(status: string): string {
    return (
        {
            Open: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
            'In Progress': 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400',
            Fixed: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
            Closed: 'text-muted-foreground',
        }[status] ?? ''
    );
}
