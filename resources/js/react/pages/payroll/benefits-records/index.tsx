import { Link } from '@inertiajs/react';
import { Eye, FileSpreadsheet } from 'lucide-react';
import { useState } from 'react';
import type { BenefitsFilterValues } from '@/components/benefits-filters';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { MonthYearPicker } from '@/components/data-table/month-year-picker';
import { DetailDialog, DetailGrid } from '@/components/modal/detail-dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { definePage } from '@/lib/define-page';
import { peso } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface Program {
    name: string;
    id: string | null;
    due: number;
    collected: number;
    employer: number;
    total: number;
}

interface EmployeeRow {
    id: number;
    name: string;
    employee_no: string | null;
    company: string | null;
    group_label: string;
    posted: boolean;
    settlement_status: string;
    settlement_mode: string;
    payroll_numbers: string[];
    programs: Program[];
    employee_total: number;
    employee_collected_total: number;
    employer_total: number;
    grand_total: number;
    unrecovered: number;
}

interface Props {
    employees: Paginated<EmployeeRow>;
    kpis: { active: number; posted: number; not_posted: number; employee_due: number; collected: number; unrecovered: number; employer: number };
    filters: BenefitsFilterValues;
    periodLabel: string;
    groupOptions: Record<string, string>;
    urls: { index: string; overallWithFilters: string };
}

const STATUS: Record<string, { label: string; className: string }> = {
    complete: { label: 'Complete', className: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400' },
    employer_advanced: { label: 'Employer Advance', className: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400' },
    partial_collection: { label: 'Partial / Capped', className: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400' },
};

const statusOf = (row: EmployeeRow) =>
    STATUS[row.settlement_status] ??
    (row.posted ? { label: 'Finalized', className: 'border-sky-300 text-sky-700 dark:text-sky-400' } : { label: 'Not Posted', className: 'text-muted-foreground' });

export default definePage<Props>({
    title: () => 'Benefits Records',
    description: ({ periodLabel }) =>
        `One monthly record per employee (${periodLabel}). Statutory amount due is kept separate from the amount actually collected from payroll.`,
    actions: ({ urls }) => (
        <Button asChild>
            <Link href={urls.overallWithFilters}>
                <FileSpreadsheet />
                Overall / Print
            </Link>
        </Button>
    ),
    Content: BenefitsRecordsIndex,
});

function BenefitsRecordsIndex({ employees, kpis, filters, periodLabel, groupOptions, urls }: Props) {
    const [viewing, setViewing] = useState<EmployeeRow | null>(null);

    const columns: DataTableColumn<EmployeeRow>[] = [
        {
            key: 'employee',
            header: 'Employee',
            cell: (row) => (
                <>
                    <div className="font-medium">{row.name}</div>
                    <div className="text-xs text-muted-foreground">
                        {row.employee_no || 'No employee no.'} · {row.company || 'No company'}
                    </div>
                </>
            ),
        },
        {
            key: 'group',
            header: 'Payroll group',
            hideBelow: 'lg',
            className: 'text-sm',
            cell: (row) => row.group_label,
            filter: { type: 'select', param: 'garage_group', options: Object.entries(groupOptions).map(([value, label]) => ({ value, label })), placeholder: 'All groups' },
        },
        {
            key: 'status',
            header: 'Status',
            cell: (row) => {
                const status = statusOf(row);
                return (
                    <Badge variant="outline" className={status.className}>
                        {status.label}
                    </Badge>
                );
            },
        },
        { key: 'due', header: 'Employee due', align: 'right', className: 'tabular-nums', cell: (row) => peso(row.employee_total) },
        {
            key: 'collected',
            header: 'Collected',
            align: 'right',
            className: 'tabular-nums',
            cell: (row) => (
                <span className={cn(row.unrecovered > 0 ? 'text-amber-700 dark:text-amber-400' : undefined)}>
                    {peso(row.employee_collected_total)}
                    {row.unrecovered > 0 && <span className="block text-xs">Gap {peso(row.unrecovered)}</span>}
                </span>
            ),
        },
        { key: 'employer', header: 'Company share', align: 'right', hideBelow: 'md', className: 'tabular-nums', cell: (row) => peso(row.employer_total) },
        { key: 'total', header: 'Total', align: 'right', className: 'font-semibold tabular-nums', cell: (row) => peso(row.grand_total) },
    ];

    return (
        <>
            <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <Kpi label="Active employees" value={kpis.active.toLocaleString()} />
                <Kpi label="Posted" value={kpis.posted.toLocaleString()} hint={kpis.not_posted > 0 ? `${kpis.not_posted} active not posted` : undefined} />
                <Kpi label="Employee due" value={peso(kpis.employee_due)} />
                <Kpi label="Actually collected" value={peso(kpis.collected)} />
                <Kpi label="Unrecovered / advanced" value={peso(kpis.unrecovered)} tone={kpis.unrecovered > 0 ? 'text-amber-700 dark:text-amber-400' : undefined} />
                <Kpi label="Company share" value={peso(kpis.employer)} />
            </div>

            <DataTable
                title="Employee benefit records"
                description={`Only finalized payroll contributions are posted · ${periodLabel} · ${employees.total.toLocaleString()} employee(s)`}
                noun="employee"
                paginator={employees}
                url={urls.index}
                filters={{ search: filters.search, garage_group: filters.garage_group }}
                keepParams={{ month: String(filters.month), year: String(filters.year) }}
                columns={columns}
                rowKey={(row) => row.id}
                searchPlaceholder="Search name, employee no. or company..."
                emptyText="No employees match the selected month and filters."
                toolbar={<MonthYearPicker url={urls.index} month={filters.month} year={filters.year} params={{ search: filters.search, garage_group: filters.garage_group }} />}
                onRowClick={setViewing}
                rowActions={(row) => (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={(event) => {
                            event.stopPropagation();
                            setViewing(row);
                        }}
                    >
                        <Eye />
                        View
                    </Button>
                )}
            />

            {viewing && <RecordDialog row={viewing} periodLabel={periodLabel} onClose={() => setViewing(null)} />}
        </>
    );
}

function RecordDialog({ row, periodLabel, onClose }: { row: EmployeeRow; periodLabel: string; onClose: () => void }) {
    const status = statusOf(row);

    return (
        <DetailDialog
            open
            onOpenChange={(open) => !open && onClose()}
            size="lg"
            title={row.name}
            description={`${row.employee_no || 'No employee no.'} · ${row.company || 'No company'} · ${row.group_label}`}
            actions={
                <Badge variant="outline" className={status.className}>
                    {status.label}
                </Badge>
            }
        >
            <div className="grid gap-4">
                <DetailGrid
                    columns={3}
                    items={[
                        { label: 'Period', value: periodLabel },
                        { label: 'Payroll', value: row.payroll_numbers.length > 0 ? row.payroll_numbers.join(', ') : 'Not posted' },
                        { label: 'Grand total', value: <span className="font-semibold tabular-nums">{peso(row.grand_total)}</span> },
                    ]}
                />

                <div className="overflow-hidden rounded-lg border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/40 hover:bg-muted/40">
                                <TableHead className="pl-4">Benefit</TableHead>
                                <TableHead>Government ID</TableHead>
                                <TableHead className="text-right">Employee due</TableHead>
                                <TableHead className="text-right">Collected</TableHead>
                                <TableHead className="text-right">Company share</TableHead>
                                <TableHead className="pr-4 text-right">Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {row.programs.map((program) => {
                                const gap = Math.round((program.due - program.collected) * 100) / 100;
                                return (
                                    <TableRow key={program.name}>
                                        <TableCell className="pl-4 font-medium">{program.name}</TableCell>
                                        <TableCell className={cn('text-sm', !program.id && 'text-muted-foreground')}>{program.id || 'Not encoded'}</TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(program.due)}</TableCell>
                                        <TableCell className={cn('text-right tabular-nums', gap > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400')}>
                                            {peso(program.collected)}
                                            {gap > 0 && <div className="text-xs">Gap {peso(gap)}</div>}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(program.employer)}</TableCell>
                                        <TableCell className="pr-4 text-right tabular-nums">{peso(program.total)}</TableCell>
                                    </TableRow>
                                );
                            })}
                        </TableBody>
                        <TableFooter>
                            <TableRow>
                                <TableCell colSpan={2} className="pl-4 font-medium">
                                    Total
                                </TableCell>
                                <TableCell className="text-right font-medium tabular-nums">{peso(row.employee_total)}</TableCell>
                                <TableCell className="text-right font-medium tabular-nums">{peso(row.employee_collected_total)}</TableCell>
                                <TableCell className="text-right font-medium tabular-nums">{peso(row.employer_total)}</TableCell>
                                <TableCell className="pr-4 text-right font-semibold tabular-nums">{peso(row.grand_total)}</TableCell>
                            </TableRow>
                        </TableFooter>
                    </Table>
                </div>

                {row.unrecovered > 0 && (
                    <Alert>
                        <AlertDescription>
                            <span>
                                <strong>{peso(row.unrecovered)}</strong> employee share was not collected from salary.{' '}
                                {row.settlement_mode === 'employer_advance'
                                    ? 'Employer Advance is selected; keep this amount for HR/accounting settlement instead of making payroll negative.'
                                    : 'Payroll collection was capped to available pay; HR may review the employee settlement/refund action from the payroll item.'}
                            </span>
                        </AlertDescription>
                    </Alert>
                )}
            </div>
        </DetailDialog>
    );
}

function Kpi({ label, value, hint, tone }: { label: string; value: string; hint?: string; tone?: string }) {
    return (
        <div className="rounded-xl border bg-card p-3.5 shadow-xs">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className={cn('mt-1 text-lg font-semibold tabular-nums', tone)}>{value}</p>
            {hint && <p className="mt-0.5 text-xs text-muted-foreground">{hint}</p>}
        </div>
    );
}
