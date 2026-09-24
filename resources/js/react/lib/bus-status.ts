/** Badge colours for a bus_details.status value (Active, Maintenance, Inactive, ...). */
export function busStatusClass(status: string | null | undefined): string {
    switch ((status ?? '').toLowerCase()) {
        case 'active':
        case 'available':
            return 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400';
        case 'maintenance':
        case 'repair':
        case 'under repair':
            return 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400';
        case 'out of service':
            return 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400';
        case 'inactive':
        case 'not active':
            return 'text-muted-foreground';
        default:
            return 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400';
    }
}
