import { router } from '@inertiajs/react';
import { Fingerprint, Search } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { SyncPanel, type SyncAccount } from '@/components/biometrics/sync-panel';
import { DataPagination } from '@/components/data-pagination';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface MonitorRow {
    employee_name: string;
    employee_no: string | null;
    date_label: string | null;
    remarks: string | null;
    shift_name: string | null;
    shift_mode: string | null;
    schedule_status: string | null;
    scheduled_time_in: string | null;
    scheduled_time_out: string | null;
    grace_minutes: number;
    paid_hours: string;
    day_off: string | null;
    actual_time_in: string | null;
    actual_time_out: string | null;
    worked_hours_label: string;
    required_hours_label: string;
    late_minutes: number;
    late_label: string;
    undertime_minutes: number;
    undertime_label: string;
    attendance_note: string;
    attendance_class: string;
}

interface Filters {
    q: string;
    cutoff_month: number;
    cutoff_year: number;
    cutoff_type: string;
}

interface Props {
    rows: Paginated<MonitorRow>;
    people: { value: string; label: string }[];
    filters: Filters;
    cutoffLabel: string;
    cutoffTypes: Record<string, string>;
    isSearch: boolean;
    syncAccounts: SyncAccount[];
    today: string;
    can: { sync: boolean };
    urls: { index: string; syncStart: string; syncStep: string; syncStatus: string };
}

const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const thisYear = new Date().getFullYear();
const YEARS = Array.from({ length: 6 }, (_, index) => thisYear - 2 + index);

const TONE: Record<string, string> = {
    success: 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400',
    warning: 'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-400',
    danger: 'border-red-300 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400',
    info: 'border-sky-300 bg-sky-50 text-sky-700 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-400',
    secondary: 'bg-muted text-muted-foreground',
};

const STATUS_BADGE: Record<string, { label: string; tone: string }> = {
    rest_day: { label: 'Rest Day', tone: 'warning' },
    leave: { label: 'Leave', tone: 'info' },
    holiday: { label: 'Holiday', tone: 'danger' },
};

export default function BiometricsSyncIndex({ rows, people, filters, cutoffLabel, cutoffTypes, isSearch, syncAccounts, today, can, urls }: Props) {
    const [values, setValues] = useState({ ...filters, cutoff_month: String(filters.cutoff_month), cutoff_year: String(filters.cutoff_year) });

    const search = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.index, values);
    };

    return (
        <AppLayout title="Biometrics Sync">
            <PageHeader title="Biometrics Sync" description="Pull CrossChex attendance logs and review cutoff attendance per employee." />

            <SyncPanel accounts={syncAccounts} canSync={can.sync} today={today} urls={urls} />

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-4 border-b py-4 2xl:flex-row 2xl:items-end 2xl:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Attendance monitoring summary</CardTitle>
                        <CardDescription>
                            Cutoff coverage: <span className="font-medium text-foreground">{cutoffLabel}</span>
                        </CardDescription>
                    </div>
                    <form onSubmit={search} className="grid gap-2 sm:grid-cols-2 lg:grid-cols-[16rem_9rem_6rem_12rem_auto] lg:items-end">
                        <div className="grid gap-1.5">
                            <Label htmlFor="monitor-q">Employee</Label>
                            <Input
                                id="monitor-q"
                                list="monitor-people"
                                required
                                placeholder="Search employee name / employee no..."
                                value={values.q}
                                onChange={(event) => setValues({ ...values, q: event.target.value })}
                            />
                            <datalist id="monitor-people">
                                {people.map((person, index) => (
                                    <option key={`${person.value}-${index}`} value={person.value}>
                                        {person.label}
                                    </option>
                                ))}
                            </datalist>
                        </div>
                        <SimpleSelect id="monitor-month" label="Month" value={values.cutoff_month} onChange={(cutoff_month) => setValues({ ...values, cutoff_month })}>
                            {MONTHS.map((name, index) => (
                                <SelectItem key={name} value={String(index + 1)}>
                                    {name}
                                </SelectItem>
                            ))}
                        </SimpleSelect>
                        <SimpleSelect id="monitor-year" label="Year" value={values.cutoff_year} onChange={(cutoff_year) => setValues({ ...values, cutoff_year })}>
                            {YEARS.map((year) => (
                                <SelectItem key={year} value={String(year)}>
                                    {year}
                                </SelectItem>
                            ))}
                        </SimpleSelect>
                        <SimpleSelect id="monitor-type" label="Cutoff" value={values.cutoff_type} onChange={(cutoff_type) => setValues({ ...values, cutoff_type })}>
                            {Object.entries(cutoffTypes).map(([key, label]) => (
                                <SelectItem key={key} value={key}>
                                    {label}
                                </SelectItem>
                            ))}
                        </SimpleSelect>
                        <Button type="submit" variant="outline">
                            <Search />
                            Search
                        </Button>
                    </form>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[1600px] text-xs">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-12 pl-6">#</TableHead>
                                <TableHead>Employee name</TableHead>
                                <TableHead>Employee no</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Shift</TableHead>
                                <TableHead>Plotted schedule</TableHead>
                                <TableHead>Day off</TableHead>
                                <TableHead>Time in</TableHead>
                                <TableHead>Time out</TableHead>
                                <TableHead>Clock span</TableHead>
                                <TableHead>Required clock</TableHead>
                                <TableHead>Late</TableHead>
                                <TableHead>Undertime</TableHead>
                                <TableHead className="pr-6">Attendance status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {rows.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={14} className="py-12">
                                        <div className="flex flex-col items-center gap-2 text-center">
                                            <Fingerprint className="size-8 text-muted-foreground" />
                                            <div className="font-semibold">{isSearch ? 'No records found' : 'Search employee first'}</div>
                                            <div className="text-muted-foreground">
                                                {isSearch
                                                    ? 'No biometrics logs or plotting schedule found for the selected employee and cutoff.'
                                                    : 'Please search an employee name or employee number to view cutoff attendance logs.'}
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {rows.data.map((row, index) => (
                                <TableRow key={`${row.employee_no}-${row.date_label}-${index}`}>
                                    <TableCell className="pl-6 text-muted-foreground">{(rows.from ?? 1) + index}</TableCell>
                                    <TableCell className="font-medium">
                                        {row.employee_name}
                                        {row.remarks && <div className="font-normal text-muted-foreground">{row.remarks}</div>}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">{row.employee_no || '—'}</TableCell>
                                    <TableCell className="text-muted-foreground">{row.date_label || '—'}</TableCell>
                                    <TableCell>
                                        {row.shift_name ? (
                                            <Badge variant="outline" className={row.shift_mode === 'Flexible' ? TONE.info : 'border-primary/30'}>
                                                {row.shift_mode === 'Flexible' ? 'Flexible Shift' : 'Regular Shift'}
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline" className="text-muted-foreground">
                                                No Shift
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <PlottedSchedule row={row} />
                                    </TableCell>
                                    <TableCell>
                                        {row.day_off ? (
                                            <Badge variant="outline" className={TONE.warning}>
                                                {row.day_off}
                                            </Badge>
                                        ) : (
                                            <span className="text-muted-foreground">None</span>
                                        )}
                                    </TableCell>
                                    <TableCell className="tabular-nums">{row.actual_time_in || '—'}</TableCell>
                                    <TableCell className="tabular-nums">{row.actual_time_out || '—'}</TableCell>
                                    <TableCell className="font-medium tabular-nums">{row.worked_hours_label}</TableCell>
                                    <TableCell className="tabular-nums">{row.required_hours_label}</TableCell>
                                    <TableCell>
                                        {row.late_minutes > 0 ? (
                                            <Badge variant="outline" className={TONE.warning}>
                                                {row.late_label}
                                            </Badge>
                                        ) : (
                                            '—'
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {row.undertime_minutes > 0 ? (
                                            <Badge variant="outline" className={TONE.danger}>
                                                {row.undertime_label}
                                            </Badge>
                                        ) : (
                                            '—'
                                        )}
                                    </TableCell>
                                    <TableCell className="pr-6">
                                        <div className={cn('rounded-md border px-2 py-1 whitespace-normal', TONE[row.attendance_class] ?? TONE.secondary)}>
                                            {row.attendance_note}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            {isSearch && <DataPagination paginator={rows} noun="row" />}
        </AppLayout>
    );
}

function PlottedSchedule({ row }: { row: MonitorRow }) {
    if (row.schedule_status === 'scheduled') {
        return row.shift_mode === 'Flexible' ? (
            <div>
                <div className="font-medium text-sky-700 dark:text-sky-400">Flexible - {row.required_hours_label} Clock Hours Required</div>
                <div className="text-muted-foreground">{row.paid_hours} paid hour(s) + lunch; no fixed Time In / Time Out</div>
            </div>
        ) : (
            <div>
                <div className="font-medium text-emerald-700 dark:text-emerald-400">
                    {row.scheduled_time_in || 'No Time In'} - {row.scheduled_time_out || 'No Time Out'}
                </div>
                <div className="text-muted-foreground">Grace: {row.grace_minutes} min</div>
            </div>
        );
    }

    const badge = row.schedule_status ? STATUS_BADGE[row.schedule_status] : undefined;

    return (
        <Badge variant="outline" className={badge ? TONE[badge.tone] : 'text-muted-foreground'}>
            {badge?.label ?? 'No Schedule'}
        </Badge>
    );
}

function SimpleSelect({ id, label, value, onChange, children }: { id: string; label: string; value: string; onChange: (value: string) => void; children: ReactNode }) {
    return (
        <div className="grid min-w-0 gap-1.5">
            <Label htmlFor={id}>{label}</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger id={id} className="w-full min-w-0 [&_[data-slot=select-value]]:truncate">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>{children}</SelectContent>
            </Select>
        </div>
    );
}
