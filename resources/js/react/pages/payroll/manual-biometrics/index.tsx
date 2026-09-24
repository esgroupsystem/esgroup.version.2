import { useForm } from '@inertiajs/react';
import { Copy, Eraser, Loader2, Save, Search, UserSearch, Zap } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { CutoffPicker, type CutoffValue } from '@/components/cutoff-picker';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { useModal } from '@/components/modal/modal-context';
import { RemoteEmployeeSearch, type RemoteEmployee } from '@/components/remote-employee-search';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';

interface SelectedEmployee extends RemoteEmployee {
    employee_name: string;
    crosschex_account: string;
}

interface CutoffRow {
    work_date: string;
    day_name: string;
    time_in: string | null;
    time_out: string | null;
    remarks: string | null;
    has_manual_log: boolean;
    device_punches: string[];
}

interface SavedLog {
    id: number;
    check_time: string | null;
    state: string;
    device_name: string;
    remarks: string;
}

interface Props {
    filters: CutoffValue;
    cutoffLabel: string;
    selectedEmployee: SelectedEmployee | null;
    cutoffRows: CutoffRow[];
    recentLogs: SavedLog[];
    can: { create: boolean };
    urls: { index: string; search: string; store: string };
}

interface RowValue {
    work_date: string;
    time_in: string;
    time_out: string;
    remarks: string;
}

const thisYear = new Date().getFullYear();
const YEARS = Array.from({ length: thisYear + 2 - 2024 }, (_, index) => thisYear + 1 - index);

const dateLabel = (value: string) =>
    new Date(`${value}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

const STATE_CLASS: Record<string, string> = {
    'Check In': 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
    'Check Out': 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
};

const LOG_COLUMNS: DataTableColumn<SavedLog>[] = [
    { key: 'check_time', header: 'Date time', className: 'font-medium whitespace-nowrap', cell: (log) => log.check_time, value: (log) => log.check_time },
    {
        key: 'state',
        header: 'State',
        cell: (log) => (
            <Badge variant="outline" className={STATE_CLASS[log.state]}>
                {log.state}
            </Badge>
        ),
        value: (log) => log.state,
        filter: {
            type: 'select',
            param: 'state',
            placeholder: 'All states',
            options: [
                { value: 'check in', label: 'Check In' },
                { value: 'check out', label: 'Check Out' },
            ],
        },
    },
    { key: 'device', header: 'Device', hideBelow: 'md', cell: (log) => log.device_name, value: (log) => log.device_name },
    { key: 'remarks', header: 'Remarks', className: 'max-w-72 whitespace-normal', cell: (log) => log.remarks, value: (log) => log.remarks },
];

export default definePage<Props>({
    title: () => 'Manual WFH Cutoff Encoding',
    description: () => 'Search one payroll employee, load the whole cutoff, then encode daily Time In / Time Out fast.',
    actions: ({ cutoffLabel }) => <Badge variant="secondary">{cutoffLabel}</Badge>,
    size: 'xl',
    Content: ManualBiometricsIndex,
});

function ManualBiometricsIndex({ filters, cutoffLabel, selectedEmployee, cutoffRows, recentLogs, can, urls }: Props) {
    const [cutoff, setCutoff] = useState<CutoffValue>(filters);
    const [employee, setEmployee] = useState<RemoteEmployee | null>(selectedEmployee);
    const modal = useModal();

    const load = (event: FormEvent) => {
        event.preventDefault();
        modal.get(urls.index, { ...cutoff, employee_biometric_id: employee?.employee_biometric_id });
    };

    return (
        <>
            <Card>
                <CardHeader>
                    <CardTitle>Step 1: Select cutoff and employee</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={load} className="grid gap-3 md:grid-cols-2 xl:grid-cols-[10rem_7rem_minmax(0,16rem)_minmax(0,1fr)_auto] xl:items-end">
                        <CutoffPicker value={cutoff} onChange={setCutoff} years={YEARS} idPrefix="manual" />
                        <div className="grid min-w-0 gap-1.5">
                            <Label htmlFor="manual-employee">Employee search</Label>
                            <RemoteEmployeeSearch id="manual-employee" url={urls.search} value={employee} onChange={setEmployee} />
                        </div>
                        <Button type="submit">
                            <Search />
                            Load
                        </Button>
                    </form>
                </CardContent>
            </Card>

            {selectedEmployee ? (
                <>
                    <div className="grid gap-4 md:grid-cols-3">
                        <InfoTile label="Employee name" value={selectedEmployee.employee_display_name} />
                        <InfoTile label="Employee no" value={selectedEmployee.employee_no || '-'} />
                        <InfoTile label="Bio ID / Account" value={`#${selectedEmployee.employee_biometric_id} / ${selectedEmployee.crosschex_account}`} />
                    </div>

                    <EncodeGrid
                        key={`${selectedEmployee.employee_biometric_id}-${filters.cutoff_year}-${filters.cutoff_month}-${filters.cutoff_type}`}
                        filters={filters}
                        cutoffLabel={cutoffLabel}
                        employeeId={selectedEmployee.employee_biometric_id}
                        rows={cutoffRows}
                        canCreate={can.create}
                        storeUrl={urls.store}
                    />

                    <DataTable
                        title="Saved manual logs"
                        description={`${cutoffLabel} · ${selectedEmployee.employee_display_name} · ${recentLogs.length} log(s)`}
                        noun="log"
                        rows={recentLogs}
                        columns={LOG_COLUMNS}
                        rowKey={(log) => log.id}
                        searchPlaceholder="Search date, device or remarks..."
                        emptyText="No saved manual logs for this cutoff."
                        minWidth={520}
                    />
                </>
            ) : (
                <Card>
                    <CardContent className="flex flex-col items-center gap-2 py-12 text-center">
                        <UserSearch className="size-10 text-muted-foreground" />
                        <h3 className="text-lg font-semibold">No employee selected yet</h3>
                        <p className="max-w-md text-sm text-muted-foreground">
                            Choose the cutoff, search an employee, then click Load to encode the whole cutoff.
                        </p>
                    </CardContent>
                </Card>
            )}
        </>
    );
}

function EncodeGrid({
    filters,
    cutoffLabel,
    employeeId,
    rows,
    canCreate,
    storeUrl,
}: {
    filters: CutoffValue;
    cutoffLabel: string;
    employeeId: number;
    rows: CutoffRow[];
    canCreate: boolean;
    storeUrl: string;
}) {
    const [bulk, setBulk] = useState({ time_in: '09:00', time_out: '18:00', remarks: '' });
    const form = useForm<{ cutoff_month: number; cutoff_year: number; cutoff_type: string; employee_biometric_id: number; rows: RowValue[] }>({
        ...filters,
        employee_biometric_id: employeeId,
        rows: rows.map((row) => ({ work_date: row.work_date, time_in: row.time_in ?? '', time_out: row.time_out ?? '', remarks: row.remarks ?? '' })),
    });
    const errors = form.errors as Record<string, string>;
    const modal = useModal();

    const setRow = (index: number, patch: Partial<RowValue>) =>
        form.setData('rows', form.data.rows.map((row, current) => (current === index ? { ...row, ...patch } : row)));

    // Fill only blank cells, exactly like the Blade quick-fill buttons.
    const applyCommon = (monSatOnly: boolean) =>
        form.setData(
            'rows',
            form.data.rows.map((row, index) => {
                if (monSatOnly && rows[index].day_name === 'Sun') return row;

                return {
                    ...row,
                    time_in: row.time_in || bulk.time_in,
                    time_out: row.time_out || bulk.time_out,
                    remarks: row.remarks || bulk.remarks,
                };
            }),
        );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(storeUrl, modal.visit({ preserveScroll: true }));
    };

    return (
        <form onSubmit={submit}>
            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <CardTitle>Step 2: Encode whole cutoff</CardTitle>
                        <CardDescription>{cutoffLabel}</CardDescription>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button type="button" variant="outline" size="sm" onClick={() => applyCommon(true)}>
                            <Copy />
                            Apply Mon-Sat only
                        </Button>
                        <Button type="button" variant="outline" size="sm" onClick={() => applyCommon(false)}>
                            <Zap />
                            Apply all blank dates
                        </Button>
                        {canCreate && (
                            <Button type="submit" size="sm" disabled={form.processing}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                Save employee logs
                            </Button>
                        )}
                    </div>
                </CardHeader>

                <div className="grid gap-3 border-b bg-muted/40 px-6 py-4 sm:grid-cols-2 lg:grid-cols-[10rem_10rem_1fr_auto] lg:items-end">
                    <div className="grid gap-1.5">
                        <Label htmlFor="bulk-in">Common time in</Label>
                        <Input id="bulk-in" type="time" value={bulk.time_in} onChange={(event) => setBulk({ ...bulk, time_in: event.target.value })} />
                    </div>
                    <div className="grid gap-1.5">
                        <Label htmlFor="bulk-out">Common time out</Label>
                        <Input id="bulk-out" type="time" value={bulk.time_out} onChange={(event) => setBulk({ ...bulk, time_out: event.target.value })} />
                    </div>
                    <div className="grid gap-1.5">
                        <Label htmlFor="bulk-remarks">Common remarks</Label>
                        <Input id="bulk-remarks" placeholder="Optional remarks" value={bulk.remarks} onChange={(event) => setBulk({ ...bulk, remarks: event.target.value })} />
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => form.setData('rows', form.data.rows.map((row) => ({ ...row, time_in: '', time_out: '', remarks: '' })))}
                    >
                        <Eraser />
                        Clear all
                    </Button>
                </div>

                <CardContent className="px-0">
                    <Table className="min-w-[900px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-12 pl-6">#</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead className="w-20">Day</TableHead>
                                <TableHead className="w-36">Time in</TableHead>
                                <TableHead className="w-36">Time out</TableHead>
                                <TableHead>Remarks</TableHead>
                                <TableHead>Device punches</TableHead>
                                <TableHead className="pr-6">Encoded</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {form.data.rows.map((row, index) => {
                                const source = rows[index];
                                const sunday = source.day_name === 'Sun';

                                return (
                                    <TableRow key={row.work_date} className={cn(sunday && 'bg-muted/50')}>
                                        <TableCell className="pl-6 font-medium">{index + 1}</TableCell>
                                        <TableCell className="font-medium whitespace-nowrap">{dateLabel(row.work_date)}</TableCell>
                                        <TableCell>
                                            <Badge variant={sunday ? 'outline' : 'secondary'} className={cn(sunday && 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400')}>
                                                {source.day_name}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                type="time"
                                                aria-label={`Time in ${row.work_date}`}
                                                aria-invalid={!!errors[`rows.${index}.time_in`]}
                                                value={row.time_in}
                                                onChange={(event) => setRow(index, { time_in: event.target.value })}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                type="time"
                                                aria-label={`Time out ${row.work_date}`}
                                                aria-invalid={!!errors[`rows.${index}.time_out`]}
                                                value={row.time_out}
                                                onChange={(event) => setRow(index, { time_out: event.target.value })}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                aria-label={`Remarks ${row.work_date}`}
                                                placeholder="Optional remarks"
                                                value={row.remarks}
                                                onChange={(event) => setRow(index, { remarks: event.target.value })}
                                            />
                                        </TableCell>
                                        <TableCell className="text-xs text-muted-foreground">
                                            {source.device_punches.length ? source.device_punches.join(', ') : '-'}
                                        </TableCell>
                                        <TableCell className="pr-6">
                                            {source.has_manual_log ? (
                                                <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">
                                                    Manual
                                                </Badge>
                                            ) : (
                                                <Badge variant="outline" className="text-muted-foreground">
                                                    None
                                                </Badge>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                );
                            })}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </form>
    );
}

function InfoTile({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border bg-card p-4 shadow-xs">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="mt-1 truncate font-semibold">{value}</p>
        </div>
    );
}
