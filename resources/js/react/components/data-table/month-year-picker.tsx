import { CalendarDays } from 'lucide-react';
import { useModal } from '@/components/modal/modal-context';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

/**
 * Period switcher for a DataTable toolbar (month + year). Changing it reloads
 * the page (or the modal) with the current filters kept.
 *
 *   toolbar={<MonthYearPicker url={urls.index} month={filters.month} year={filters.year} params={filters} />}
 */
export function MonthYearPicker({
    url,
    month,
    year,
    params = {},
    years,
}: {
    url: string;
    month: number | string;
    year: number | string;
    /** Other query params to keep (search, group, ...). */
    params?: object;
    /** Selectable years; defaults to the last 6 years and next year. */
    years?: number[];
}) {
    const modal = useModal();
    const thisYear = new Date().getFullYear();
    const yearList = years ?? Array.from({ length: 7 }, (_, index) => thisYear + 1 - index);
    const keep = params as Record<string, string | number | undefined>;

    const go = (next: { month?: string; year?: string }) => modal.get(url, { ...keep, month: String(month), year: String(year), ...next });

    return (
        <div className="flex items-center gap-2">
            <CalendarDays className="size-4 text-muted-foreground" aria-hidden />
            <Select value={String(month)} onValueChange={(value) => go({ month: value })}>
                <SelectTrigger size="sm" className="w-32" aria-label="Month">
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
            <Select value={String(year)} onValueChange={(value) => go({ year: value })}>
                <SelectTrigger size="sm" className="w-24" aria-label="Year">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {[...new Set([Number(year), ...yearList])]
                        .sort((a, b) => b - a)
                        .map((value) => (
                            <SelectItem key={value} value={String(value)}>
                                {value}
                            </SelectItem>
                        ))}
                </SelectContent>
            </Select>
        </div>
    );
}
