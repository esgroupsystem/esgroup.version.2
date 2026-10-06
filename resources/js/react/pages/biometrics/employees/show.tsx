import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CalendarClock, CircleCheck, CircleDashed, Link2, ListChecks, LoaderCircle, Save, TriangleAlert, Wallet } from 'lucide-react';
import { useMemo, useState } from 'react';
import { DetailGrid } from '@/components/modal/detail-dialog';
import { useModal } from '@/components/modal/modal-context';
import { openModal } from '@/components/modal/modal-store';
import { FormField } from '@/components/form-field';
import {
    addMinutes,
    DayOffPicker,
    RowSelect,
    SHIFT_OPTIONS,
    STATUS_OPTIONS,
    type ScheduleShift,
    type ScheduleStatus,
    type WorkdayRule,
} from '@/components/scheduling/schedule-fields';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { definePage } from '@/lib/define-page';
import { initials, peso } from '@/lib/format';
import { cn } from '@/lib/utils';
import { BiometricEmployeeEdit, type BiometricEmployeeEditProps } from './edit';
import { EmployeeSalaryForm, type EmployeeSalaryFormProps } from '../../payroll/employee-salaries/form';

type TabKey = 'details' | 'schedule' | 'rates';

interface EmployeeSummary {
    id: number;
    display_name: string;
    display_no: string;
    source_no: string;
    group_label: string | null;
    company: string | null;
    active: boolean;
    payroll_included: boolean;
    device_name: string;
    last_check: string | null;
    total_logs: number;
    remarks: string | null;
    hr_employee: { name: string; url: string } | null;
    schedule: { label: string; hours: string | null } | null;
    rate: { visible: boolean; label: string | null };
}

interface ScheduleTab {
    row: {
        employee_biometric_id: number;
        schedule: {
            status: ScheduleStatus;
            shift_name: ScheduleShift;
            workday_type: string;
            time_in: string | null;
            time_out: string | null;
            grace_minutes: number | string;
            day_offs: string[];
            remarks: string;
            is_saved: boolean;
        };
    };
    workdayRules: Record<string, WorkdayRule>;
    weekdays: string[];
    canUpdate: boolean;
    payrollActive: boolean;
    urls: { save: string };
}

interface RatesTab {
    mode: 'edit' | 'create' | 'view' | 'unavailable';
    message?: string;
    summary?: { rate_type: string; basic_salary: number; allowance: number; paid_day_off: boolean; is_active: boolean } | null;
    form?: EmployeeSalaryFormProps | null;
}

interface Props {
    employee: EmployeeSummary;
    checklist: { key: TabKey; label: string; done: boolean }[];
    details: Omit<BiometricEmployeeEditProps, 'returnProfile'> | null;
    schedule: ScheduleTab | null;
    rates: RatesTab | null;
    urls: { index: string; bulkSchedule: string | null; ratesList: string | null };
}

const ACTIVE_CLASS = 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400';
const MISSING_CLASS = 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300';

export default definePage<Props>({
    title: ({ employee }) => employee.display_name,
    description: () => 'Details, work schedule and rates of this employee in one place. Each tab saves on its own.',
    actions: (props) => <Actions {...props} />,
    size: 'xl',
    Content: EmployeeProfile,
});

function Actions({ urls }: Props) {
    const modal = useModal();

    return (
        <>
            {urls.bulkSchedule && (
                <Button variant="outline" size="sm" asChild>
                    <Link href={urls.bulkSchedule}>
                        <CalendarClock />
                        Bulk schedule
                    </Link>
                </Button>
            )}
            {!modal.inModal && (
                <Button variant="outline" size="sm" asChild>
                    <Link href={urls.index}>
                        <ArrowLeft />
                        Back
                    </Link>
                </Button>
            )}
        </>
    );
}

function EmployeeProfile(props: Props) {
    const { employee, checklist, details, schedule, rates } = props;
    const [tab, setTab] = useState<TabKey>('details');
    const done = checklist.filter((item) => item.done).length;
    const missing = checklist.filter((item) => !item.done);
    const missingIn = (key: TabKey) => missing.filter((item) => item.key === key).length;
    const percent = checklist.length ? Math.round((done / checklist.length) * 100) : 100;

    return (
        <div className="grid min-w-0 gap-4">
            <Card className="gap-4">
                <CardContent className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,20rem)]">
                    <div className="flex min-w-0 items-start gap-4">
                        <Avatar className="size-14 rounded-xl">
                            <AvatarFallback className="rounded-xl text-lg">{initials(employee.display_name)}</AvatarFallback>
                        </Avatar>
                        <div className="grid min-w-0 gap-1.5">
                            <div className="text-lg leading-tight font-semibold break-words">{employee.display_name}</div>
                            <div className="text-sm text-muted-foreground">
                                No. {employee.display_no}
                                {employee.source_no !== employee.display_no && ` · Source ${employee.source_no}`}
                            </div>
                            <div className="flex flex-wrap gap-1.5">
                                <Badge variant="outline" className={employee.active ? ACTIVE_CLASS : 'text-muted-foreground'}>
                                    {employee.active ? 'Active' : 'Inactive'}
                                </Badge>
                                <Badge variant="outline" className={employee.payroll_included ? ACTIVE_CLASS : 'text-muted-foreground'}>
                                    Payroll {employee.payroll_included ? 'included' : 'excluded'}
                                </Badge>
                                {employee.group_label && <Badge variant="secondary">{employee.group_label}</Badge>}
                                {employee.company && <Badge variant="secondary">{employee.company}</Badge>}
                            </div>
                            {employee.hr_employee && (
                                <button
                                    type="button"
                                    className="inline-flex w-fit items-center gap-1 text-xs text-primary hover:underline"
                                    onClick={() => openModal(employee.hr_employee!.url, { size: 'xl' })}
                                >
                                    <Link2 className="size-3" />
                                    HR profile: {employee.hr_employee.name}
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="grid content-start gap-2">
                        <div className="flex items-center justify-between text-sm">
                            <span className="font-medium">Profile completeness</span>
                            <span className="tabular-nums text-muted-foreground">
                                {done} / {checklist.length}
                            </span>
                        </div>
                        <div className="h-2 overflow-hidden rounded-full bg-muted">
                            <div className={cn('h-full rounded-full', percent === 100 ? 'bg-emerald-500' : 'bg-amber-500')} style={{ width: `${percent}%` }} />
                        </div>
                        {missing.length === 0 ? (
                            <p className="flex items-center gap-1.5 text-xs text-emerald-700 dark:text-emerald-400">
                                <CircleCheck className="size-3.5" /> Everything payroll needs is set.
                            </p>
                        ) : (
                            <div className="flex flex-wrap gap-1.5">
                                {missing.map((item) => (
                                    <button
                                        key={item.label}
                                        type="button"
                                        onClick={() => setTab(item.key)}
                                        className={cn('inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs hover:opacity-80', MISSING_CLASS)}
                                    >
                                        <CircleDashed className="size-3" />
                                        {item.label}
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>

            <Tabs value={tab} onValueChange={(value) => setTab(value as TabKey)} className="min-w-0">
                <TabsList className="max-w-full justify-start overflow-x-auto">
                    <TabsTrigger value="details" className="gap-1.5">
                        <ListChecks className="size-4" />
                        Details
                        <Count value={missingIn('details')} />
                    </TabsTrigger>
                    {schedule && (
                        <TabsTrigger value="schedule" className="gap-1.5">
                            <CalendarClock className="size-4" />
                            Work schedule
                            <Count value={missingIn('schedule')} />
                        </TabsTrigger>
                    )}
                    {rates && (
                        <TabsTrigger value="rates" className="gap-1.5">
                            <Wallet className="size-4" />
                            Rates
                            <Count value={missingIn('rates')} />
                        </TabsTrigger>
                    )}
                </TabsList>

                <TabsContent value="details" className="mt-4">
                    {details ? (
                        <BiometricEmployeeEdit key={JSON.stringify(details.values)} {...details} returnProfile={employee.id} />
                    ) : (
                        <DetailsReadOnly employee={employee} />
                    )}
                </TabsContent>

                {schedule && (
                    <TabsContent value="schedule" className="mt-4">
                        <ScheduleEditor key={JSON.stringify(schedule.row.schedule)} employeeId={employee.id} tab={schedule} />
                    </TabsContent>
                )}

                {rates && (
                    <TabsContent value="rates" className="mt-4">
                        <RatesPanel employeeId={employee.id} rates={rates} ratesList={props.urls.ratesList} />
                    </TabsContent>
                )}
            </Tabs>
        </div>
    );
}

function Count({ value }: { value: number }) {
    if (value === 0) return null;

    return (
        <Badge variant="outline" className={cn('h-5 px-1.5', MISSING_CLASS)}>
            {value}
        </Badge>
    );
}

function DetailsReadOnly({ employee }: { employee: EmployeeSummary }) {
    return (
        <DetailGrid
            items={[
                { label: 'Display name', value: employee.display_name },
                { label: 'Employee no.', value: employee.display_no },
                { label: 'Payroll group', value: employee.group_label ?? 'Not set' },
                { label: 'Company tag', value: employee.company ?? 'Not tagged' },
                { label: 'Status', value: employee.active ? 'Active' : 'Inactive' },
                { label: 'Payroll inclusion', value: employee.payroll_included ? 'Included' : 'Excluded' },
                { label: 'Device', value: employee.device_name },
                { label: 'Last check', value: employee.last_check ? `${employee.last_check} · ${employee.total_logs.toLocaleString()} logs` : 'No logs yet' },
                { label: 'Remarks', value: employee.remarks || '—', wide: true },
            ]}
        />
    );
}

interface ScheduleValues {
    employee_biometric_id: number;
    status: ScheduleStatus;
    shift_name: ScheduleShift;
    workday_type: string;
    time_in: string;
    time_out: string;
    grace_minutes: number | string;
    day_offs: string[];
    remarks: string;
}

/** The employee's permanent Work Schedule (same save route and rules as the Work Schedule grid). */
function ScheduleEditor({ employeeId, tab }: { employeeId: number; tab: ScheduleTab }) {
    const modal = useModal();
    const saved = tab.row.schedule;
    const initial = useMemo<ScheduleValues>(() => {
        const rule = tab.workdayRules[saved.workday_type] ?? tab.workdayRules.eight_hours;
        const timeIn = saved.time_in ?? '08:00';

        return {
            employee_biometric_id: tab.row.employee_biometric_id,
            status: saved.status,
            shift_name: saved.shift_name,
            workday_type: saved.workday_type,
            time_in: timeIn,
            time_out: saved.time_out ?? addMinutes(timeIn, rule.clock_minutes),
            grace_minutes: saved.grace_minutes,
            day_offs: saved.day_offs,
            remarks: saved.remarks ?? '',
        };
    }, [saved, tab]);

    const form = useForm<{ schedule: ScheduleValues[] }>({ schedule: [initial] });
    const row = form.data.schedule[0];
    const rule = tab.workdayRules[row.workday_type] ?? tab.workdayRules.eight_hours;
    const errors = form.errors as Record<string, string | undefined>;
    const error = (field: string) => errors[`schedule.0.${field}`];
    const readOnly = !tab.canUpdate;
    const timesDisabled = readOnly || row.status !== 'scheduled' || row.shift_name === 'Flexible Shift';

    const change = (changes: Partial<ScheduleValues>) => {
        const next = { ...row, ...changes };
        if (('time_in' in changes || 'workday_type' in changes) && next.time_in) {
            const nextRule = tab.workdayRules[next.workday_type];
            if (nextRule) next.time_out = addMinutes(next.time_in, nextRule.clock_minutes);
        }
        form.setData('schedule', [next]);
    };

    const save = () => {
        form.transform((data) => ({ ...data, return_profile: employeeId }));
        form.post(tab.urls.save, modal.visit({ preserveScroll: true }));
    };

    return (
        <Card className="gap-4">
            <CardHeader>
                <CardTitle className="flex flex-wrap items-center gap-2 text-base">
                    Permanent work schedule
                    {!saved.is_saved && (
                        <Badge variant="outline" className={MISSING_CLASS}>
                            Not saved yet — payroll uses the default 8 hrs + 1 hr lunch
                        </Badge>
                    )}
                </CardTitle>
                <CardDescription>
                    {rule.lunch_minutes > 0 ? `${rule.paid_hours} paid hours + ${rule.lunch_minutes / 60} unpaid lunch hour` : `${rule.paid_hours} paid hours straight, no lunch`}.
                    Time out follows time in and work hours automatically.
                </CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4">
                {!tab.payrollActive && (
                    <p className="flex items-start gap-2 rounded-md border px-3 py-2 text-xs text-muted-foreground">
                        <TriangleAlert className="mt-0.5 size-3.5 shrink-0" />
                        This employee is not in payroll (inactive or payroll inclusion off), so the schedule is kept but not used until they are included again.
                    </p>
                )}
                <div className="grid gap-4 md:grid-cols-3">
                    <FormField id="schedule-status" label="Status" error={error('status')} hint={STATUS_OPTIONS.find((option) => option.value === row.status)?.help}>
                        <RowSelect
                            id="schedule-status"
                            value={row.status}
                            disabled={readOnly}
                            onChange={(status) => change({ status: status as ScheduleStatus })}
                            options={STATUS_OPTIONS.map(({ value, label }) => ({ value, label }))}
                        />
                    </FormField>
                    <FormField id="schedule-shift" label="Shift" error={error('shift_name')} hint={row.shift_name === 'Flexible Shift' ? `Needs ${rule.clock_minutes / 60} clock hours any time.` : undefined}>
                        <RowSelect
                            id="schedule-shift"
                            value={row.shift_name}
                            disabled={readOnly}
                            onChange={(shift) => change({ shift_name: shift as ScheduleShift })}
                            options={SHIFT_OPTIONS.map((shift) => ({ value: shift, label: shift }))}
                        />
                    </FormField>
                    <FormField id="schedule-hours" label="Work hours" error={error('workday_type')}>
                        <RowSelect
                            id="schedule-hours"
                            value={row.workday_type}
                            disabled={readOnly}
                            onChange={(workday_type) => change({ workday_type })}
                            options={Object.entries(tab.workdayRules).map(([value, option]) => ({ value, label: option.label }))}
                        />
                    </FormField>
                    <FormField id="schedule-in" label="Time in" error={error('time_in')}>
                        <Input id="schedule-in" type="time" value={row.time_in} disabled={timesDisabled} onChange={(event) => change({ time_in: event.target.value })} />
                    </FormField>
                    <FormField id="schedule-out" label="Time out" error={error('time_out')}>
                        <Input id="schedule-out" type="time" value={row.time_out} disabled={timesDisabled} onChange={(event) => change({ time_out: event.target.value })} />
                    </FormField>
                    <FormField id="schedule-grace" label="Grace (min)" error={error('grace_minutes')} hint="0 or blank uses the company default.">
                        <Input
                            id="schedule-grace"
                            type="number"
                            min={0}
                            max={240}
                            value={row.grace_minutes}
                            disabled={readOnly || row.status !== 'scheduled'}
                            onChange={(event) => change({ grace_minutes: event.target.value })}
                        />
                    </FormField>
                    <FormField label="Days off" error={error('day_offs')} className="md:col-span-2">
                        <DayOffPicker weekdays={tab.weekdays} value={row.day_offs} disabled={readOnly} onChange={(day_offs) => change({ day_offs })} />
                    </FormField>
                    <FormField id="schedule-remarks" label="Remarks" error={error('remarks')}>
                        <Input id="schedule-remarks" maxLength={255} value={row.remarks} disabled={readOnly} onChange={(event) => change({ remarks: event.target.value })} />
                    </FormField>
                </div>
                {tab.canUpdate && (
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <p className="text-xs text-muted-foreground">After saving, rebuild Attendance Summary before regenerating a draft payroll.</p>
                        <Button type="button" onClick={save} disabled={form.processing}>
                            {form.processing ? <LoaderCircle className="animate-spin" /> : <Save />}
                            Save schedule
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function RatesPanel({ employeeId, rates, ratesList }: { employeeId: number; rates: RatesTab; ratesList: string | null }) {
    if (rates.mode === 'unavailable') {
        return (
            <Card>
                <CardContent className="text-sm text-muted-foreground">{rates.message}</CardContent>
            </Card>
        );
    }

    if (rates.mode === 'view' || !rates.form) {
        const summary = rates.summary!;
        return (
            <DetailGrid
                items={[
                    { label: 'Rate type', value: summary.rate_type },
                    { label: 'Basic salary', value: peso(summary.basic_salary) },
                    { label: 'Monthly allowance', value: peso(summary.allowance) },
                    { label: 'Day off', value: summary.paid_day_off ? 'Paid' : 'Not paid' },
                    { label: 'Status', value: summary.is_active ? 'Active' : 'Inactive' },
                    { label: 'Editing', value: 'You can view this rate but not change it.' },
                ]}
            />
        );
    }

    return (
        <div className="grid min-w-0 gap-3">
            {rates.mode === 'create' && (
                <p className="rounded-md border border-dashed px-3 py-2 text-sm text-muted-foreground">No rate yet. Fill this in and save to add one for this employee.</p>
            )}
            {rates.summary && !rates.summary.is_active && (
                <p className={cn('rounded-md border px-3 py-2 text-sm', MISSING_CLASS)}>This rate is inactive, so payroll does not use it. Tick Active below to use it again.</p>
            )}
            <EmployeeSalaryForm key={JSON.stringify(rates.form.values)} {...rates.form} returnProfile={employeeId} />
            {ratesList && (
                <Link href={ratesList} className="w-fit text-xs text-muted-foreground hover:underline">
                    Open the full Employee Rates list (sync, delete, all employees)
                </Link>
            )}
        </div>
    );
}
