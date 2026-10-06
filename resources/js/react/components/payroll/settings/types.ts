/** Shared types and value helpers of the Payroll Settings pages. */

export type SettingType = 'percent' | 'money' | 'number' | 'hours' | 'minutes' | 'days' | 'time' | 'select' | 'text' | 'date';

export interface SettingField {
    key: string;
    label: string;
    type: SettingType;
    help?: string;
    min?: number;
    max?: number;
    step?: number;
    options?: Record<string, string>;
}

export interface SettingSection {
    key: string;
    title: string;
    description: string;
    fields: SettingField[];
}

export type SettingValues = Record<string, string | number>;

export interface Option {
    value: string;
    label: string;
    hint?: string;
}

export interface TestOptions {
    employees: Option[];
    groups: Option[];
    versions: Option[];
    period: { month: number; year: number; type: 'first' | 'second' };
    urls: { run: string; contributions: string };
}

export interface SettingsTabUrls {
    settings: string;
    rules: string;
    test: string;
}

const trim = (value: number, decimals = 4) => String(Number(value.toFixed(decimals)));

/** Stored value (config units, 1.25 = 125%) → what the input shows. */
export function toDisplay(field: SettingField, value: string | number | undefined): string {
    if (value === undefined || value === null) return '';
    if (field.type === 'percent') return trim(Number(value) * 100);
    return String(value);
}

/** What the input shows → stored value. */
export function fromDisplay(field: SettingField, display: string): string | number {
    if (['select', 'text', 'date', 'time'].includes(field.type)) return display;
    if (display.trim() === '') return '';
    const number = Number(display);
    if (Number.isNaN(number)) return display;
    return field.type === 'percent' ? Number((number / 100).toFixed(8)) : number;
}

/** A stored value as people read it ("125%", "₱10,000.00", "8 hr"). */
export function formatSetting(field: SettingField, value: string | number | undefined): string {
    if (value === undefined || value === '') return '—';
    const number = Number(value);
    const money = new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    switch (field.type) {
        case 'percent':
            return `${trim(number * 100)}%`;
        case 'money':
            return `₱${money.format(number)}`;
        case 'hours':
            return `${trim(number, 2)} hr`;
        case 'minutes':
            return `${number} min`;
        case 'days':
            return `${number} day${number === 1 ? '' : 's'}`;
        case 'select':
            return field.options?.[String(value)] ?? String(value);
        case 'time':
            return formatTime(String(value));
        default:
            return String(value);
    }
}

export function formatTime(value: string): string {
    const [hours, minutes] = value.split(':').map(Number);
    if (Number.isNaN(hours)) return value;
    const suffix = hours >= 12 ? 'PM' : 'AM';
    return `${((hours + 11) % 12) + 1}:${String(minutes ?? 0).padStart(2, '0')} ${suffix}`;
}

export function sameValue(a: string | number | undefined, b: string | number | undefined): boolean {
    if (typeof a === 'number' || typeof b === 'number') {
        return Math.abs(Number(a) - Number(b)) < 1e-7;
    }
    return String(a ?? '') === String(b ?? '');
}
