import { useForm } from '@inertiajs/react';
import { Save, TriangleAlert, WandSparkles } from 'lucide-react';
import { useMemo, useState } from 'react';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { useModal } from '@/components/modal/modal-context';
import { StatCard } from '@/components/page-header';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

type Status = 'scheduled' | 'rest_day' | 'inactive';
type Shift = 'Regular Shift' | 'Flexible Shift';

interface ScheduleRow {
    employee_biometric_id: number;
    status: Status;
    shift_name: Shift;
    workday_type: string;
    time_in: string;
    time_out: string;
    grace_minutes: number | string;
    day_offs: string[];
    remarks: string;
}

interface EmployeeRow {
    employee_biometric_id: number;
    name: string;
    employee_no: string | null;
    group_name: string | null;
    schedule: Omit<ScheduleRow, 'employee_biometric_id' | 'time_in' | 'time_out'> & {
        time_in: string | null;
        time_out: string | null;
        is_saved: boolean;
    };
}

interface WorkdayRule {
    label: string;
    short_label: string;
    paid_hours: number;
    lunch_minutes: number;
    clock_minutes: number;
}

interface Props {
    employees: Paginated<EmployeeRow>;
    filters: { search: string; status: string; shift: string; group_name: string };
    groups: string[];
    stats: Record<string, number>;
    workdayRules: Record<string, WorkdayRule>;
    weekdays: string[];
    urls: { index: string; save: string };
}

const STATUS_OPTIONS: { value: Status; label: string; help: string }[] = [
    { value: 'scheduled', label: 'Scheduled', help: 'Normal attendance computation' },
    { value: 'rest_day', label: 'Rest Day', help: 'Permanent rest-day status' },
    { value: 'inactive', label: 'Inactive', help: 'Excluded from payroll attendance' },
];

const SHIFT_OPTIONS: Shift[] = ['Regular Shift', 'Flexible Shift'];

/** Mirrors the save validation: Time Out = Time In + the workday's clock span. */
function addMinutes(time: string, minutes: number): string {
    const [hours, mins] = time.split(':').map(Number);
    if (!Number.isFinite(hours) || !Number.isFinite(mins)) return '';

    const total = (((hours * 60 + mins + minutes) % 1440) + 1440) % 1440;

    return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
}

export default definePage<Props>({
    title: () => 'Permanent Work Schedule',
    description: () =>
        'Assign 8 paid hours + 1 unpaid lunch hour, 9 paid hours + 1 unpaid lunch hour, or 8 straight paid hours with no lunch. More than one weekly day off may be selected per employee.',
    size: 'xl',
    // New rows (filter, page, or a save) remount the editor so it never keeps stale edits.
    Content: (props) => (
        <PlottingEditor key={JSON.stringify(props.employees.data.map((employee) => [employee.employee_biometric_id, employee.schedule]))} {...props} />
    ),
});

function PlottingEditor({ employees, filters, groups, stats, workdayRules, weekdays, urls }: Props) {
    const modal = useModal();
    const initialRows = useMemo<ScheduleRow[]>(
        () =>
            employees.data.map((employee) => {
                const rule = workdayRules[employee.schedule.workday_type] ?? workdayRules.eight_hours;
                const timeIn = employee.schedule.time_in ?? '08:00';

                return {
                    employee_biometric_id: employee.employee_biometric_id,
                    status: employee.schedule.status,
                    shift_name: employee.schedule.shift_name,
                    workday_type: employee.schedule.workday_type,
                    time_in: timeIn,
                    time_out: employee.schedule.time_out ?? addMinutes(timeIn, rule.clock_minutes),
                    grace_minutes: employee.schedule.grace_minutes,
                    day_offs: employee.schedule.day_offs,
                    remarks: employee.schedule.remarks ?? '',
                };
            }),
        [employees.data, workdayRules],
    );

    const form = useForm({ schedule: initialRows });
    const errors = form.errors as Record<string, string>;
    const indexOf = useMemo(() => new Map(employees.data.map((employee, index) => [employee.employee_biometric_id, index])), [employees.data]);

    const updateRow = (index: number, changes: Partial<ScheduleRow>) => {
        form.setData(
            'schedule',
            form.data.schedule.map((row, i) => {
                if (i !== index) return row;

                const next = { ...row, ...changes };

                if ('time_in' in changes || 'workday_type' in changes) {
                    const rule = workdayRules[next.workday_type];
                    if (rule && next.time_in) next.time_out = addMinutes(next.time_in, rule.clock_minutes);
                }

                return next;
            }),
        );
    };

    const save = () => {
        // Send the filters in effect now (they can change without the rows changing).
        form.transform((data) => ({ ...data, ...filters }));
        form.post(urls.save, modal.visit({ preserveScroll: true }));
    };

    /** Editable state of one employee row. */
    const edit = (employee: EmployeeRow) => {
        const index = indexOf.get(employee.employee_biometric_id) ?? -1;
        const row = form.data.schedule[index];
        const rule = (row && workdayRules[row.workday_type]) ?? workdayRules.eight_hours;
        const error = (field: string) => errors[`schedule.${index}.${field}`];
        const rowError = Object.entries(errors).find(([key]) => key.startsWith(`schedule.${index}.`))?.[1];

        return { index, row, rule, error, rowError, change: (changes: Partial<ScheduleRow>) => updateRow(index, changes) };
    };

    const columns: DataTableColumn<EmployeeRow>[] = [
        {
            key: 'employee',
            header: 'Employee',
            className: 'align-top',
            filter: { type: 'select', param: 'group_name', options: groups.map((group) => ({ value: group, label: `Group ${group}` })), placeholder: 'All groups' },
            cell: (employee) => {
                const { rowError } = edit(employee);

                return (
                    <>
                        <div className="max-w-52 truncate font-medium" title={employee.name}>
                            {employee.name}
                        </div>
                        <div className="text-xs text-muted-foreground">{employee.employee_no ?? 'No employee number'}</div>
                        <div className="mt-1 flex flex-wrap gap-1">
                            {employee.group_name && <Badge variant="outline">Group {employee.group_name}</Badge>}
                            {!employee.schedule.is_saved && <Badge variant="secondary">Not saved yet</Badge>}
                        </div>
                        {rowError && <p className="mt-1 max-w-56 text-xs whitespace-normal text-destructive">{rowError}</p>}
                    </>
                );
            },
        },
        {
            key: 'status',
            header: 'Status',
            className: 'align-top',
            filter: { type: 'select', param: 'status', options: STATUS_OPTIONS.map(({ value, label }) => ({ value, label })), placeholder: 'All statuses' },
            cell: (employee) => {
                const { row, change } = edit(employee);
                if (!row) return null;

                return (
                    <>
                        <RowSelect
                            value={row.status}
                            onChange={(status) => change({ status: status as Status })}
                            options={STATUS_OPTIONS.map((option) => ({ value: option.value, label: option.label }))}
                            className="w-32"
                        />
                        <p className="mt-1 max-w-32 text-xs whitespace-normal text-muted-foreground">{STATUS_OPTIONS.find((option) => option.value === row.status)?.help}</p>
                    </>
                );
            },
        },
        {
            key: 'shift',
            header: 'Shift & work hours',
            className: 'align-top',
            filter: { type: 'select', param: 'shift', options: SHIFT_OPTIONS.map((shift) => ({ value: shift, label: shift })), placeholder: 'All shifts' },
            cell: (employee) => {
                const { row, rule, change } = edit(employee);
                if (!row) return null;

                return (
                    <div className="grid w-52 gap-1.5">
                        <RowSelect
                            value={row.shift_name}
                            onChange={(shift) => change({ shift_name: shift as Shift })}
                            options={SHIFT_OPTIONS.map((shift) => ({ value: shift, label: shift }))}
                            ariaLabel={`Shift ${employee.name}`}
                        />
                        <RowSelect
                            value={row.workday_type}
                            onChange={(workday_type) => change({ workday_type })}
                            options={Object.entries(workdayRules).map(([value, option]) => ({ value, label: option.label }))}
                            ariaLabel={`Work hours ${employee.name}`}
                        />
                        <p className="text-xs whitespace-normal text-muted-foreground">
                            {row.shift_name === 'Flexible Shift' ? `Flexible: requires ${rule.clock_minutes / 60} clock hours. ` : ''}
                            {rule.lunch_minutes > 0 ? `${rule.paid_hours} paid + ${rule.lunch_minutes / 60} unpaid lunch hour` : `${rule.paid_hours} paid hours straight, no lunch`}
                        </p>
                    </div>
                );
            },
        },
        {
            key: 'time',
            header: 'Time in / out & grace',
            className: 'align-top',
            cell: (employee) => {
                const { row, error, change } = edit(employee);
                if (!row) return null;
                const disabled = row.status !== 'scheduled' || row.shift_name === 'Flexible Shift';

                return (
                    <div className="grid w-32 gap-1.5">
                        <Input
                            type="time"
                            aria-label={`Time in ${employee.name}`}
                            value={row.time_in}
                            disabled={disabled}
                            aria-invalid={Boolean(error('time_in'))}
                            onChange={(event) => change({ time_in: event.target.value })}
                        />
                        <Input
                            type="time"
                            aria-label={`Time out ${employee.name}`}
                            value={row.time_out}
                            disabled={disabled}
                            aria-invalid={Boolean(error('time_out'))}
                            onChange={(event) => change({ time_out: event.target.value })}
                        />
                        <div className="flex items-center rounded-md border shadow-xs focus-within:ring-[3px] focus-within:ring-ring/50">
                            <Input
                                type="number"
                                min={0}
                                max={240}
                                className="border-0 text-right shadow-none focus-visible:ring-0"
                                value={row.grace_minutes}
                                disabled={row.status !== 'scheduled'}
                                aria-label={`Grace minutes ${employee.name}`}
                                onChange={(event) => change({ grace_minutes: event.target.value })}
                            />
                            <span className="pr-2 text-xs whitespace-nowrap text-muted-foreground">min grace</span>
                        </div>
                    </div>
                );
            },
        },
        {
            key: 'days_off',
            header: 'Days off & remarks',
            className: 'align-top',
            cell: (employee) => {
                const { row, change } = edit(employee);
                if (!row) return null;

                return (
                    <div className="grid gap-1.5">
                        <DayOffPicker weekdays={weekdays} value={row.day_offs} onChange={(day_offs) => change({ day_offs })} />
                        <Input
                            className="w-full"
                            maxLength={255}
                            placeholder="Optional note"
                            aria-label={`Remarks ${employee.name}`}
                            value={row.remarks}
                            onChange={(event) => change({ remarks: event.target.value })}
                        />
                    </div>
                );
            },
        },
        {
            key: 'preview',
            header: 'Preview',
            hideBelow: '2xl',
            className: 'align-top text-xs text-muted-foreground',
            cell: (employee) => {
                const { row, rule } = edit(employee);
                if (!row) return null;

                return (
                    <>
                        <div className="font-medium text-foreground">{row.shift_name}</div>
                        <div>{rule.short_label}</div>
                        {row.status === 'scheduled' && row.shift_name !== 'Flexible Shift' && (
                            <div className="tabular-nums">
                                {row.time_in || '—'} to {row.time_out || '—'}
                            </div>
                        )}
                        <div>Days off: {row.day_offs.length ? row.day_offs.map((day) => day.slice(0, 3)).join(', ') : 'None'}</div>
                    </>
                );
            },
        },
    ];

    const saveButton = (
        <Button type="button" onClick={save} disabled={form.processing || employees.data.length === 0}>
            <Save />
            {form.processing ? 'Saving…' : 'Save Schedule'}
        </Button>
    );

    return (
        <>
            <Alert>
                <TriangleAlert />
                <AlertTitle>Payroll safety reminder</AlertTitle>
                <AlertDescription>After saving schedule changes, rebuild Attendance Summary before regenerating a draft payroll.</AlertDescription>
            </Alert>

            <div className="grid grid-cols-2 gap-3 xl:grid-cols-4">
                <StatCard label="Visible employees" value={stats.visible_employees ?? 0} hint="Current page after filtering" />
                <StatCard label="Scheduled" value={stats.scheduled ?? 0} hint="Normal work schedules" />
                <StatCard label="Rest-day status" value={stats.rest_day ?? 0} hint="Permanent non-working status" />
                <StatCard label="Inactive" value={stats.inactive ?? 0} hint="Excluded from payroll attendance" />
            </div>

            <div className="flex flex-wrap gap-2">
                <Badge variant="secondary">{stats.eight_hours ?? 0} on 8 hrs + lunch</Badge>
                <Badge variant="secondary">{stats.nine_hours ?? 0} on 9 hrs + lunch</Badge>
                <Badge variant="secondary">{stats.straight_eight ?? 0} on 8 hrs straight</Badge>
            </div>

            <QuickFill
                workdayRules={workdayRules}
                weekdays={weekdays}
                disabled={form.data.schedule.length === 0}
                onApply={(values) => form.setData('schedule', form.data.schedule.map((row) => ({ ...row, ...values })))}
            />

            <DataTable
                title="Employee work schedule"
                description={
                    <>
                        Regular Shift must span exactly the workday's clock hours. Time Out is recalculated when Work Hours or Time In changes.
                        {form.isDirty && <span className="ml-2 font-medium text-amber-700 dark:text-amber-400">Unsaved changes — save before changing filters or pages.</span>}
                    </>
                }
                noun="employee"
                paginator={employees}
                url={urls.index}
                filters={filters}
                columns={columns}
                rowKey={(employee) => employee.employee_biometric_id}
                rowClassName={(employee) => (edit(employee).rowError ? 'bg-destructive/5' : undefined)}
                searchPlaceholder="Name, employee no., or biometric ID..."
                emptyText="No employees found. Change the filters or verify the biometric employee records."
                minWidth={980}
                toolbar={saveButton}
                footer={employees.data.length > 0 ? <div className="flex justify-end border-t px-6 py-3">{saveButton}</div> : null}
            />
        </>
    );
}

type QuickFillValues = Omit<ScheduleRow, 'employee_biometric_id' | 'remarks'>;

function QuickFill({
    workdayRules,
    weekdays,
    disabled,
    onApply,
}: {
    workdayRules: Record<string, WorkdayRule>;
    weekdays: string[];
    disabled: boolean;
    onApply: (values: QuickFillValues) => void;
}) {
    const [values, setValues] = useState<QuickFillValues>({
        status: 'scheduled',
        shift_name: 'Regular Shift',
        workday_type: 'eight_hours',
        time_in: '08:00',
        time_out: '17:00',
        grace_minutes: 15,
        day_offs: [],
    });

    const change = (changes: Partial<QuickFillValues>) => {
        const next = { ...values, ...changes };
        const rule = workdayRules[next.workday_type];
        if (rule && next.time_in && ('time_in' in changes || 'workday_type' in changes)) {
            next.time_out = addMinutes(next.time_in, rule.clock_minutes);
        }
        setValues(next);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <WandSparkles className="size-4" />
                    Quick Fill visible rows
                </CardTitle>
                <CardDescription>Applies these values to every employee on this page. Nothing is saved until you click Save.</CardDescription>
            </CardHeader>
            <CardContent className="flex flex-wrap items-end gap-3">
                <div className="grid gap-1.5 w-40">
                    <Label>Status</Label>
                    <RowSelect
                        value={values.status}
                        onChange={(status) => change({ status: status as Status })}
                        options={STATUS_OPTIONS.map((option) => ({ value: option.value, label: option.label }))}
                    />
                </div>
                <div className="grid gap-1.5 w-40">
                    <Label>Shift</Label>
                    <RowSelect
                        value={values.shift_name}
                        onChange={(shift) => change({ shift_name: shift as Shift })}
                        options={SHIFT_OPTIONS.map((shift) => ({ value: shift, label: shift }))}
                    />
                </div>
                <div className="grid gap-1.5 w-64">
                    <Label>Work hours</Label>
                    <RowSelect
                        value={values.workday_type}
                        onChange={(workday_type) => change({ workday_type })}
                        options={Object.entries(workdayRules).map(([value, rule]) => ({ value, label: rule.label }))}
                    />
                </div>
                <div className="grid gap-1.5 w-32">
                    <Label>Time in</Label>
                    <Input type="time" value={values.time_in} onChange={(event) => change({ time_in: event.target.value })} />
                </div>
                <div className="grid gap-1.5 w-32">
                    <Label>Time out</Label>
                    <Input type="time" value={values.time_out} onChange={(event) => change({ time_out: event.target.value })} />
                </div>
                <div className="grid gap-1.5 w-24">
                    <Label>Grace (min)</Label>
                    <Input
                        type="number"
                        min={0}
                        max={240}
                        value={values.grace_minutes}
                        onChange={(event) => change({ grace_minutes: event.target.value })}
                    />
                </div>
                <div className="grid gap-1.5">
                    <Label>Days off</Label>
                    <DayOffPicker weekdays={weekdays} value={values.day_offs} onChange={(day_offs) => change({ day_offs })} />
                </div>
                <Button type="button" variant="secondary" disabled={disabled} onClick={() => onApply(values)}>
                    Apply
                </Button>
            </CardContent>
        </Card>
    );
}

function RowSelect({
    value,
    onChange,
    options,
    className,
    ariaLabel,
}: {
    value: string;
    onChange: (value: string) => void;
    options: { value: string; label: string }[];
    className?: string;
    ariaLabel?: string;
}) {
    return (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger className={cn('w-full', className)} aria-label={ariaLabel}>
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
function DayOffPicker({
    weekdays,
    value,
    onChange,
}: {
    weekdays: string[];
    value: string[];
    onChange: (value: string[]) => void;
}) {
    const toggle = (day: string) =>
        onChange(
            value.includes(day)
                ? value.filter((selected) => selected !== day)
                : weekdays.filter((weekday) => weekday === day || value.includes(weekday)),
        );

    return (
        <div className="flex gap-1" role="group" aria-label="Weekly days off">
            {weekdays.map((day) => {
                const active = value.includes(day);

                return (
                    <button
                        key={day}
                        type="button"
                        title={day}
                        aria-pressed={active}
                        onClick={() => toggle(day)}
                        className={cn(
                            'h-8 w-9 rounded-md border text-xs font-medium transition-colors',
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

