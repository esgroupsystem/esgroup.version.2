/** Badge colours for employee statuses (Active, Suspended, Terminated, ...). */
export function employeeStatusClass(status: string | null | undefined): string {
    const value = status ?? 'Active';

    if (value.startsWith('Active')) return 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400';
    if (value === 'On Leave') return 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400';
    if (value === 'Suspended') return 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400';
    if (value === 'Inactive') return 'text-muted-foreground';

    return 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400';
}

export interface DepartmentOption {
    id: string;
    name: string;
    positions: { id: string; title: string }[];
}
