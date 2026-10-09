import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

/** Shared by the Work Schedule grid and the Work schedule tab of the employee profile. */

export type ScheduleStatus = 'scheduled' | 'rest_day' | 'inactive';
export type ScheduleShift = 'Regular Shift' | 'Flexible Shift';
export type FlexibleMode = 'anytime' | 'condition' | 'custom';

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

/** Flexible Shift sub-modes. "anytime" is the legacy default: no clock-in window at all. */
export const FLEXIBLE_MODE_OPTIONS: { value: FlexibleMode; label: string; help: string }[] = [
    { value: 'anytime', label: 'Anytime', help: 'No clock-in window. Must complete the required clock hours any time in the day.' },
    { value: 'condition', label: 'Condition (clock-in window)', help: 'Must clock in within the window below; clock-out is whenever the required clock hours are completed.' },
    { value: 'custom', label: 'Custom time in/out', help: 'Fixed time in and time out; late/undertime work the same way as Regular Shift.' },
];

/** Quick-fill presets for a time in / time out pair. Not a restricted list — any time may still be typed. */
export const TIME_PRESETS: { label: string; time_in: string; time_out: string }[] = [
    { label: '6:00 AM – 3:00 PM', time_in: '06:00', time_out: '15:00' },
    { label: '9:00 AM – 6:00 PM', time_in: '09:00', time_out: '18:00' },
    { label: '8:00 AM – 5:00 PM', time_in: '08:00', time_out: '17:00' },
];

/** Tappable presets that fill a time in / time out pair, e.g. a Flexible (Condition) clock-in window. */
export function TimePresetButtons({ onPick, disabled }: { onPick: (preset: { time_in: string; time_out: string }) => void; disabled?: boolean }) {
    return (
        <div className="flex flex-wrap gap-1">
            {TIME_PRESETS.map((preset) => (
                <button
                    key={preset.label}
                    type="button"
                    disabled={disabled}
                    onClick={() => onPick({ time_in: preset.time_in, time_out: preset.time_out })}
                    className="rounded-md border px-1.5 py-0.5 text-xs whitespace-nowrap text-muted-foreground hover:bg-accent hover:text-accent-foreground disabled:opacity-50"
                >
                    {preset.label}
                </button>
            ))}
        </div>
    );
}

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

/** One weekday of a "different time per day" schedule. */
export interface DayTimes {
    time_in: string;
    time_out: string;
    workday_type: string;
}

export type WeeklyTimes = Record<string, DayTimes>;

/** Working days (not a day off) with the times to start from: saved per-day times, else the fixed times. */
export function fillWeeklyTimes(weekdays: string[], dayOffs: string[], current: WeeklyTimes, fallback: DayTimes): WeeklyTimes {
    return Object.fromEntries(weekdays.filter((day) => !dayOffs.includes(day)).map((day) => [day, current[day] ?? { ...fallback }]));
}

/** "Same every day" / "Different per day" switch. */
export function PatternToggle({ perDay, onChange, disabled }: { perDay: boolean; onChange: (perDay: boolean) => void; disabled?: boolean }) {
    return (
        <div className="inline-flex w-fit rounded-lg bg-muted p-1" role="radiogroup" aria-label="Schedule pattern">
            {(
                [
                    [false, 'Same every day'],
                    [true, 'Different per day'],
                ] as const
            ).map(([value, label]) => (
                <button
                    key={label}
                    type="button"
                    role="radio"
                    aria-checked={perDay === value}
                    disabled={disabled}
                    onClick={() => onChange(value)}
                    className={cn(
                        'h-8 rounded-md px-3 text-sm font-medium disabled:opacity-50',
                        perDay === value ? 'bg-background shadow-sm' : 'text-muted-foreground hover:text-foreground',
                    )}
                >
                    {label}
                </button>
            ))}
        </div>
    );
}

/**
 * Time in, work hours and time out for each working day. Time out follows time in and work hours
 * (the same rule the save checks); days off are skipped.
 */
export function WeeklyTimesEditor({
    weekdays,
    dayOffs,
    value,
    rules,
    onChange,
    error,
    disabled,
}: {
    weekdays: string[];
    dayOffs: string[];
    value: WeeklyTimes;
    rules: Record<string, WorkdayRule>;
    onChange: (value: WeeklyTimes) => void;
    error?: (day: string) => string | undefined;
    disabled?: boolean;
}) {
    const working = weekdays.filter((day) => !dayOffs.includes(day));

    const change = (day: string, changes: Partial<DayTimes>) => {
        const next = { ...value[day], ...changes };
        const rule = rules[next.workday_type];
        if (('time_in' in changes || 'workday_type' in changes) && rule && next.time_in) next.time_out = addMinutes(next.time_in, rule.clock_minutes);
        onChange({ ...value, [day]: next });
    };

    if (working.length === 0) {
        return <p className="text-sm text-muted-foreground">Every day is a day off.</p>;
    }

    return (
        <div className="grid gap-2">
            <div className="hidden grid-cols-[6rem_minmax(0,1fr)_minmax(0,1.6fr)_minmax(0,1fr)] gap-2 px-1 text-xs font-medium text-muted-foreground sm:grid">
                <span>Day</span>
                <span>Time in</span>
                <span>Work hours</span>
                <span>Time out</span>
            </div>
            {working.map((day) => {
                const times = value[day];
                if (!times) return null;
                const message = error?.(day);

                return (
                    <div key={day} className="grid gap-1">
                        <div className="grid grid-cols-2 items-center gap-2 rounded-lg border p-2 sm:grid-cols-[6rem_minmax(0,1fr)_minmax(0,1.6fr)_minmax(0,1fr)] sm:border-0 sm:p-0">
                            <span className="col-span-2 text-sm font-medium sm:col-span-1">{day}</span>
                            <Input
                                type="time"
                                aria-label={`${day} time in`}
                                value={times.time_in}
                                disabled={disabled}
                                onChange={(event) => change(day, { time_in: event.target.value })}
                            />
                            <RowSelect
                                ariaLabel={`${day} work hours`}
                                value={times.workday_type}
                                disabled={disabled}
                                onChange={(workday_type) => change(day, { workday_type })}
                                options={Object.entries(rules).map(([key, rule]) => ({ value: key, label: rule.short_label }))}
                                className="col-span-2 sm:col-span-1"
                            />
                            <Input
                                type="time"
                                aria-label={`${day} time out`}
                                value={times.time_out}
                                disabled={disabled}
                                aria-invalid={!!message}
                                onChange={(event) => change(day, { time_out: event.target.value })}
                                className="col-start-2 sm:col-start-auto"
                            />
                        </div>
                        {message && <p className="text-xs text-destructive">{message}</p>}
                    </div>
                );
            })}
        </div>
    );
}

/** "Mon 9:00–6:00 PM, Tue 6:00–3:00 PM" */
export function weeklySummary(weekly: WeeklyTimes): string {
    const format = (time: string) => {
        const [hours, minutes] = time.split(':').map(Number);
        return `${((hours + 11) % 12) + 1}:${String(minutes).padStart(2, '0')}`;
    };
    const suffix = (time: string) => (Number(time.split(':')[0]) >= 12 ? ' PM' : ' AM');

    return Object.entries(weekly)
        .map(([day, times]) => `${day.slice(0, 3)} ${format(times.time_in)}–${format(times.time_out)}${suffix(times.time_out)}`)
        .join(', ');
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
