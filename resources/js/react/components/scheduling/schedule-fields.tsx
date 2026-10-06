import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

/** Shared by the Work Schedule grid and the Work schedule tab of the employee profile. */

export type ScheduleStatus = 'scheduled' | 'rest_day' | 'inactive';
export type ScheduleShift = 'Regular Shift' | 'Flexible Shift';

export interface WorkdayRule {
    label: string;
    short_label: string;
    paid_hours: number;
    lunch_minutes: number;
    clock_minutes: number;
}

export const STATUS_OPTIONS: { value: ScheduleStatus; label: string; help: string }[] = [
    { value: 'scheduled', label: 'Scheduled', help: 'Normal attendance computation' },
    { value: 'rest_day', label: 'Rest Day', help: 'Permanent rest-day status' },
    { value: 'inactive', label: 'Inactive', help: 'Excluded from payroll attendance' },
];

export const SHIFT_OPTIONS: ScheduleShift[] = ['Regular Shift', 'Flexible Shift'];

/** Mirrors the save validation: Time Out = Time In + the workday's clock span. */
export function addMinutes(time: string, minutes: number): string {
    const [hours, mins] = time.split(':').map(Number);
    if (!Number.isFinite(hours) || !Number.isFinite(mins)) return '';

    const total = (((hours * 60 + mins + minutes) % 1440) + 1440) % 1440;

    return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
}

export function RowSelect({
    id,
    value,
    onChange,
    options,
    className,
    ariaLabel,
    disabled,
}: {
    id?: string;
    value: string;
    onChange: (value: string) => void;
    options: { value: string; label: string }[];
    className?: string;
    ariaLabel?: string;
    disabled?: boolean;
}) {
    return (
        <Select value={value} onValueChange={onChange} disabled={disabled}>
            <SelectTrigger id={id} className={cn('w-full', className)} aria-label={ariaLabel}>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

/** Compact Mon-Sun toggles; several days off may be selected. */
export function DayOffPicker({
    weekdays,
    value,
    onChange,
    disabled,
}: {
    weekdays: string[];
    value: string[];
    onChange: (value: string[]) => void;
    disabled?: boolean;
}) {
    const toggle = (day: string) =>
        onChange(
            value.includes(day)
                ? value.filter((selected) => selected !== day)
                : weekdays.filter((weekday) => weekday === day || value.includes(weekday)),
        );

    return (
        <div className="flex flex-wrap gap-1" role="group" aria-label="Weekly days off">
            {weekdays.map((day) => {
                const active = value.includes(day);

                return (
                    <button
                        key={day}
                        type="button"
                        title={day}
                        aria-pressed={active}
                        disabled={disabled}
                        onClick={() => toggle(day)}
                        className={cn(
                            'h-8 w-9 rounded-md border text-xs font-medium transition-colors disabled:opacity-50',
                            active
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'bg-background text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                        )}
                    >
                        {day.slice(0, 2)}
                    </button>
                );
            })}
        </div>
    );
}
