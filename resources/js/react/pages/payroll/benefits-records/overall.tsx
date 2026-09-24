import { Link } from '@inertiajs/react';
import { Eye, Printer, Users } from 'lucide-react';
import { useState } from 'react';
import type { BenefitsFilterValues } from '@/components/benefits-filters';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { MonthYearPicker } from '@/components/data-table/month-year-picker';
import { DetailDialog } from '@/components/modal/detail-dialog';
import { useModal } from '@/components/modal/modal-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';

interface RegisterRow {
    id: number;
    name: string;
    company: string;
    sss_employee_regular_ss: number;
    sss_employer_regular_ss: number;
    sss_employer_ec: number;
    sss_employee_mpf: number;
    sss_employer_mpf: number;
    sss_total_contribution: number;
    philhealth_employee: number;
    philhealth_employer: number;
    philhealth_total: number;
    pagibig_employee: number;
    pagibig_employer: number;
    pagibig_total: number;
}

interface Props {
    rows: RegisterRow[];
    totals: Record<string, number>;
    companyTotals: { company_name: string; employee_count: number; totals: Record<string, number> }[];
    payrollNumbers: string[];
    filters: BenefitsFilterValues;
    periodLabel: string;
    groupOptions: Record<string, string>;
    urls: { overall: string; indexWithFilters: string; print: string };
}

const amount = (value: number | undefined) => Number(value ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const sssPremium = (row: RegisterRow) => row.sss_employee_regular_ss + row.sss_employer_regular_ss + row.sss_employer_ec;
const mpf = (row: RegisterRow) => row.sss_employee_mpf + row.sss_employer_mpf;
const ALL = '__all';

export default definePage<Props>({
    title: () => 'Benefits Overall',
    description: ({ periodLabel }) => `Statutory contribution register (SSS/MPF worksheet layout) · ${periodLabel}. Print gives the full worksheet.`,
    actions: ({ urls }) => (
        <>
            <Button variant="outline" asChild>
                <Link href={urls.indexWithFilters}>
                    <Users />
                    Employee records
                </Link>
            </Button>
            <Button asChild>
                <a href={urls.print} target="_blank" rel="noopener">
                    <Printer />
                    Print
                </a>
            </Button>
        </>
    ),
    Content: BenefitsOverall,
});

/** Total with the employee / employer shares under it, so the register fits without 16 columns. */
function Split({ total, ee, er, extra }: { total: number; ee: number; er: number; extra?: string }) {
    return (
        <div className="text-right tabular-nums">
            <div className="font-medium">{amount(total)}</div>
            <div className="text-xs whitespace-nowrap text-muted-foreground">
                EE {amount(ee)} · ER {amount(er)}
                {extra}
            </div>
        </div>
    );
}

function BenefitsOverall({ rows, totals, companyTotals, payrollNumbers, filters, periodLabel, groupOptions, urls }: Props) {
    const modal = useModal();
    const [viewing, setViewing] = useState<RegisterRow | null>(null);
    const companies = [...new Set(rows.map((row) => row.company))].sort().map((value) => ({ value, label: value }));

    const columns: DataTableColumn<RegisterRow>[] = [
        { key: 'name', header: 'Employee', className: 'font-medium', value: (row) => row.name, cell: (row) => row.name },
        { key: 'company', header: 'Company', hideBelow: 'lg', value: (row) => row.company, cell: (row) => row.company, filter: { type: 'select', param: 'company', options: companies, placeholder: 'All companies' } },
        {
            key: 'sss',
            header: 'SSS premium',
            align: 'right',
            cell: (row) => <Split total={sssPremium(row)} ee={row.sss_employee_regular_ss} er={row.sss_employer_regular_ss} extra={` · EC ${amount(row.sss_employer_ec)}`} />,
        },
        { key: 'mpf', header: 'MPF', align: 'right', hideBelow: 'xl', cell: (row) => <Split total={mpf(row)} ee={row.sss_employee_mpf} er={row.sss_employer_mpf} /> },
        { key: 'sss_total', header: 'SSS / MPF', align: 'right', className: 'font-semibold tabular-nums', cell: (row) => amount(row.sss_total_contribution) },
        { key: 'philhealth', header: 'PhilHealth', align: 'right', hideBelow: 'md', cell: (row) => <Split total={row.philhealth_total} ee={row.philhealth_employee} er={row.philhealth_employer} /> },
        { key: 'pagibig', header: 'Pag-IBIG', align: 'right', hideBelow: 'md', cell: (row) => <Split total={row.pagibig_total} ee={row.pagibig_employee} er={row.pagibig_employer} /> },
    ];

    const setGroup = (value: string) =>
        modal.get(urls.overall, { month: String(filters.month), year: String(filters.year), search: filters.search, garage_group: value === ALL ? '' : value });

    return (
        <>
            <DataTable
                title="Government benefits contribution register"
                description={`${periodLabel} · ${rows.length} posted employee record(s)${payrollNumbers.length > 0 ? ` · Payroll: ${payrollNumbers.join(', ')}` : ''} · EE = Employee · ER = Employer · EC = Employees' Compensation`}
                rows={rows}
                rowKey={(row) => row.id}
                columns={columns}
                noun="employee"
                pageSize={25}
                searchPlaceholder="Search employee or company..."
                emptyText="No posted Benefits Records match the selected month and filters."
                toolbar={
                    <>
                        <Select value={filters.garage_group || ALL} onValueChange={setGroup}>
                            <SelectTrigger size="sm" className="w-52" aria-label="Payroll group">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={ALL}>All payroll groups</SelectItem>
                                {Object.entries(groupOptions).map(([value, label]) => (
                                    <SelectItem key={value} value={value}>
                                        {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <MonthYearPicker url={urls.overall} month={filters.month} year={filters.year} params={{ search: filters.search, garage_group: filters.garage_group }} />
                    </>
                }
                onRowClick={setViewing}
                rowActions={(row) => (
                    <Button
                        variant="ghost"
                        size="icon"
                        className="size-8"
                        aria-label={`View ${row.name}`}
                        onClick={(event) => {
                            event.stopPropagation();
                            setViewing(row);
                        }}
                    >
                        <Eye />
                    </Button>
                )}
                footer={
                    rows.length > 0 && (
                        <div className="grid grid-cols-2 gap-x-6 gap-y-2 border-t bg-muted/30 px-6 py-3 text-sm sm:grid-cols-5">
                            <Total label="SSS premium" value={totals.sss_regular_total} />
                            <Total label="MPF" value={totals.sss_mpf_total} />
                            <Total label="SSS / MPF" value={totals.sss_total} strong />
                            <Total label="PhilHealth" value={totals.philhealth_total} />
                            <Total label="Pag-IBIG" value={totals.pagibig_total} />
                        </div>
                    )
                }
            />

            {companyTotals.length > 0 && (
                <Card className="gap-0 py-0">
                    <CardHeader className="border-b py-4">
                        <CardTitle>Totals by company</CardTitle>
                    </CardHeader>
                    <CardContent className="px-0">
                        <Table className="text-sm">
                            <TableHeader>
                                <TableRow className="bg-muted/40 hover:bg-muted/40">
                                    <TableHead className="pl-6">Company</TableHead>
                                    <TableHead className="text-right">Employees</TableHead>
                                    <TableHead className="hidden text-right md:table-cell">SSS/MPF</TableHead>
                                    <TableHead className="hidden text-right md:table-cell">PhilHealth</TableHead>
                                    <TableHead className="hidden text-right md:table-cell">Pag-IBIG</TableHead>
                                    <TableHead className="text-right">Employee</TableHead>
                                    <TableHead className="text-right">Employer</TableHead>
                                    <TableHead className="pr-6 text-right">Grand total</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {companyTotals.map((company) => (
                                    <TableRow key={company.company_name}>
                                        <TableCell className="pl-6 font-medium">{company.company_name}</TableCell>
                                        <TableCell className="text-right tabular-nums">{company.employee_count}</TableCell>
                                        <TableCell className="hidden text-right tabular-nums md:table-cell">{amount(company.totals.sss_total)}</TableCell>
                                        <TableCell className="hidden text-right tabular-nums md:table-cell">{amount(company.totals.philhealth_total)}</TableCell>
                                        <TableCell className="hidden text-right tabular-nums md:table-cell">{amount(company.totals.pagibig_total)}</TableCell>
                                        <TableCell className="text-right tabular-nums">{amount(company.totals.employee_total)}</TableCell>
                                        <TableCell className="text-right tabular-nums">{amount(company.totals.employer_total)}</TableCell>
                                        <TableCell className="pr-6 text-right font-semibold tabular-nums">{amount(company.totals.grand_total)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            )}

            {viewing && (
                <DetailDialog open onOpenChange={(open) => !open && setViewing(null)} size="md" title={viewing.name} description={`${viewing.company} · ${periodLabel}`}>
                    <div className="overflow-hidden rounded-lg border bg-card">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/40 hover:bg-muted/40">
                                    <TableHead className="pl-4">Contribution</TableHead>
                                    <TableHead className="text-right">Employee (EE)</TableHead>
                                    <TableHead className="text-right">Employer (ER)</TableHead>
                                    <TableHead className="pr-4 text-right">Total</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <Breakdown label="SSS premium" ee={viewing.sss_employee_regular_ss} er={viewing.sss_employer_regular_ss} total={sssPremium(viewing)} note={`incl. EC ${amount(viewing.sss_employer_ec)}`} />
                                <Breakdown label="MPF" ee={viewing.sss_employee_mpf} er={viewing.sss_employer_mpf} total={mpf(viewing)} />
                                <Breakdown label="PhilHealth" ee={viewing.philhealth_employee} er={viewing.philhealth_employer} total={viewing.philhealth_total} />
                                <Breakdown label="Pag-IBIG" ee={viewing.pagibig_employee} er={viewing.pagibig_employer} total={viewing.pagibig_total} />
                            </TableBody>
                            <TableFooter>
                                <TableRow>
                                    <TableCell className="pl-4 font-medium">SSS / MPF total</TableCell>
                                    <TableCell colSpan={2} />
                                    <TableCell className="pr-4 text-right font-semibold tabular-nums">{amount(viewing.sss_total_contribution)}</TableCell>
                                </TableRow>
                            </TableFooter>
                        </Table>
                    </div>
                </DetailDialog>
            )}
        </>
    );
}

function Breakdown({ label, ee, er, total, note }: { label: string; ee: number; er: number; total: number; note?: string }) {
    return (
        <TableRow>
            <TableCell className="pl-4">
                <div className="font-medium">{label}</div>
                {note && <div className="text-xs text-muted-foreground">{note}</div>}
            </TableCell>
            <TableCell className="text-right tabular-nums">{amount(ee)}</TableCell>
            <TableCell className="text-right tabular-nums">{amount(er)}</TableCell>
            <TableCell className="pr-4 text-right font-medium tabular-nums">{amount(total)}</TableCell>
        </TableRow>
    );
}

function Total({ label, value, strong }: { label: string; value: number | undefined; strong?: boolean }) {
    return (
        <div>
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className={cn('tabular-nums', strong ? 'font-semibold' : 'font-medium')}>{amount(value)}</div>
        </div>
    );
}
