import { router } from '@inertiajs/react';
import { CircleAlert, CircleCheck, Eye, Printer, RefreshCw, ShieldCheck, UserX } from 'lucide-react';
import { useState } from 'react';
import type { CutoffValue } from '@/components/cutoff-picker';
import { CutoffToolbar } from '@/components/data-table/cutoff-toolbar';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { DetailDialog, DetailGrid } from '@/components/modal/detail-dialog';
import { useModal } from '@/components/modal/modal-context';
import { StatCard } from '@/components/page-header';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface SummaryRow {
    id: number;
    work_date: string | null;
    weekday: string | null;
    employee_name: string;
    employee_no: string | null;
    biometric_employee_id: string | null;
    schedule: {
        kind: 'none' | 'flexible' | 'fixed' | 'other';
        shift_name: string | null;
        time_in: string | null;
        time_out: string | null;
        paid_hours: string;
        clock_hours: string;
        has_lunch: boolean;
        grace_minutes: number;
        status_label: string;
    };
    actual_in: { time: string; date: string } | null;
    actual_out: { time: string; date: string } | null;
    late_minutes: number;
    undertime_minutes: number;
    worked_minutes: number;
    status: string;
    status_label: string;
    needs_check: boolean;
    day: { kind: 'holiday' | 'rest_day' | 'leave' | 'regular'; name?: string; type?: string };
    adjustment: { type: string; remarks: string | null } | null;
    pay_label: string;
    payable_days: string;
    payable_hours: string;
    remarks: string | null;
}

interface Filters extends CutoffValue {
    search: string;
    status: string;
    day_type: string;
    group_name: string;
}

interface Props {
    summaries: Paginated<SummaryRow>;
    stats: Record<string, number | { employee_name?: string; employee_no?: string }[]>;
    filters: Filters;
    cutoffLabel: string;
    groupLabel: string;
    statusOptions: Record<string, string>;
    dayTypeOptions: Record<string, string>;
    payrollGroups: Record<string, string>;
    years: number[];
    can: { rebuild: boolean; export: boolean };
    urls: { index: string; rebuild: string; export: string };
}

const ALL = '__all';

const RULES: [string, string][] = [
    ['Biometrics', 'Earliest log is time in and latest log is time out. Missing or invalid out becomes review/half day.'],
    ['Adjustments', 'Approved manual time and paid leave can qualify attendance. Approved Offset credit can cover eligible late, undertime, partial-day, or absence shortage without creating separate cash pay.'],
    ['Holiday gate', 'Holiday without work is paid only when before and after dates are qualified by logs, leave, adjustment, or rest day.'],
    ['Rest day', 'Plotted rest day/day off is always 100% paid and still shown for payroll checking.'],
    ['No schedule', 'Biometrics with no plotted schedule is not silently paid. It appears as No Schedule.'],
    ['Regular shift', 'Late and undertime are based on plotted in/out and grace minutes.'],
    ['Flexible shift', "Flexible shift follows each employee's selected workday clock hours (paid hours plus lunch when the workday has one)."],
    ['Holiday pay units', 'Regular holiday worked = 2.00 units. Special/non-regular worked = 1.30 units.'],
];

export default definePage<Props>({
    title: () => 'Payroll Attendance Summary',
    description: () =>
        'Final checking before payroll: plotted schedule, biometrics, adjustments, holidays, rest days, leaves, late, undertime and payable units. Review warnings before exporting.',
    actions: ({ filters, cutoffLabel, can, urls }) => (
        <>
            {can.rebuild && <RebuildButton filters={filters} url={urls.rebuild} cutoffLabel={cutoffLabel} />}
            {can.export && (
                <Button variant="outline" asChild>
                    <a href={urls.export} target="_blank" rel="noreferrer">
                        <Printer />
                        Export Payroll Print
                    </a>
                </Button>
            )}
        </>
    ),
    Content: AttendanceSummaryIndex,
});

const options = (record: Record<string, string>) => Object.entries(record).filter(([value]) => value !== '').map(([value, label]) => ({ value, label }));

function AttendanceSummaryIndex(props: Props) {
    const { summaries, stats, filters, cutoffLabel, groupLabel, statusOptions, dayTypeOptions, payrollGroups, years, urls } = props;
    const modal = useModal();
    const [viewing, setViewing] = useState<SummaryRow | null>(null);
    const num = (key: string) => Number(stats[key] ?? 0);
    const missingList = (stats.missing_summary_employee_list as { employee_name?: string; employee_no?: string }[]) ?? [];
    const needsReview = num('needs_review');
    const missing = num('missing_summary_employees');
    const cutoff = { cutoff_month: filters.cutoff_month, cutoff_year: filters.cutoff_year, cutoff_type: filters.cutoff_type };

    const columns: DataTableColumn<SummaryRow>[] = [
        {
            key: 'date',
            header: 'Date',
            className: 'whitespace-nowrap',
            cell: (row) => (
                <>
                    <div className="font-medium">{row.work_date ?? '—'}</div>
                    <div className="text-xs text-muted-foreground">{row.weekday ?? '—'}</div>
                </>
            ),
        },
        {
            key: 'employee',
            header: 'Employee',
            cell: (row) => (
                <>
                    <div className="max-w-52 truncate font-medium" title={row.employee_name}>
                        {row.employee_name}
                    </div>
                    <div className="text-xs whitespace-nowrap text-muted-foreground">
                        {row.employee_no || 'No Emp No'} · Bio {row.biometric_employee_id || '—'}
                    </div>
                </>
            ),
        },
        { key: 'schedule', header: 'Schedule', hideBelow: 'xl', cell: (row) => <ScheduleCell row={row} /> },
        {
            key: 'logs',
            header: 'Biometrics',
            hideBelow: 'lg',
            className: 'text-xs whitespace-nowrap tabular-nums',
            cell: (row) => (
                <>
                    <div>
                        <span className="text-muted-foreground">In </span>
                        {row.actual_in ? <span className="font-medium">{row.actual_in.time}</span> : '—'}
                    </div>
                    <div>
                        <span className="text-muted-foreground">Out </span>
                        {row.actual_out ? <span className="font-medium">{row.actual_out.time}</span> : '—'}
                    </div>
                </>
            ),
        },
        {
            key: 'time',
            header: 'Late / UT / Worked',
            hideBelow: 'md',
            className: 'text-xs whitespace-nowrap tabular-nums',
            cell: (row) => (
                <>
                    <span className={cn(row.late_minutes > 0 && 'font-medium text-amber-700 dark:text-amber-400')}>{row.late_minutes}m</span>
                    <span className="text-muted-foreground"> / </span>
                    <span className={cn(row.undertime_minutes > 0 && 'font-medium text-amber-700 dark:text-amber-400')}>{row.undertime_minutes}m</span>
                    <span className="text-muted-foreground"> / </span>
                    <span>{(row.worked_minutes / 60).toFixed(2)}h</span>
                </>
            ),
        },
        {
            key: 'status',
            header: 'Payroll status',
            cell: (row) => (
                <div className="grid justify-items-start gap-1">
                    <Badge variant="outline" className={STATUS_TONE[row.status]}>
                        {row.status_label}
                    </Badge>
                    {row.needs_check && <span className="text-xs text-destructive">Check before payroll</span>}
                </div>
            ),
            filter: { type: 'select', param: 'status', options: options(statusOptions), placeholder: 'All statuses' },
        },
        {
            key: 'day',
            header: 'Day',
            cell: (row) => <DayCell row={row} />,
            filter: { type: 'select', param: 'day_type', options: options(dayTypeOptions), placeholder: 'All day types' },
        },
        {
            key: 'pay',
            header: 'Pay units',
            className: 'whitespace-nowrap',
            cell: (row) => (
                <>
                    <Badge variant={Number(row.payable_days) > 0 ? 'secondary' : 'outline'} className={cn(Number(row.payable_days) <= 0 && 'text-destructive')}>
                        {row.pay_label}
                    </Badge>
                    <div className="mt-1 text-xs text-muted-foreground tabular-nums">
                        {row.payable_days} unit · {row.payable_hours} hr
                    </div>
                </>
            ),
        },
    ];

    const setGroup = (value: string) => modal.get(urls.index, { ...filters, group_name: value === ALL ? '' : value });

    return (
        <>
            <div className="flex flex-wrap gap-2">
                <Badge variant="secondary">{cutoffLabel}</Badge>
                <Badge variant="secondary">{groupLabel}</Badge>
                <Badge variant="secondary">{num('eligible_employees').toLocaleString()} payroll eligible</Badge>
                <Badge variant="secondary">{num('total_payable_days').toFixed(2)} pay unit(s)</Badge>
                {needsReview > 0 && (
                    <Badge variant="outline" className="border-amber-300 text-amber-700 dark:text-amber-400">
                        {needsReview.toLocaleString()} need review
                    </Badge>
                )}
            </div>

            <Alert variant={missing > 0 ? 'destructive' : 'default'}>
                {missing > 0 ? <UserX /> : <CircleCheck />}
                <AlertTitle className="flex flex-wrap items-center gap-2">
                    Payroll roster coverage — {groupLabel}
                    <Badge variant={missing > 0 ? 'destructive' : 'secondary'}>{missing > 0 ? 'Rebuild required' : 'Roster covered'}</Badge>
                </AlertTitle>
                <AlertDescription>
                    <p>
                        Eligible: <strong>{num('eligible_employees').toLocaleString()}</strong> · With attendance summary:{' '}
                        <strong>{num('summary_employees').toLocaleString()}</strong> · Missing summary: <strong>{missing.toLocaleString()}</strong>. Eligibility is
                        based only on Active status, Payroll Inclusion ON, and payroll group.
                    </p>
                    {missingList.length > 0 && (
                        <p>
                            <strong>Missing:</strong>{' '}
                            {missingList
                                .slice(0, 10)
                                .map((employee) => `${employee.employee_name ?? 'Unknown'} [${employee.employee_no ?? 'No Emp No'}]`)
                                .join(', ')}
                            {missingList.length > 10 && ` and ${missingList.length - 10} more`}
                        </p>
                    )}
                </AlertDescription>
            </Alert>

            {needsReview > 0 && (
                <Alert>
                    <CircleAlert />
                    <AlertTitle>Payroll checking required before release</AlertTitle>
                    <AlertDescription>
                        Found {needsReview.toLocaleString()} record(s) needing review: {num('absent')} absent, {num('half_day')} half day, {num('incomplete')} incomplete
                        log, {num('no_schedule')} no plotted schedule, and {num('holiday_unpaid')} unpaid holiday.
                    </AlertDescription>
                </Alert>
            )}

            <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <StatCard label="Payable records" value={num('payable_records').toLocaleString()} hint={`${num('present')} present`} />
                <StatCard label="Needs review" value={needsReview.toLocaleString()} hint={`${num('absent')} absent · ${num('incomplete')} incomplete`} />
                <StatCard label="Late / undertime" value={num('late_undertime_records').toLocaleString()} hint={`${num('total_late_minutes')} late min · ${num('total_undertime_minutes')} UT min`} />
                <StatCard label="Holidays" value={num('holiday').toLocaleString()} hint={`${num('holiday_paid')} paid · ${num('holiday_worked')} worked`} />
                <StatCard label="Rest days / leave" value={(num('rest_day') + num('leave')).toLocaleString()} hint={`${num('rest_day')} rest · ${num('leave')} leave`} />
                <StatCard label="Payable hours" value={num('total_payable_hours').toFixed(2)} hint={`${num('total_payable_days').toFixed(2)} pay units`} />
            </div>

            <RulesCard />

            <DataTable
                title="Daily payroll attendance records"
                noun="record"
                paginator={summaries}
                url={urls.index}
                filters={{ search: filters.search, status: filters.status, day_type: filters.day_type }}
                keepParams={{ ...Object.fromEntries(Object.entries(cutoff).map(([key, value]) => [key, String(value)])), group_name: filters.group_name }}
                columns={columns}
                rowKey={(row) => row.id}
                searchPlaceholder="Search employee name, no. or biometric ID..."
                emptyText="No attendance summary records. Try another cutoff, rebuild the summary, or check that schedules are plotted."
                minWidth={880}
                rowClassName={(row) => cn(row.needs_check && 'bg-amber-50/60 dark:bg-amber-950/20')}
                onRowClick={setViewing}
                toolbar={
                    <>
                        <CutoffToolbar url={urls.index} value={cutoff} params={filters} years={years} />
                        <Select value={filters.group_name || ALL} onValueChange={setGroup}>
                            <SelectTrigger size="sm" className="w-52" aria-label="Payroll group">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={ALL}>All payroll groups</SelectItem>
                                {Object.entries(payrollGroups).map(([value, label]) => (
                                    <SelectItem key={value} value={value}>
                                        {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </>
                }
                rowActions={(row) => (
                    <Button
                        variant="ghost"
                        size="icon"
                        className="size-8"
                        aria-label={`View ${row.employee_name} ${row.work_date ?? ''}`}
                        onClick={(event) => {
                            event.stopPropagation();
                            setViewing(row);
                        }}
                    >
                        <Eye />
                    </Button>
                )}
            />

            {viewing && <SummaryDialog row={viewing} onClose={() => setViewing(null)} />}
        </>
    );
}

function ScheduleCell({ row }: { row: SummaryRow }) {
    const s = row.schedule;

    if (s.kind === 'none') {
        return (
            <Badge variant="outline" className="border-destructive/40 text-destructive">
                No plotted schedule
            </Badge>
        );
    }

    if (s.kind === 'flexible') {
        return (
            <div className="text-xs">
                <div className="font-medium">{s.shift_name || 'Flexible Shift'}</div>
                <div className="text-muted-foreground">{s.clock_hours} clock hr</div>
            </div>
        );
    }

    if (s.kind === 'fixed') {
        return (
            <div className="text-xs whitespace-nowrap">
                <div className="font-medium tabular-nums">
                    {s.time_in ?? '—'} – {s.time_out ?? '—'}
                </div>
                <div className="text-muted-foreground">{s.shift_name || 'Regular Shift'}</div>
            </div>
        );
    }

    return <span className="text-xs text-muted-foreground">{s.shift_name || '—'}</span>;
}

function DayCell({ row }: { row: SummaryRow }) {
    return (
        <div className="grid justify-items-start gap-1 text-xs">
            {row.day.kind === 'holiday' && <Badge variant="secondary">Holiday</Badge>}
            {row.day.kind === 'rest_day' && <Badge variant="secondary">Rest day</Badge>}
            {row.day.kind === 'leave' && <Badge variant="secondary">Leave</Badge>}
            {row.day.kind === 'regular' && <span className="text-muted-foreground">Regular</span>}
            {row.adjustment && <Badge variant="outline">Adjusted</Badge>}
        </div>
    );
}

function SummaryDialog({ row, onClose }: { row: SummaryRow; onClose: () => void }) {
    const s = row.schedule;
    const schedule =
        s.kind === 'none'
            ? 'No plotted schedule — fix plotting before payroll'
            : s.kind === 'flexible'
              ? `${s.shift_name || 'Flexible Shift'} · ${s.clock_hours} clock hours (${s.paid_hours} paid${s.has_lunch ? ' + lunch' : ''})`
              : s.kind === 'fixed'
                ? `${s.time_in ?? '—'} to ${s.time_out ?? '—'} · ${s.shift_name || 'Regular Shift'} · ${s.paid_hours} paid hr(s)${s.has_lunch ? ' + lunch' : ''} · Grace ${s.grace_minutes} min`
                : s.shift_name || '—';
    const day =
        row.day.kind === 'holiday'
            ? `Holiday: ${row.day.name ?? ''} (${row.day.type ?? ''})`
            : row.day.kind === 'rest_day'
              ? 'Rest day / day off (100% paid)'
              : row.day.kind === 'leave'
                ? 'Leave'
                : 'Regular day';

    return (
        <DetailDialog
            open
            onOpenChange={(open) => !open && onClose()}
            size="md"
            title={row.employee_name}
            description={`${row.work_date ?? '—'} (${row.weekday ?? '—'}) · Emp No ${row.employee_no || '—'} · Bio ID ${row.biometric_employee_id || '—'}`}
            actions={
                <Badge variant="outline" className={STATUS_TONE[row.status]}>
                    {row.status_label}
                </Badge>
            }
        >
            <div className="grid gap-4">
                {row.needs_check && (
                    <Alert>
                        <CircleAlert />
                        <AlertDescription>This record must be checked before payroll.</AlertDescription>
                    </Alert>
                )}
                <DetailGrid
                    items={[
                        { label: 'Plotted schedule', value: schedule, wide: true },
                        { label: 'Schedule status', value: s.status_label },
                        { label: 'Day', value: day },
                        { label: 'Time in', value: row.actual_in ? `${row.actual_in.time} (${row.actual_in.date})` : '—' },
                        { label: 'Time out', value: row.actual_out ? `${row.actual_out.time} (${row.actual_out.date})` : '—' },
                        { label: 'Late', value: `${row.late_minutes} min` },
                        { label: 'Undertime', value: `${row.undertime_minutes} min` },
                        { label: 'Worked', value: `${row.worked_minutes} min (${(row.worked_minutes / 60).toFixed(2)} hr)` },
                        { label: 'Pay', value: `${row.pay_label} · ${row.payable_days} unit(s) · ${row.payable_hours} hr` },
                        { label: 'Adjustment', value: row.adjustment ? `${row.adjustment.type}${row.adjustment.remarks ? ` — ${row.adjustment.remarks}` : ''}` : 'None', wide: true },
                        { label: 'Audit remarks', value: row.remarks ?? 'No remarks', wide: true },
                    ]}
                />
            </div>
        </DetailDialog>
    );
}

function RebuildButton({ filters, url, cutoffLabel }: { filters: Filters; url: string; cutoffLabel: string }) {
    const [processing, setProcessing] = useState(false);

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>
                <Button disabled={processing}>
                    <RefreshCw className={cn(processing && 'animate-spin')} />
                    {processing ? 'Rebuilding…' : 'Rebuild current cutoff'}
                </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Rebuild attendance summary?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Rebuilds {cutoffLabel} for all Active payroll-included employees. Do this after changing schedules, biometrics,
                        adjustments, holidays, or leaves. It can take a minute.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={() =>
                            router.post(url, { ...filters }, {
                                preserveScroll: true,
                                onStart: () => setProcessing(true),
                                onFinish: () => setProcessing(false),
                            })
                        }
                    >
                        Rebuild
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

function RulesCard() {
    return (
        <Collapsible>
            <Card className="py-4">
                <CardHeader className="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle className="flex items-center gap-2">
                            <ShieldCheck className="size-4" />
                            Payroll rules applied
                        </CardTitle>
                        <CardDescription>The critical rules used by the summary builder (schedule-first payroll validation).</CardDescription>
                    </div>
                    <CollapsibleTrigger asChild>
                        <Button variant="ghost" size="sm">
                            Show / hide
                        </Button>
                    </CollapsibleTrigger>
                </CardHeader>
                <CollapsibleContent>
                    <CardContent className="grid gap-3 pt-2 sm:grid-cols-2 xl:grid-cols-4">
                        {RULES.map(([title, text]) => (
                            <div key={title} className="rounded-lg border p-3">
                                <div className="text-sm font-medium">{title}</div>
                                <p className="mt-1 text-xs text-muted-foreground">{text}</p>
                            </div>
                        ))}
                    </CardContent>
                </CollapsibleContent>
            </Card>
        </Collapsible>
    );
}

const STATUS_TONE: Record<string, string> = {
    present: 'text-emerald-700 border-emerald-300 dark:text-emerald-400 dark:border-emerald-800',
    adjusted_present: 'text-emerald-700 border-emerald-300 dark:text-emerald-400 dark:border-emerald-800',
    late: 'text-amber-700 border-amber-300 dark:text-amber-400 dark:border-amber-800',
    undertime: 'text-amber-700 border-amber-300 dark:text-amber-400 dark:border-amber-800',
    late_undertime: 'text-amber-700 border-amber-300 dark:text-amber-400 dark:border-amber-800',
    half_day: 'text-amber-700 border-amber-300 dark:text-amber-400 dark:border-amber-800',
    absent: 'text-destructive border-destructive/40',
    incomplete_log: 'text-destructive border-destructive/40',
    holiday_unpaid: 'text-destructive border-destructive/40',
    no_schedule: 'text-destructive border-destructive/40',
    holiday: 'text-sky-700 border-sky-300 dark:text-sky-400 dark:border-sky-800',
    holiday_worked: 'text-sky-700 border-sky-300 dark:text-sky-400 dark:border-sky-800',
};
