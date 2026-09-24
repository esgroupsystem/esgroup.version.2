import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const MONTHS = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

export interface CutoffValue {
    cutoff_month: number;
    cutoff_year: number;
    /** Legacy keys: "second" = business 1st cutoff (26-10), "first" = business 2nd cutoff (11-25). */
    cutoff_type: 'first' | 'second';
}

function dayLabel(year: number, monthIndex: number, day: number, includeYear: boolean): string {
    const safeMonth = ((monthIndex % 12) + 12) % 12;
    const adjustedYear = year + Math.floor(monthIndex / 12);
    const label = `${MONTHS[safeMonth]} ${day}`;

    return includeYear ? `${label}, ${adjustedYear}` : label;
}

/** Same wording the Blade layout script produced for the cutoff dropdown. */
export function cutoffLabels(month: number, year: number): { second: string; first: string } {
    const monthIndex = month - 1;
    const crossesYear = monthIndex - 1 < 0;

    return {
        second: `1st Cutoff — ${dayLabel(year, monthIndex - 1, 26, crossesYear)} - ${dayLabel(year, monthIndex, 10, crossesYear)}`,
        first: `2nd Cutoff — ${dayLabel(year, monthIndex, 11, false)} - ${dayLabel(year, monthIndex, 25, false)}`,
    };
}

export function CutoffPicker({
    value,
    onChange,
    years,
    idPrefix = 'cutoff',
}: {
    value: CutoffValue;
    onChange: (value: CutoffValue) => void;
    years: number[];
    idPrefix?: string;
}) {
    const labels = cutoffLabels(value.cutoff_month, value.cutoff_year);

    return (
        <>
            <div className="grid gap-1.5">
                <Label htmlFor={`${idPrefix}-month`}>Month</Label>
                <Select
                    value={String(value.cutoff_month)}
                    onValueChange={(month) => onChange({ ...value, cutoff_month: Number(month) })}
                >
                    <SelectTrigger id={`${idPrefix}-month`} className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {MONTHS.map((name, index) => (
                            <SelectItem key={name} value={String(index + 1)}>
                                {name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
            <div className="grid gap-1.5">
                <Label htmlFor={`${idPrefix}-year`}>Year</Label>
                <Select value={String(value.cutoff_year)} onValueChange={(year) => onChange({ ...value, cutoff_year: Number(year) })}>
                    <SelectTrigger id={`${idPrefix}-year`} className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {years.map((year) => (
                            <SelectItem key={year} value={String(year)}>
                                {year}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
            <div className="grid min-w-0 gap-1.5">
                <Label htmlFor={`${idPrefix}-type`}>Cutoff</Label>
                <Select
                    value={value.cutoff_type}
                    onValueChange={(type) => onChange({ ...value, cutoff_type: type as CutoffValue['cutoff_type'] })}
                >
                    <SelectTrigger id={`${idPrefix}-type`} className="w-full min-w-0 overflow-hidden [&_[data-slot=select-value]]:truncate">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="second">{labels.second}</SelectItem>
                        <SelectItem value="first">{labels.first}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </>
    );
}
