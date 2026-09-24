import { Link, router } from '@inertiajs/react';
import { ArrowLeft, ChevronRight, FileSpreadsheet, FileText, HandCoins, LoaderCircle, Lock, UserX } from 'lucide-react';
import { useState } from 'react';
import { ConfirmAction } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { useModal } from '@/components/modal/modal-context';
import { ModalLink } from '@/components/modal/modal-link';
import { openModal } from '@/components/modal/modal-store';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { definePage } from '@/lib/define-page';
import { peso } from '@/lib/format';
import { cn } from '@/lib/utils';

type Tone = 'success' | 'warning' | 'danger' | 'info' | 'primary';

interface ItemRow {
    id: number;
    name: string;
    employee_no: string | null;
    settlement_only: boolean;
    tags: { label: string; amount: number }[];
    payable_days: number;
    regular: number;
    additions: number;
    government: number;
    other_deductions: number;
    gross: number;
    net: number;
    badges: { label: string; tone: Tone }[];
    severity: 'ok' | 'warning' | 'danger';
    url: string;
}

interface Props {
    payroll: {
        id: number;
        payroll_number: string;
        status: string;
        cutoff_label: string;
        contribution_label: string;
        period_start: string | null;
        period_end: string | null;
        group_label: string;
        eligible_roster: number;
        missing_summary_employees: number;
        settlement_carry_forward: number;
    };
    totals: Record<string, number>;
    items: ItemRow[];
    can: { finalize: boolean; export: boolean };
    urls: { index: string; finalize: string; excel: string; pdf: string };
}

export const TONE_CLASS: Record<Tone, string> = {
    success: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
    warning: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    danger: 'border-destructive/40 text-destructive',
    info: 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400',
    primary: 'border-violet-300 text-violet-700 dark:border-violet-800 dark:text-violet-400',
};

const forReviewCount = (items: ItemRow[]) => items.filter((item) => item.severity !== 'ok').length;

export default definePage<Props>({
    title: ({ payroll }) => payroll.payroll_number,
    description: (props) => <Summary {...props} />,
    actions: (props) => <Actions {...props} />,
    // Starts extra-large; the modal widens itself to fit the breakdown table.
    size: 'xl',
    Content: PayrollContent,
});

function Summary({ payroll, items }: Props) {
    const forReview = forReviewCount(items);

    return (
        <span className="flex flex-wrap items-center gap-x-2 gap-y-1">
            <Badge variant={payroll.status === 'finalized' ? 'outline' : 'secondary'} className={cn(payroll.status === 'finalized' && TONE_CLASS.success)}>
                {payroll.status.charAt(0).toUpperCase() + payroll.status.slice(1)}
            </Badge>
            <Badge variant="outline" className={forReview > 0 ? TONE_CLASS.warning : TONE_CLASS.success}>
                {forReview > 0 ? `${forReview} for review` : 'Audit clean'}
            </Badge>
            <span>{payroll.cutoff_label}</span>·<span>Contribution: {payroll.contribution_label}</span>·
            <span>
                {payroll.period_start} – {payroll.period_end}
            </span>
            ·<span>{payroll.group_label}</span>
        </span>
    );
}

function Actions({ can, urls }: Props) {
    const modal = useModal();
    const [finalizing, setFinalizing] = useState(false);
    const size = modal.inModal ? 'sm' : 'default';

    return (
        <>
            {!modal.inModal && (
                <Button variant="outline" asChild>
                    <Link href={urls.index}>
                        <ArrowLeft />
                        Back
                    </Link>
                </Button>
            )}
            {can.export && (
                <>
                    <Button variant="outline" size={size} asChild>
                        <a href={urls.excel}>
                            <FileSpreadsheet />
                            Excel
                        </a>
                    </Button>
                    <Button variant="outline" size={size} asChild>
                        <a href={urls.pdf} target="_blank" rel="noopener">
                            <FileText />
                            Payslips PDF
                        </a>
                    </Button>
                </>
            )}
            {can.finalize && (
                <ConfirmAction
                    title="Finalize payroll?"
                    description="Finalizing locks this payroll and posts government benefit contributions. Review every flagged row first."
                    confirmLabel="Finalize"
                    onConfirm={() =>
                        router.post(
                            urls.finalize,
                            {},
                            modal.visit({
                                preserveScroll: true,
                                onStart: () => setFinalizing(true),
                                onFinish: () => setFinalizing(false),
                            }),
                        )
                    }
                    trigger={
                        <Button size={size} disabled={finalizing}>
                            {finalizing ? <LoaderCircle className="animate-spin" /> : <Lock />}
                            {finalizing ? 'Finalizing & posting benefits…' : 'Finalize'}
                        </Button>
                    }
                />
            )}
        </>
    );
}

const AUDIT_OPTIONS = [
    { value: 'ok', label: 'Clean' },
    { value: 'warning', label: 'Needs checking' },
    { value: 'danger', label: 'Problem' },
];

const columns: DataTableColumn<ItemRow>[] = [
    {
        key: 'employee',
        header: 'Employee',
        value: (item) => `${item.name} ${item.employee_no ?? ''}`,
        filter: { type: 'text', param: 'employee', placeholder: 'Name or ID...' },
        cell: (item) => (
            <div className="min-w-56">
                <div className="font-medium">{item.name}</div>
                <div className="text-xs text-muted-foreground">{item.employee_no || 'No Employee No'}</div>
                {(item.settlement_only || item.tags.length > 0) && (
                    <div className="mt-1 flex flex-wrap gap-1">
                        {item.settlement_only && (
                            <Badge variant="outline" className={TONE_CLASS.info}>
                                Separated / Settlement
                            </Badge>
                        )}
                        {item.tags.map((tag, index) => (
                            <Badge key={index} variant="secondary" className="font-normal">
                                {tag.label}
                                {tag.amount > 0 && ` ${peso(tag.amount)}`}
                            </Badge>
                        ))}
                    </div>
                )}
            </div>
        ),
    },
    { key: 'payable', header: 'Payable', align: 'right', hideBelow: 'md', className: 'tabular-nums whitespace-nowrap', cell: (item) => `${item.payable_days.toFixed(2)} day` },
    { key: 'regular', header: 'Regular', align: 'right', hideBelow: 'lg', className: 'tabular-nums', cell: (item) => peso(item.regular) },
    { key: 'additions', header: 'Additions', align: 'right', hideBelow: 'lg', className: 'tabular-nums', cell: (item) => peso(item.additions) },
    { key: 'government', header: 'Government', align: 'right', hideBelow: 'xl', className: 'tabular-nums', cell: (item) => peso(item.government) },
    { key: 'other', header: 'Other deduct.', align: 'right', hideBelow: 'xl', className: 'tabular-nums', cell: (item) => peso(item.other_deductions) },
    { key: 'gross', header: 'Gross', align: 'right', className: 'tabular-nums', cell: (item) => peso(item.gross) },
    { key: 'net', header: 'Net', align: 'right', className: 'font-medium tabular-nums', cell: (item) => peso(item.net) },
    {
        key: 'audit',
        header: 'Audit',
        value: (item) => item.severity,
        filter: { type: 'select', param: 'audit', options: AUDIT_OPTIONS, placeholder: 'All rows' },
        cell: (item) =>
            item.badges.length === 0 ? (
                <span className="text-xs text-muted-foreground">Clean</span>
            ) : (
                <div className="flex max-w-64 flex-wrap gap-1">
                    {item.badges.map((badge) => (
                        <Badge key={badge.label} variant="outline" className={TONE_CLASS[badge.tone]}>
                            {badge.label}
                        </Badge>
                    ))}
                </div>
            ),
    },
    {
        key: 'kind',
        header: 'Record',
        hideBelow: 'xl',
        value: (item) => (item.settlement_only ? 'settlement' : 'regular'),
        filter: {
            type: 'select',
            param: 'kind',
            options: [
                { value: 'regular', label: 'Regular' },
                { value: 'settlement', label: 'Settlement only' },
            ],
            placeholder: 'All records',
        },
        cell: (item) => <span className="text-xs text-muted-foreground">{item.settlement_only ? 'Settlement' : 'Regular'}</span>,
    },
];

function PayrollContent({ payroll, totals, items }: Props) {
    const additions = totals.holiday_pay + totals.rest_day_pay + totals.overtime_pay + totals.night_differential_pay + totals.leave_pay + totals.other_additions;
    const deductions = totals.government + totals.other_deductions;
    const forReview = forReviewCount(items);
    const netRate = totals.gross_pay > 0 ? (totals.net_pay / totals.gross_pay) * 100 : 0;
    const deductionRate = totals.gross_pay > 0 ? (deductions / totals.gross_pay) * 100 : 0;

    return (
        <>
            {payroll.missing_summary_employees > 0 && (
                <Alert variant="destructive">
                    <UserX />
                    <AlertTitle>Attendance Summary coverage is incomplete</AlertTitle>
                    <AlertDescription>
                        {payroll.missing_summary_employees} payroll-eligible employee(s) have no summary rows for this cutoff. They are still included below
                        as zero-pay No Summary records so nobody disappears silently. Rebuild Attendance Summary and regenerate this draft before finalizing.
                    </AlertDescription>
                </Alert>
            )}

            {payroll.settlement_carry_forward > 0 && (
                <Alert>
                    <HandCoins />
                    <AlertTitle>Benefit settlement carry-forward included</AlertTitle>
                    <AlertDescription>
                        {payroll.settlement_carry_forward} employee(s) from the finalized 26-10 cutoff are no longer payroll-active for this 11-25 cutoff.
                        They remain here as zero-gross settlement records so HR can reconcile SSS, PhilHealth, Pag-IBIG, and any approved final-pay
                        reimbursement.
                    </AlertDescription>
                </Alert>
            )}

            <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <Kpi label="Employees" value={totals.employees.toLocaleString()} hint={`Roster: ${payroll.eligible_roster} eligible`} />
                <Kpi label="Regular pay" value={peso(totals.regular_pay)} hint="Base salary amount" />
                <Kpi label="Additions" value={peso(additions)} hint="Holiday, rest, OT, night diff, adjustments" />
                <Kpi label="Gross pay" value={peso(totals.gross_pay)} hint="Before deduction" />
                <Kpi label="Deductions" value={peso(deductions)} hint="Gov. + other" tone="text-destructive" />
                <Kpi label="Net pay" value={peso(totals.net_pay)} hint="Final payable" tone="text-emerald-700 dark:text-emerald-400" />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <Card className="gap-3">
                    <CardHeader>
                        <CardTitle>Payroll audit summary</CardTitle>
                    </CardHeader>
                    <CardContent className="grid grid-cols-2 gap-2 text-sm">
                        <Metric label="With additions" value={items.filter((item) => item.additions > 0).length} />
                        <Metric label="With deductions" value={items.filter((item) => item.government + item.other_deductions > 0).length} />
                        <Metric label="For review" value={forReview} />
                        <Metric label="Clean records" value={Math.max(items.length - forReview, 0)} />
                        <Metric label="Total payable days" value={totals.payable_days.toFixed(2)} />
                        <Metric label="Total payable hours" value={totals.payable_hours.toFixed(2)} />
                    </CardContent>
                </Card>
                <Card className="gap-3">
                    <CardHeader>
                        <CardTitle>Gross to net ratio</CardTitle>
                        <CardDescription>High deduction rows are tagged when total deductions exceed 60% of gross pay.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 text-sm">
                        <Ratio label="Net pay ratio" value={netRate} barClass="bg-emerald-500" />
                        <Ratio label="Deduction ratio" value={deductionRate} barClass="bg-destructive" />
                    </CardContent>
                </Card>
                <Card className="gap-3">
                    <CardHeader>
                        <CardTitle>Amount breakdown</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2 text-sm">
                        <Line label="Holiday pay" value={peso(totals.holiday_pay)} />
                        <Line label="Rest day pay" value={peso(totals.rest_day_pay)} />
                        <Line label="Overtime pay" value={peso(totals.overtime_pay)} />
                        <Line label="Government deductions" value={peso(totals.government)} />
                        <Line label="Loans / other deductions" value={peso(totals.other_deductions)} />
                    </CardContent>
                </Card>
            </div>

            <DataTable
                title="Employee payroll breakdown"
                description="Badges identify rows that need checking. Click a row for the full computation."
                rows={items}
                rowKey={(item) => item.id}
                columns={columns}
                searchPlaceholder="Search employee name or ID..."
                noun="employee"
                pageSize={20}
                minWidth={900}
                rowClassName={(item) =>
                    cn('cursor-pointer', item.severity === 'danger' && 'bg-destructive/5', item.severity === 'warning' && 'bg-amber-50/60 dark:bg-amber-950/20')
                }
                onRowClick={(item) => openModal(item.url, { size: 'xl' })}
                rowActions={(item) => (
                    <Button variant="ghost" size="icon" className="size-8" asChild>
                        <ModalLink href={item.url} size="xl" aria-label={`Open ${item.name}`} onClick={(event) => event.stopPropagation()}>
                            <ChevronRight />
                        </ModalLink>
                    </Button>
                )}
                footer={
                    <div className="grid grid-cols-2 gap-x-6 gap-y-1 border-t bg-muted/30 px-6 py-3 text-sm sm:grid-cols-4">
                        <Total label={`Employees (${totals.employees})`} value={`${totals.payable_days.toFixed(2)} days`} />
                        <Total label="Gross" value={peso(totals.gross_pay)} />
                        <Total label="Deductions" value={peso(deductions)} />
                        <Total label="Net" value={peso(totals.net_pay)} strong />
                    </div>
                }
            />
        </>
    );
}

function Total({ label, value, strong }: { label: string; value: string; strong?: boolean }) {
    return (
        <div className="flex items-baseline justify-between gap-2 sm:block">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className={cn('tabular-nums', strong ? 'font-semibold' : 'font-medium')}>{value}</div>
        </div>
    );
}

function Kpi({ label, value, hint, tone }: { label: string; value: string; hint: string; tone?: string }) {
    return (
        <div className="rounded-xl border bg-card p-3.5 shadow-xs">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className={cn('mt-1 text-lg font-semibold tabular-nums', tone)}>{value}</p>
            <p className="mt-0.5 line-clamp-1 text-xs text-muted-foreground" title={hint}>
                {hint}
            </p>
        </div>
    );
}

function Metric({ label, value }: { label: string; value: string | number }) {
    return (
        <div className="rounded-lg border p-2.5">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="text-base font-semibold tabular-nums">{value}</div>
        </div>
    );
}

function Ratio({ label, value, barClass }: { label: string; value: number; barClass: string }) {
    return (
        <div className="grid gap-1.5">
            <div className="flex justify-between">
                <span className="text-muted-foreground">{label}</span>
                <span className="font-medium tabular-nums">{value.toFixed(1)}%</span>
            </div>
            <div className="h-2 overflow-hidden rounded-full bg-muted">
                <div className={cn('h-full rounded-full', barClass)} style={{ width: `${Math.min(Math.max(value, 0), 100)}%` }} />
            </div>
        </div>
    );
}

function Line({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between border-b pb-2 last:border-0">
            <span className="text-muted-foreground">{label}</span>
            <span className="font-medium tabular-nums">{value}</span>
        </div>
    );
}
