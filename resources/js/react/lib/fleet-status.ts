import { tones } from '@/lib/dashboard-tones';

/** Badge colours for Bus::operational_status values. */
export function busConditionClass(status: string | null | undefined): string {
    switch (status) {
        case 'active':
            return tones.GREEN;
        case 'mechanical_breakdown':
            return tones.AMBER;
        case 'accident_related_breakdown':
            return tones.RED;
        case 'on_hold_plate_registration':
            return tones.SKY;
        case 'for_rental_charter':
            return 'border-indigo-300 text-indigo-700 dark:border-indigo-800 dark:text-indigo-400';
        default:
            return 'text-muted-foreground';
    }
}

/** Badge colours for bus_for_sale_records.status values. */
export function forSaleStatusClass(status: string | null | undefined): string {
    switch (status) {
        case 'mechanical_breakdown':
            return tones.AMBER;
        case 'accident_related':
            return tones.RED;
        case 'on_hold':
            return tones.SKY;
        case 'running_condition':
            return tones.GREEN;
        default:
            return 'text-muted-foreground';
    }
}
