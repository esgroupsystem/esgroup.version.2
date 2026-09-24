import { CalendarRange } from 'lucide-react';
import { cutoffLabels, type CutoffValue } from '@/components/cutoff-picker';
import { useModal } from '@/components/modal/modal-context';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

/**
 * Compact payroll cutoff switcher for a DataTable toolbar (month, year, 1st/2nd cutoff).
 * Changing it reloads the page (or modal) and keeps the other filters.
 *
 *   toolbar={<CutoffToolbar url={urls.index} value={filters} params={filters} years={years} />}
 */
export function CutoffToolbar({ url, value, params = {}, years }: { url: string; value: CutoffValue; params?: object; years: number[] }) {
    const modal = useModal();
    const labels = cutoffLabels(value.cutoff_month, value.cutoff_year);

    const go = (next: Partial<CutoffValue>) => {
        const merged = { ...value, ...next };
        modal.get(url, {
            ...(params as Record<string, string | number | undefined>),
            cutoff_month: String(merged.cutoff_month),
            cutoff_year: String(merged.cutoff_year),
            cutoff_type: merged.cutoff_type,
        });
    };

    return (
        <div className="flex flex-wrap items-center gap-2">
            <CalendarRange className="size-4 text-muted-foreground" aria-hidden />
            <Select value={String(value.cutoff_month)} onValueChange={(month) => go({ cutoff_month: Number(month) })}>
                <SelectTrigger size="sm" className="w-24" aria-label="Cutoff month">
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
            <Select value={String(value.cutoff_year)} onValueChange={(year) => go({ cutoff_year: Number(year) })}>
                <SelectTrigger size="sm" className="w-24" aria-label="Cutoff year">
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
            <Select value={value.cutoff_type} onValueChange={(type) => go({ cutoff_type: type as CutoffValue['cutoff_type'] })}>
                <SelectTrigger size="sm" className="w-72 max-w-full" aria-label="Cutoff">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="second">{labels.second}</SelectItem>
                    <SelectItem value="first">{labels.first}</SelectItem>
                </SelectContent>
            </Select>
        </div>
    );
}
