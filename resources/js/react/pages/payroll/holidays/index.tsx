import { router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { MonthYearPicker } from '@/components/data-table/month-year-picker';
import { ModalLink } from '@/components/modal/modal-link';
import { useModal } from '@/components/modal/modal-context';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface HolidayRow {
    id: number;
    name: string;
    type: 'regular' | 'special';
    actual_date: string;
    observed_date: string;
    is_moved: boolean;
    is_active: boolean;
    not_worked_multiplier: number;
    worked_multiplier: number;
    source: string | null;
    urls: { edit: string; destroy: string };
}

interface CalendarEntry {
    name: string;
    type: 'regular' | 'special';
    moved_from: string | null;
}

interface Props {
    holidays: Paginated<HolidayRow>;
    calendar: Record<string, CalendarEntry[]>;
    filters: { year: number; month: number; search: string; type: string };
    can: { create: boolean; update: boolean; delete: boolean };
    urls: { index: string; create: string };
}

const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const TYPE_CLASS: Record<string, string> = {
    regular: 'border-destructive/40 bg-destructive/10 text-destructive',
    special: 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
};

const TYPE_OPTIONS = [
    { value: 'regular', label: 'Regular' },
    { value: 'special', label: 'Special' },
];

export default definePage<Props>({
    title: () => 'Philippine Holiday Calendar',
    description: () =>
        'Connected to Payroll and Attendance Summary. Regular holidays pay 100% not worked / 200% worked; special non-working pay 0% / 130% unless customised.',
    actions: ({ can, urls }) =>
        can.create && (
            <Button asChild>
                <ModalLink href={urls.create} mode="form">
                    <Plus />
                    Add holiday
                </ModalLink>
            </Button>
        ),
    size: 'xl',
    Content: HolidaysIndex,
});

function HolidaysIndex({ holidays, calendar, filters, can, urls }: Props) {
    const modal = useModal();

    const columns: DataTableColumn<HolidayRow>[] = [
        {
            key: 'name',
            header: 'Holiday',
            className: 'min-w-40 whitespace-normal',
            cell: (holiday) => (
                <>
                    <div className="font-medium">{holiday.name}</div>
                    {holiday.is_moved && <div className="text-xs text-muted-foreground">Moved holiday observance</div>}
                    {!holiday.is_active && <div className="text-xs text-muted-foreground">Inactive</div>}
                </>
            ),
        },
        {
            key: 'type',
            header: 'Type',
            cell: (holiday) => (
                <Badge variant="outline" className={TYPE_CLASS[holiday.type]}>
                    {holiday.type === 'regular' ? 'Regular' : 'Special'}
                </Badge>
            ),
            filter: { type: 'select', param: 'type', options: TYPE_OPTIONS, placeholder: 'All types' },
        },
        {
            key: 'dates',
            header: 'Observed / actual',
            className: 'text-sm sm:whitespace-nowrap',
            cell: (holiday) => (
                <>
                    <div className="font-medium">{holiday.observed_date}</div>
                    {holiday.actual_date !== holiday.observed_date && <div className="text-xs text-muted-foreground">Actual: {holiday.actual_date}</div>}
                </>
            ),
        },
        {
            key: 'pay',
            header: 'Not worked / worked',
            hideBelow: 'sm',
            align: 'right',
            className: 'tabular-nums whitespace-nowrap',
            cell: (holiday) => (
                <>
                    {holiday.not_worked_multiplier.toFixed(2)}x <span className="text-muted-foreground">/</span> {holiday.worked_multiplier.toFixed(2)}x
                </>
            ),
        },
        {
            key: 'source',
            header: 'Source',
            hideBelow: 'lg',
            className: 'max-w-64 text-sm text-muted-foreground',
            cell: (holiday) => holiday.source || '—',
        },
    ];

    return (
        <>
            <MonthGrid year={filters.year} month={filters.month} calendar={calendar} />

            <DataTable
                title="Holidays"
                noun="holiday"
                paginator={holidays}
                url={urls.index}
                filters={{ search: filters.search, type: filters.type }}
                keepParams={{ year: String(filters.year), month: String(filters.month) }}
                toolbar={<MonthYearPicker url={urls.index} month={filters.month} year={filters.year} params={{ search: filters.search, type: filters.type }} />}
                columns={columns}
                rowKey={(holiday) => holiday.id}
                rowClassName={(holiday) => (holiday.is_active ? undefined : 'opacity-60')}
                searchPlaceholder="Search holiday, type or proclamation..."
                emptyText="No holidays for this period."
                minWidth={420}
                rowActions={
                    can.update || can.delete
                        ? (holiday) => (
                              <>
                                  {can.update && (
                                      <Tooltip>
                                          <TooltipTrigger asChild>
                                              <Button variant="ghost" size="icon" className="size-8" asChild>
                                                  <ModalLink href={holiday.urls.edit} mode="form" aria-label={`Edit ${holiday.name}`}>
                                                      <Pencil />
                                                  </ModalLink>
                                              </Button>
                                          </TooltipTrigger>
                                          <TooltipContent>Edit holiday</TooltipContent>
                                      </Tooltip>
                                  )}
                                  {can.delete && (
                                      <ConfirmAction
                                          title="Delete holiday?"
                                          description={`Delete ${holiday.name}? Rebuild Attendance Summary afterwards so payroll stops treating the date as a holiday.`}
                                          confirmLabel="Delete"
                                          destructive
                                          onConfirm={() => router.delete(holiday.urls.destroy, modal.visit({ preserveScroll: true, preserveState: true }))}
                                          trigger={
                                              <IconButton label={`Delete ${holiday.name}`}>
                                                  <Trash2 className="text-destructive" />
                                              </IconButton>
                                          }
                                      />
                                  )}
                              </>
                          )
                        : undefined
                }
            />
        </>
    );
}

function MonthGrid({ year, month, calendar }: { year: number; month: number; calendar: Record<string, CalendarEntry[]> }) {
    const first = new Date(year, month - 1, 1);
    const daysInMonth = new Date(year, month, 0).getDate();
    const cells: (number | null)[] = [...Array(first.getDay()).fill(null), ...Array.from({ length: daysInMonth }, (_, i) => i + 1)];
    while (cells.length % 7 !== 0) cells.push(null);

    const key = (day: number) => `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    return (
        <Card className="gap-4">
            <CardHeader className="flex flex-wrap items-center justify-between gap-2">
                <CardTitle>
                    {MONTHS[month - 1]} {year}
                </CardTitle>
                <div className="flex gap-2 text-xs">
                    <Badge variant="outline" className={TYPE_CLASS.regular}>
                        Regular
                    </Badge>
                    <Badge variant="outline" className={TYPE_CLASS.special}>
                        Special
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-7 overflow-hidden rounded-lg border text-sm">
                    {WEEKDAYS.map((day) => (
                        <div key={day} className="border-b bg-muted/50 px-1 py-1.5 text-center text-xs font-medium text-muted-foreground">
                            {day}
                        </div>
                    ))}
                    {cells.map((day, index) => {
                        const entries = day ? (calendar[key(day)] ?? []) : [];

                        return (
                            <div key={index} className={cn('min-h-16 min-w-0 border-r border-b p-1 sm:min-h-20 sm:p-1.5 [&:nth-child(7n)]:border-r-0', !day && 'bg-muted/20')}>
                                {day && <div className="text-xs text-muted-foreground">{String(day).padStart(2, '0')}</div>}
                                {entries.map((entry) => (
                                    <div key={entry.name} className={cn('mt-1 rounded border px-1 py-0.5 text-[10px] sm:px-1.5 sm:text-xs', TYPE_CLASS[entry.type])}>
                                        <div className="truncate font-medium" title={entry.name}>
                                            {entry.name}
                                        </div>
                                        {entry.moved_from && <div className="hidden text-[10px] opacity-80 sm:block">Actual: {entry.moved_from}</div>}
                                    </div>
                                ))}
                            </div>
                        );
                    })}
                </div>
            </CardContent>
        </Card>
    );
}
