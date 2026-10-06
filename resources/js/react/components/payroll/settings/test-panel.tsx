import { FlaskConical, LoaderCircle, Play, User, Users } from 'lucide-react';
import { useMemo, useState } from 'react';
import { CutoffPicker, type CutoffValue } from '@/components/cutoff-picker';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { FormField } from '@/components/form-field';
import { DetailDialog, DetailGrid } from '@/components/modal/detail-dialog';
import { SearchSelect } from '@/components/search-select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { csrfToken, peso } from '@/lib/format';
import { notify, notifyHttpError } from '@/lib/notify';
import { cn } from '@/lib/utils';
import type { SettingValues, TestOptions } from './types';

interface Line {
    key: string;
    label: string;
    section: 'earnings' | 'attendance' | 'deductions' | 'employer' | 'total';
    amount: number;
    hint: string | null;
    counted: boolean;
}

interface ItemResult {
    employee_biometric_id: number;
    name: string;
    employee_no: string | null;
    rate_type: string;
    facts: { label: string; value: string }[];
    lines: Line[];
    gross: number;
    net: number;
    employee_government: number;
    employer_government: number;
    notes: string[];
}

interface RunResult {
    label: string;
    version_id: number | null;
    items: ItemResult[];
    totals: { gross: number; net: number; employee_government: number; employer_government: number };
}

interface SimulationResponse {
    period: { label: string; cutoff: string; note: string | null };
    scope: 'employee' | 'group';
    baseline: RunResult;
    candidate: RunResult | null;
}

/** What to compare the saved settings with. Omit for a plain test run. */
export interface TestCompare {
    values?: SettingValues;
    rule?: Record<string, unknown>;
}

const SECTIONS: { key: Line['section']; title: string }[] = [
    { key: 'earnings', title: 'Earnings' },
    { key: 'attendance', title: 'Attendance loss' },
    { key: 'deductions', title: 'Deductions' },
    { key: 'employer', title: 'Employer share (not taken from pay)' },
];

const yearsAround = (year: number) => [year - 2, year - 1, year, year + 1];

async function postJson<T>(url: string, body: unknown): Promise<T | null> {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(body),
    });

    if (response.status === 422) {
        const data = (await response.json().catch(() => null)) as { message?: string; errors?: Record<string, string[]> } | null;
        const first = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        notify('error', first ?? data?.message ?? 'Please check the values.');
        return null;
    }

    if (!response.ok) {
        notifyHttpError(response.status);
        return null;
    }

    return (await response.json()) as T;
}

export { postJson };

/**
 * Runs the real payroll computation for one employee or a whole payroll group and shows
 * every line. Nothing is saved. With `compare`, a second run shows what would change.
 */
export function TestPanel({
    options,
    compare,
    compareLabel = 'With your changes',
    allowVersionCompare = false,
    validate,
}: {
    options: TestOptions;
    compare?: TestCompare;
    compareLabel?: string;
    allowVersionCompare?: boolean;
    /** Return a message to stop the run (e.g. the form is incomplete). */
    validate?: () => string | null;
}) {
    const [mode, setMode] = useState<'employee' | 'group'>('employee');
    const [employee, setEmployee] = useState('');
    const [group, setGroup] = useState(options.groups[0]?.value ?? '');
    const [period, setPeriod] = useState<CutoffValue>({
        cutoff_month: options.period.month,
        cutoff_year: options.period.year,
        cutoff_type: options.period.type,
    });
    const [version, setVersion] = useState('none');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState<SimulationResponse | null>(null);

    const run = async () => {
        const problem = validate?.();
        if (problem) {
            notify('warning', problem);
            return;
        }
        if (mode === 'employee' && !employee) {
            notify('warning', 'Pick an employee to test.');
            return;
        }

        setLoading(true);
        try {
            const data = await postJson<SimulationResponse>(options.urls.run, {
                ...(mode === 'employee' ? { employee_biometric_id: Number(employee) } : { garage_group: group }),
                ...period,
                ...(compare?.values ? { compare_values: compare.values } : {}),
                ...(compare?.rule ? { compare_rule: compare.rule } : {}),
                ...(allowVersionCompare && version !== 'none' ? { compare_version_id: Number(version) } : {}),
            });
            if (data) setResult(data);
        } catch {
            notify('error', 'Could not reach the server. Check your connection and try again.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="grid min-w-0 gap-4">
            <Card className="gap-4">
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-base">
                        <FlaskConical className="size-4" aria-hidden />
                        Test on real attendance
                    </CardTitle>
                    <CardDescription>
                        Runs the same computation as Generate payroll, then throws it away. Nothing is saved.
                        {compare && ' The result shows the saved settings next to your unsaved changes.'}
                    </CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4">
                    <div className="inline-flex w-fit rounded-lg bg-muted p-1" role="tablist" aria-label="Who to test">
                        {(
                            [
                                ['employee', 'One employee', User],
                                ['group', 'Whole payroll group', Users],
                            ] as const
                        ).map(([key, label, Icon]) => (
                            <button
                                key={key}
                                type="button"
                                role="tab"
                                aria-selected={mode === key}
                                onClick={() => setMode(key)}
                                className={cn(
                                    'inline-flex h-8 items-center gap-1.5 rounded-md px-3 text-sm font-medium',
                                    mode === key ? 'bg-background shadow-sm' : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                <Icon className="size-4" aria-hidden />
                                {label}
                            </button>
                        ))}
                    </div>

                    <div className="grid gap-4 md:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,1fr))]">
                        {mode === 'employee' ? (
                            <FormField id="test-employee" label="Employee">
                                <SearchSelect
                                    id="test-employee"
                                    ariaLabel="Employee"
                                    options={options.employees}
                                    value={employee}
                                    onChange={setEmployee}
                                    placeholder="Pick an employee"
                                    searchPlaceholder="Search name or number..."
                                />
                            </FormField>
                        ) : (
                            <FormField id="test-group" label="Payroll group" hint="Every Active employee with Payroll Inclusion ON. Can take a minute.">
                                <Select value={group} onValueChange={setGroup}>
                                    <SelectTrigger id="test-group" className="w-full">
                                        <SelectValue placeholder="Pick a group" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.groups.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                        )}
                        <CutoffPicker value={period} onChange={setPeriod} years={yearsAround(options.period.year)} idPrefix="test-cutoff" />
                    </div>

                    <div className="flex flex-wrap items-end justify-between gap-3">
                        {allowVersionCompare ? (
                            <FormField id="test-compare" label="Compare with" className="w-full sm:w-80">
                                <Select value={version} onValueChange={setVersion}>
                                    <SelectTrigger id="test-compare" className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">Nothing (just test)</SelectItem>
                                        {options.versions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                        ) : (
                            <span />
                        )}
                        <Button type="button" onClick={run} disabled={loading} className="w-full sm:w-auto">
                            {loading ? <LoaderCircle className="animate-spin" /> : <Play />}
                            {loading ? 'Computing...' : 'Run test'}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            {result && <SimulationResult result={result} compareLabel={compare ? compareLabel : undefined} />}
        </div>
    );
}

function SimulationResult({ result, compareLabel }: { result: SimulationResponse; compareLabel?: string }) {
    const { baseline, candidate } = result;
    const newLabel = candidate ? (compareLabel ?? candidate.label) : null;

    return (
        <div className="grid min-w-0 gap-4">
            <div className="flex flex-wrap items-center gap-2 text-sm">
                <Badge variant="outline">{result.period.cutoff}</Badge>
                <span className="text-muted-foreground">{result.period.label}</span>
                <span className="text-muted-foreground">·</span>
                <span>
                    Saved settings: <span className="font-medium">{baseline.label}</span>
                </span>
                {candidate && (
                    <>
                        <span className="text-muted-foreground">vs</span>
                        <span className="font-medium text-primary">{newLabel}</span>
                    </>
                )}
            </div>
            {result.period.note && <p className="text-xs text-muted-foreground">{result.period.note}</p>}

            {result.scope === 'employee' ? (
                <EmployeeResult baseline={baseline.items[0]} candidate={candidate?.items[0] ?? null} newLabel={newLabel} />
            ) : (
                <GroupResult baseline={baseline} candidate={candidate} newLabel={newLabel} />
            )}
        </div>
    );
}

function EmployeeResult({ baseline, candidate, newLabel }: { baseline: ItemResult; candidate: ItemResult | null; newLabel: string | null }) {
    return (
        <div className="grid min-w-0 gap-4">
            <div className="grid gap-3 sm:grid-cols-3">
                <Total label="Gross pay" value={baseline.gross} next={candidate?.gross} />
                <Total label="Government (employee)" value={baseline.employee_government} next={candidate?.employee_government} negative />
                <Total label="Net pay" value={baseline.net} next={candidate?.net} strong />
            </div>

            <Card className="gap-3">
                <CardHeader>
                    <CardTitle className="text-base">{baseline.name}</CardTitle>
                    <CardDescription>
                        {[baseline.employee_no, baseline.rate_type ? `${baseline.rate_type}-paid` : null].filter(Boolean).join(' · ')}
                    </CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4">
                    <DetailGrid columns={3} items={baseline.facts.map((fact) => ({ label: fact.label, value: fact.value }))} />
                    {[...baseline.notes, ...(candidate?.notes ?? []).filter((note) => !baseline.notes.includes(note))].map((note) => (
                        <p key={note} className="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                            {note}
                        </p>
                    ))}
                    <LinesTable baseline={baseline} candidate={candidate} newLabel={newLabel} />
                </CardContent>
            </Card>
        </div>
    );
}

function Total({ label, value, next, negative, strong }: { label: string; value: number; next?: number; negative?: boolean; strong?: boolean }) {
    const diff = next !== undefined ? Math.round((next - value) * 100) / 100 : 0;

    return (
        <Card className={cn('gap-1 py-4', strong && 'border-primary/40 bg-primary/5')}>
            <CardContent className="grid gap-1">
                <span className="text-xs text-muted-foreground">{label}</span>
                <span className={cn('text-xl font-semibold tabular-nums', negative && 'text-destructive')}>{peso(next ?? value)}</span>
                {next !== undefined && (
                    <span className="text-xs tabular-nums text-muted-foreground">
                        Saved: {peso(value)} · <Difference value={diff} />
                    </span>
                )}
            </CardContent>
        </Card>
    );
}

function Difference({ value }: { value: number }) {
    if (Math.abs(value) < 0.005) return <span className="text-muted-foreground">no change</span>;

    return (
        <span className={cn('font-medium tabular-nums', value > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive')}>
            {value > 0 ? '+' : '−'}
            {peso(Math.abs(value))}
        </span>
    );
}

/** Line-by-line table; zero lines in both runs collapse into one "None" note per section. */
function LinesTable({ baseline, candidate, newLabel }: { baseline: ItemResult; candidate: ItemResult | null; newLabel: string | null }) {
    const rows = useMemo(() => {
        const keys: string[] = [];
        const byKey = new Map<string, { base?: Line; next?: Line }>();
        for (const line of baseline.lines) {
            keys.push(line.key);
            byKey.set(line.key, { base: line });
        }
        for (const line of candidate?.lines ?? []) {
            if (!byKey.has(line.key)) keys.push(line.key);
            byKey.set(line.key, { ...byKey.get(line.key), next: line });
        }
        return keys.map((key) => ({ key, ...byKey.get(key)! }));
    }, [baseline, candidate]);

    const total = (key: 'gross' | 'net') => rows.find((row) => row.key === key);

    return (
        <div className="overflow-x-auto rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Item</TableHead>
                        <TableHead className="text-right">{candidate ? 'Saved settings' : 'Amount'}</TableHead>
                        {candidate && <TableHead className="text-right text-primary">{newLabel}</TableHead>}
                        {candidate && <TableHead className="text-right">Difference</TableHead>}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {SECTIONS.map((section) => {
                        const sectionRows = rows.filter((row) => (row.base ?? row.next)!.section === section.key);
                        const shown = sectionRows.filter((row) => Math.abs(row.base?.amount ?? 0) > 0.005 || Math.abs(row.next?.amount ?? 0) > 0.005);
                        const zero = sectionRows.filter((row) => !shown.includes(row));
                        const after = section.key === 'attendance' ? total('gross') : section.key === 'deductions' ? total('net') : null;

                        return (
                            <SectionRows
                                key={section.key}
                                title={section.title}
                                rows={shown}
                                zeroLabels={zero.map((row) => (row.base ?? row.next)!.label.toLowerCase())}
                                hasCandidate={!!candidate}
                                total={after}
                            />
                        );
                    })}
                </TableBody>
            </Table>
        </div>
    );
}

function SectionRows({
    title,
    rows,
    zeroLabels,
    hasCandidate,
    total,
}: {
    title: string;
    rows: { key: string; base?: Line; next?: Line }[];
    zeroLabels: string[];
    hasCandidate: boolean;
    total: { key: string; base?: Line; next?: Line } | null | undefined;
}) {
    const span = hasCandidate ? 4 : 2;

    return (
        <>
            <TableRow className="bg-muted/40 hover:bg-muted/40">
                <TableCell colSpan={span} className="py-1.5 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                    {title}
                </TableCell>
            </TableRow>
            {rows.map((row) => {
                const line = (row.base ?? row.next)!;
                const negative = line.section === 'deductions' || (line.section === 'attendance' && line.counted);
                return (
                    <TableRow key={row.key}>
                        <TableCell className="min-w-44 whitespace-normal">
                            <div className={cn('font-medium', !line.counted && 'text-muted-foreground')}>{line.label}</div>
                            {(row.next?.hint ?? line.hint) && <div className="w-72 max-w-full text-xs whitespace-normal text-muted-foreground">{row.next?.hint ?? line.hint}</div>}
                        </TableCell>
                        <TableCell className={cn('text-right tabular-nums', negative && 'text-destructive', !line.counted && 'text-muted-foreground')}>
                            {row.base ? `${negative ? '−' : ''}${peso(row.base.amount)}` : '—'}
                        </TableCell>
                        {hasCandidate && (
                            <TableCell className={cn('text-right font-medium tabular-nums', negative && 'text-destructive')}>
                                {row.next ? `${negative ? '−' : ''}${peso(row.next.amount)}` : '—'}
                            </TableCell>
                        )}
                        {hasCandidate && (
                            <TableCell className="text-right">
                                <Difference value={Math.round(((row.next?.amount ?? 0) - (row.base?.amount ?? 0)) * (negative ? -100 : 100)) / 100} />
                            </TableCell>
                        )}
                    </TableRow>
                );
            })}
            {zeroLabels.length > 0 && (
                <TableRow>
                    <TableCell colSpan={span} className="py-1.5 text-xs whitespace-normal text-muted-foreground">
                        None this cutoff: {zeroLabels.join(', ')}.
                    </TableCell>
                </TableRow>
            )}
            {total && (
                <TableRow className="bg-primary/5 font-semibold hover:bg-primary/5">
                    <TableCell>{(total.base ?? total.next)!.label}</TableCell>
                    <TableCell className="text-right tabular-nums">{total.base ? peso(total.base.amount) : '—'}</TableCell>
                    {hasCandidate && <TableCell className="text-right tabular-nums">{total.next ? peso(total.next.amount) : '—'}</TableCell>}
                    {hasCandidate && (
                        <TableCell className="text-right">
                            <Difference value={Math.round(((total.next?.amount ?? 0) - (total.base?.amount ?? 0)) * 100) / 100} />
                        </TableCell>
                    )}
                </TableRow>
            )}
        </>
    );
}

interface GroupRow {
    id: number;
    name: string;
    employee_no: string | null;
    base: ItemResult;
    next: ItemResult | null;
    diff: number;
}

function GroupResult({ baseline, candidate, newLabel }: { baseline: RunResult; candidate: RunResult | null; newLabel: string | null }) {
    const [open, setOpen] = useState<GroupRow | null>(null);

    const rows: GroupRow[] = baseline.items.map((item, index) => {
        const next = candidate?.items[index] ?? null;
        return {
            id: item.employee_biometric_id,
            name: item.name,
            employee_no: item.employee_no,
            base: item,
            next,
            diff: next ? Math.round((next.net - item.net) * 100) / 100 : 0,
        };
    });
    const changed = rows.filter((row) => Math.abs(row.diff) >= 0.005).length;
    const ordered = candidate ? [...rows].sort((a, b) => Math.abs(b.diff) - Math.abs(a.diff)) : rows;

    const columns: DataTableColumn<GroupRow>[] = [
        {
            key: 'name',
            header: 'Employee',
            className: 'min-w-44 whitespace-normal',
            value: (row) => `${row.name} ${row.employee_no ?? ''}`,
            cell: (row) => (
                <>
                    <div className="font-medium">{row.name}</div>
                    <div className="text-xs text-muted-foreground">{[row.employee_no, row.base.rate_type].filter(Boolean).join(' · ')}</div>
                </>
            ),
        },
        { key: 'gross', header: 'Gross', align: 'right', className: 'tabular-nums', value: (row) => row.base.gross, cell: (row) => peso(row.base.gross) },
        { key: 'net', header: candidate ? 'Net (saved)' : 'Net', align: 'right', className: 'tabular-nums', value: (row) => row.base.net, cell: (row) => peso(row.base.net) },
        ...(candidate
            ? ([
                  { key: 'new', header: 'Net (new)', align: 'right', className: 'tabular-nums font-medium', value: (row) => row.next?.net ?? 0, cell: (row) => peso(row.next?.net ?? 0) },
                  { key: 'diff', header: 'Difference', align: 'right', value: (row) => row.diff, cell: (row) => <Difference value={row.diff} /> },
              ] as DataTableColumn<GroupRow>[])
            : []),
    ];

    return (
        <div className="grid min-w-0 gap-4">
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Card className="gap-1 py-4">
                    <CardContent className="grid gap-1">
                        <span className="text-xs text-muted-foreground">Employees</span>
                        <span className="text-xl font-semibold tabular-nums">{rows.length}</span>
                        {candidate && <span className="text-xs text-muted-foreground">{changed} with a different net pay</span>}
                    </CardContent>
                </Card>
                <Total label="Total gross" value={baseline.totals.gross} next={candidate?.totals.gross} />
                <Total label="Total net pay" value={baseline.totals.net} next={candidate?.totals.net} strong />
                <Total label="Employer contributions" value={baseline.totals.employer_government} next={candidate?.totals.employer_government} />
            </div>

            <DataTable
                title="Per employee"
                description="Click a row to see every line."
                noun="employee"
                rows={ordered}
                columns={columns}
                rowKey={(row) => row.id}
                onRowClick={setOpen}
                exportTitle="Payroll test run"
            />

            <DetailDialog open={open !== null} onOpenChange={(value) => !value && setOpen(null)} title={open?.name ?? ''} description={open?.employee_no ?? undefined} size="lg">
                {open && <EmployeeResult baseline={open.base} candidate={open.next} newLabel={newLabel} />}
            </DetailDialog>
        </div>
    );
}
