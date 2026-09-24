import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, FilePlus2, LoaderCircle, RefreshCw, Save } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { AdjustmentEditor } from '@/components/adjustments/adjustment-editor';
import { ConfirmAction } from '@/components/confirm-action';
import type { PersonOption } from '@/components/employee-combobox';
import { useModal } from '@/components/modal/modal-context';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { peso } from '@/lib/format';
import { cn } from '@/lib/utils';

type Tone = 'danger' | 'warning';

interface Item {
    id: number;
    name: string;
    employee_no: string | null;
    biometric_employee_id: string | null;
    pay_model: string;
    regular_pay: number;
    late_deduction: number;
    undertime_deduction: number;
    absence_deduction: number;
    holiday_pay: number;
    rest_day_pay: number;
    leave_pay: number;
    overtime_pay: number;
    night_differential_pay: number;
    other_additions: number;
    other_deductions: number;
    government: number;
    sss_employee: number;
    philhealth_employee: number;
    pagibig_employee: number;
    gross_pay: number;
    net_pay: number;
    payable_days: number;
    payable_hours: number;
    overtime_hours: number;
    night_differential_hours: number;
    salary_adjustment_addition: number;
    salary_adjustment_deduction: number;
    attendance_deducted_from_gross: boolean;
    monthly_formula: { monthly_rate: number; divisor: number; paid_days: number } | null;
    daily_rate: number | null;
    government_schedule: string;
}

interface AuditRow {
    date: string;
    weekday: string;
    status_label: string;
    issues: { label: string; tone: Tone }[];
    severity: 'danger' | 'warning' | 'info' | 'clean';
    is_flexible: boolean;
    paid_hours: number;
    clock_hours: number;
    has_lunch: boolean;
    scheduled_in: string;
    scheduled_out: string;
    actual_in: string;
    actual_out: string;
    late_minutes: number;
    undertime_minutes: number;
    worked_hours: number;
    overtime_hours: number;
    payable_days: number;
    payable_hours: number;
    remarks: string | null;
}

interface Settlement {
    mode_label: string;
    unrecovered: number;
    lines: { label: string; monthly_due: number; this_cutoff: number }[];
    can_edit: boolean;
    values: {
        mode: string;
        sss_employee_reimbursement: string;
        philhealth_employee_reimbursement: string;
        pagibig_employee_reimbursement: string;
        reason: string;
    };
    caps: { sss: number; philhealth: number; pagibig: number };
}

interface Props {
    payroll: { id: number; payroll_number: string; status: string; cutoff_label: string; period_start: string | null; period_end: string | null; period_label: string };
    item: Item;
    attendanceRates: Record<string, number> | null;
    restDay: { qualified: boolean; by_exception: boolean; valid_log_days: number; minimum_valid_log_days: number; unpaid_count: number; deduction: number } | null;
    attendance: { worked_hours: number; late_minutes: number; undertime_minutes: number; holiday_worked: number; rest_day_worked: number };
    allowance: Record<string, number | string>;
    salaryDeductions: { name: string; schedule: string; balance_after: number | null; remarks: string | null; amount: number }[];
    adjustmentTags: { label: string; paid_this_cutoff: boolean; effect: string; date: string | null; amount: number; reason: string | null }[];
    settlement: Settlement | null;
    auditRows: AuditRow[];
    fileAdjustment: { people: PersonOption[]; types: Record<string, string>; urls: { submit: string; offsetProof: string } } | null;
    can: { recompute: boolean };
    urls: { back: string; recompute: string; settlement: string };
}

const ISSUE_TONE: Record<Tone, string> = {
    danger: 'border-destructive/40 text-destructive',
    warning: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
};

export default definePage<Props>({
    title: ({ item }) => item.name,
    description: ({ payroll, item }) => (
        <span className="flex flex-wrap gap-x-3 gap-y-1">
            <span>{payroll.cutoff_label}</span>
            {item.employee_no && <span>Employee No: {item.employee_no}</span>}
            {item.biometric_employee_id && <span>Bio ID: {item.biometric_employee_id}</span>}
            <span>Model: {item.pay_model}</span>
            <span>{payroll.payroll_number}</span>
        </span>
    ),
    actions: (props) => <ItemActions {...props} />,
    size: 'xl',
    Content: ItemContent,
});

function ItemActions(props: Props) {
    const { item, can, urls, fileAdjustment, payroll } = props;
    const modal = useModal();
    const [adjustmentOpen, setAdjustmentOpen] = useState(false);
    const [recomputing, setRecomputing] = useState(false);

    return (
        <>
            {fileAdjustment && (
                <Button onClick={() => setAdjustmentOpen(true)}>
                    <FilePlus2 />
                    File Adjustment
                </Button>
            )}
            {can.recompute && (
                <ConfirmAction
                    title={`Recompute ${item.name}?`}
                    description="Refreshes this employee's computation from the latest attendance and approved adjustments. Other employees in this payroll are not affected."
                    confirmLabel="Recompute"
                    onConfirm={() =>
                        router.post(urls.recompute, {}, modal.visit({
                            preserveScroll: true,
                            onStart: () => setRecomputing(true),
                            onFinish: () => setRecomputing(false),
                        }))
                    }
                    trigger={
                        <Button variant="outline" disabled={recomputing}>
                            <RefreshCw className={cn(recomputing && 'animate-spin')} />
                            Recompute
                        </Button>
                    }
                />
            )}
            {!modal.inModal && (
                <Button variant="outline" asChild>
                    <Link href={urls.back}>
                        <ArrowLeft />
                        Back to payroll
                    </Link>
                </Button>
            )}
            {fileAdjustment && (
                <Dialog open={adjustmentOpen} onOpenChange={setAdjustmentOpen}>
                    <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
                        <DialogHeader>
                            <DialogTitle>File adjustment for {item.name}</DialogTitle>
                            <DialogDescription>
                                Locked to this employee and to this payroll's cutoff ({payroll.period_label}). Saving automatically recomputes {item.name}'s
                                payroll item — no other employee in this payroll is affected.
                            </DialogDescription>
                        </DialogHeader>
                        <AdjustmentEditor
                            adjustment={null}
                            people={fileAdjustment.people}
                            types={fileAdjustment.types}
                            urls={fileAdjustment.urls}
                            lock={{
                                employeeBiometricId: fileAdjustment.people[0].employee_biometric_id,
                                minDate: payroll.period_start,
                                maxDate: payroll.period_end,
                            }}
                            extraData={{ recompute_payroll_item_id: item.id }}
                            submitLabel={`Save & recompute ${item.name}`}
                            onCancel={() => setAdjustmentOpen(false)}
                            onSuccess={() => setAdjustmentOpen(false)}
                            layout="dialog"
                        />
                    </DialogContent>
                </Dialog>
            )}
        </>
    );
}

function ItemContent(props: Props) {
    const { item, urls, attendanceRates: rates, restDay, allowance } = props;

    const attendanceLoss = item.late_deduction + item.undertime_deduction + item.absence_deduction;
    const additions = item.other_additions + item.holiday_pay + item.rest_day_pay + item.leave_pay + item.overtime_pay + item.night_differential_pay;
    const deductions = item.other_deductions + item.government;
    const lossDeducted = item.attendance_deducted_from_gross;

    const loans = props.salaryDeductions.reduce((sum, row) => sum + row.amount, 0);
    // Whatever "other deductions" holds beyond itemised loans and salary adjustments.
    const otherRemainder = Math.max(0, item.other_deductions - loans - item.salary_adjustment_deduction);

    const allowanceTotal = Number(allowance.total_per_cutoff ?? 0);
    const additionLines: { label: string; amount: number; hint?: string }[] = [
        { label: 'Regular allowance', amount: Number(allowance.regular_per_cutoff ?? 0), hint: `Monthly ${peso(Number(allowance.monthly_allowance ?? 0))} · ${allowance.allowance_schedule}` },
        { label: 'SIM / load allowance', amount: Number(allowance.sim_load_per_cutoff ?? 0), hint: `Monthly ${peso(Number(allowance.monthly_sim_load ?? 0))} · ${allowance.sim_load_schedule}` },
        { label: 'Holiday pay', amount: item.holiday_pay },
        { label: 'Rest day pay', amount: item.rest_day_pay },
        { label: 'Leave pay', amount: item.leave_pay },
        { label: 'Approved overtime', amount: item.overtime_pay, hint: `${item.overtime_hours.toFixed(2)} hr` },
        { label: 'Night differential', amount: item.night_differential_pay, hint: `${item.night_differential_hours.toFixed(2)} hr, 10PM-6AM` },
        { label: 'Salary adjustment', amount: item.salary_adjustment_addition },
    ];
    // Allowance total may include parts not itemised above.
    const allowanceRemainder = allowanceTotal - additionLines[0].amount - additionLines[1].amount;
    if (allowanceRemainder > 0.005) additionLines.splice(2, 0, { label: 'Other allowance', amount: allowanceRemainder });
    const shownAdditions = additionLines.filter((line) => Math.abs(line.amount) > 0.005);
    const zeroAdditions = additionLines.filter((line) => Math.abs(line.amount) <= 0.005 && !line.label.includes('allowance'));

    return (
        <>
            <PayFlow
                steps={[
                    {
                        label: 'Base pay',
                        value: item.regular_pay,
                        hint: item.monthly_formula
                            ? `${peso(item.monthly_formula.monthly_rate)} ÷ ${item.monthly_formula.divisor} × ${item.monthly_formula.paid_days} days`
                            : item.daily_rate
                              ? `${peso(item.daily_rate)} / day`
                              : `${item.payable_hours.toFixed(2)} payable hr`,
                    },
                    { sign: '−', label: 'Attendance loss', value: attendanceLoss, tone: 'negative', muted: !lossDeducted, hint: lossDeducted ? undefined : 'Already in hours' },
                    { sign: '+', label: 'Additions', value: additions, tone: 'positive' },
                    { sign: '=', label: 'Gross pay', value: item.gross_pay, strong: true },
                    { sign: '−', label: 'Deductions', value: deductions, tone: 'negative', hint: 'Gov. + loans' },
                    { sign: '=', label: 'Net pay', value: item.net_pay, strong: true, final: true },
                ]}
                note={
                    lossDeducted
                        ? undefined
                        : 'Payable Hours × Hourly Rate = Base pay. Attendance loss is shown for audit only and is not deducted twice.'
                }
            />

            <div className="grid gap-4 lg:grid-cols-3">
                <Breakdown title="Attendance loss" total={attendanceLoss} negative muted={!lossDeducted} note={lossDeducted ? undefined : 'Audit only — already reflected in payable hours.'}>
                    <Line
                        label="Late"
                        hint={rates ? `${rates.late_minutes.toFixed(0)} min × ${peso(rates.late_rate)}` : `${props.attendance.late_minutes} min`}
                        amount={item.late_deduction}
                        negative={lossDeducted}
                    />
                    <Line
                        label="Undertime"
                        hint={rates ? `${rates.undertime_minutes.toFixed(0)} min × ${peso(rates.undertime_rate)}` : `${props.attendance.undertime_minutes} min`}
                        amount={item.undertime_deduction}
                        negative={lossDeducted}
                    />
                    <Line label="Absence" hint={rates ? `${rates.absent_days.toFixed(0)} day(s) × ${peso(rates.absence_rate)}` : undefined} amount={item.absence_deduction} negative={lossDeducted} />
                    {restDay && (
                        <div className={cn('mt-2 rounded-md border px-3 py-2 text-xs', restDay.qualified ? 'bg-muted/40' : 'border-destructive/40 bg-destructive/5 text-destructive')}>
                            <span className="font-medium">Rest day {restDay.qualified ? 'qualified' : 'not qualified'}</span> · {restDay.valid_log_days} / {restDay.minimum_valid_log_days} valid
                            log days.{' '}
                            {restDay.by_exception
                                ? 'Approved adjustment/leave exception keeps the unworked rest day paid.'
                                : !restDay.qualified && `${restDay.unpaid_count} unworked rest day(s) unpaid (${peso(restDay.deduction)} included above).`}
                        </div>
                    )}
                </Breakdown>

                <Breakdown title="Additions" total={additions} note={zeroAdditions.length ? `None this cutoff: ${zeroAdditions.map((line) => line.label.toLowerCase()).join(', ')}.` : undefined}>
                    {shownAdditions.length === 0 && <p className="py-2 text-sm text-muted-foreground">No additions this cutoff.</p>}
                    {shownAdditions.map((line) => (
                        <Line key={line.label} label={line.label} hint={line.hint} amount={line.amount} />
                    ))}
                </Breakdown>

                <Breakdown title="Deductions" total={deductions} negative note={`Government schedule: ${item.government_schedule}`}>
                    <Line label="SSS" amount={item.sss_employee} negative />
                    <Line label="PhilHealth" amount={item.philhealth_employee} negative />
                    <Line label="Pag-IBIG" amount={item.pagibig_employee} negative />
                    {props.salaryDeductions.map((row, index) => (
                        <Line
                            key={index}
                            label={row.name}
                            hint={[row.schedule, row.balance_after !== null ? `balance after ${peso(row.balance_after)}` : null, row.remarks].filter(Boolean).join(' · ')}
                            amount={row.amount}
                            negative
                        />
                    ))}
                    {item.salary_adjustment_deduction > 0 && <Line label="Salary adjustment" amount={item.salary_adjustment_deduction} negative />}
                    {otherRemainder > 0.005 && <Line label="Other deductions" amount={otherRemainder} negative />}
                </Breakdown>
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <Card className="gap-3">
                    <CardHeader>
                        <CardTitle>Attendance</CardTitle>
                    </CardHeader>
                    <CardContent className="grid grid-cols-2 gap-2">
                        <Stat label="Payable" value={`${item.payable_days.toFixed(2)} days`} hint={`${item.payable_hours.toFixed(2)} hr`} />
                        <Stat label="Worked" value={`${props.attendance.worked_hours.toFixed(2)} hr`} />
                        <Stat label="Approved OT" value={`${item.overtime_hours.toFixed(2)} hr`} />
                        <Stat label="Night diff." value={`${item.night_differential_hours.toFixed(2)} hr`} />
                        <Stat label="Holiday worked" value={String(props.attendance.holiday_worked)} />
                        <Stat label="Rest day worked" value={String(props.attendance.rest_day_worked)} />
                    </CardContent>
                </Card>

                <Card className="gap-3 lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Adjustments applied / paid this cutoff</CardTitle>
                        <CardDescription>
                            {props.adjustmentTags.length === 0
                                ? 'No approved payroll adjustments or premium authorizations are applied to this cutoff.'
                                : `${props.adjustmentTags.length} adjustment${props.adjustmentTags.length === 1 ? '' : 's'}`}
                        </CardDescription>
                    </CardHeader>
                    {props.adjustmentTags.length > 0 && (
                        <CardContent className="grid gap-2 text-sm sm:grid-cols-2">
                            {props.adjustmentTags.map((tag, index) => (
                                <div key={index} className="rounded-lg border p-3">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="font-medium">{tag.label}</div>
                                        {tag.amount !== 0 && (
                                            <span className={cn('font-medium whitespace-nowrap tabular-nums', tag.amount > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-destructive')}>
                                                {tag.amount > 0 ? '+ ' : ''}
                                                {peso(tag.amount)}
                                            </span>
                                        )}
                                    </div>
                                    <div className="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                                        <Badge variant={tag.paid_this_cutoff ? 'secondary' : 'outline'}>{tag.paid_this_cutoff ? 'Paid this cutoff' : 'Applied'}</Badge>
                                        <span>{tag.effect}</span>
                                        {tag.date && <span>· {tag.date}</span>}
                                    </div>
                                    {tag.reason && <div className="mt-1 text-xs text-muted-foreground">{tag.reason}</div>}
                                </div>
                            ))}
                        </CardContent>
                    )}
                </Card>
            </div>

            {props.settlement && <SettlementCard settlement={props.settlement} url={urls.settlement} />}

            <AuditTable rows={props.auditRows} />
        </>
    );
}

interface FlowStep {
    label: string;
    value: number;
    sign?: '+' | '−' | '=';
    hint?: string;
    tone?: 'positive' | 'negative';
    strong?: boolean;
    final?: boolean;
    /** Shown for reference but not part of the math (hourly model). */
    muted?: boolean;
}

/** Base → − loss → + additions → = gross → − deductions → = net, each figure once. */
function PayFlow({ steps, note }: { steps: FlowStep[]; note?: string }) {
    return (
        <Card className="gap-0 py-0">
            <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6">
                {steps.map((step) => (
                    <div
                        key={step.label}
                        className={cn(
                            'relative border-b px-5 py-4 xl:border-r xl:border-b-0 xl:last:border-r-0',
                            step.strong && 'bg-muted/40',
                            step.final && 'bg-emerald-50 dark:bg-emerald-950/30',
                            step.muted && 'opacity-60',
                        )}
                    >
                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                            {step.sign && <span className="flex size-4 items-center justify-center rounded-full border text-[10px] font-semibold">{step.sign}</span>}
                            {step.label}
                        </div>
                        <div
                            className={cn(
                                'mt-1 text-lg font-semibold tabular-nums',
                                step.tone === 'negative' && 'text-destructive',
                                step.tone === 'positive' && step.value > 0 && 'text-emerald-700 dark:text-emerald-400',
                                step.final && 'text-xl text-emerald-700 dark:text-emerald-400',
                                step.muted && 'line-through',
                            )}
                        >
                            {peso(step.value)}
                        </div>
                        {step.hint && <div className="truncate text-xs text-muted-foreground" title={step.hint}>{step.hint}</div>}
                    </div>
                ))}
            </div>
            {note && <div className="border-t px-5 py-2 text-xs text-muted-foreground">{note}</div>}
        </Card>
    );
}

function Breakdown({ title, total, negative, muted, note, children }: { title: string; total: number; negative?: boolean; muted?: boolean; note?: string; children: ReactNode }) {
    return (
        <Card className="gap-3">
            <CardHeader className="flex flex-row items-baseline justify-between gap-3">
                <CardTitle>{title}</CardTitle>
                <span className={cn('font-semibold tabular-nums', negative && total > 0 && !muted && 'text-destructive', muted && 'text-muted-foreground')}>
                    {negative && total > 0 ? '− ' : ''}
                    {peso(total)}
                </span>
            </CardHeader>
            <CardContent className="grid content-start">
                {children}
                {note && <p className="mt-2 text-xs text-muted-foreground">{note}</p>}
            </CardContent>
        </Card>
    );
}

function Line({ label, hint, amount, negative }: { label: string; hint?: string; amount: number; negative?: boolean }) {
    const zero = Math.abs(amount) <= 0.005;

    return (
        <div className="flex items-start justify-between gap-4 border-b py-2 text-sm last:border-0">
            <div className="min-w-0">
                <div className={cn(zero && 'text-muted-foreground')}>{label}</div>
                {hint && <div className="text-xs text-muted-foreground">{hint}</div>}
            </div>
            <span className={cn('whitespace-nowrap tabular-nums', zero ? 'text-muted-foreground' : negative && 'text-destructive')}>
                {negative && !zero ? '− ' : ''}
                {peso(amount)}
            </span>
        </div>
    );
}

function Stat({ label, value, hint }: { label: string; value: string; hint?: string }) {
    return (
        <div className="rounded-lg border px-3 py-2">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="font-semibold tabular-nums">{value}</div>
            {hint && <div className="text-xs text-muted-foreground">{hint}</div>}
        </div>
    );
}

function Pair({ label, value, strong }: { label: string; value: string; strong?: boolean }) {
    return (
        <div className={cn('flex justify-between gap-4', strong && 'border-t pt-2 font-semibold')}>
            <span className={cn(!strong && 'text-muted-foreground')}>{label}</span>
            <span className="tabular-nums">{value}</span>
        </div>
    );
}

function SettlementCard({ settlement, url }: { settlement: Settlement; url: string }) {
    const modal = useModal();
    const form = useForm(settlement.values);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(url, modal.visit({ preserveScroll: true }));
    };

    const reimbursement = (field: 'sss_employee_reimbursement' | 'philhealth_employee_reimbursement' | 'pagibig_employee_reimbursement', label: string, cap: number) => (
        <div className="grid gap-1.5">
            <Label htmlFor={`settlement-${field}`}>{label}</Label>
            <Input
                id={`settlement-${field}`}
                type="number"
                step="0.01"
                min={0}
                max={cap.toFixed(2)}
                value={form.data[field]}
                onChange={(event) => form.setData(field, event.target.value)}
                aria-invalid={Boolean(form.errors[field])}
            />
            <p className="text-xs text-muted-foreground">Max manual credit: {peso(cap)}</p>
            {form.errors[field] && <p className="text-xs text-destructive">{form.errors[field]}</p>}
        </div>
    );

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex flex-wrap items-center gap-2">
                    Government benefit settlement
                    <Badge variant="secondary">{settlement.mode_label}</Badge>
                </CardTitle>
                <CardDescription>
                    Controls payroll cash collection only. The exact monthly SSS / PhilHealth / Pag-IBIG liability remains in Benefits Records.
                </CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4">
                {settlement.unrecovered > 0 && (
                    <Alert>
                        <AlertTitle>Employee share not collected from payroll</AlertTitle>
                        <AlertDescription>
                            {peso(settlement.unrecovered)} is currently tracked as employer-advanced / unrecovered employee share. Net pay is protected from
                            becoming negative.
                        </AlertDescription>
                    </Alert>
                )}
                <div className="grid gap-3 sm:grid-cols-3">
                    {settlement.lines.map((line) => (
                        <div key={line.label} className="rounded-lg border p-3 text-sm">
                            <div className="text-xs text-muted-foreground">{line.label}</div>
                            <Pair label="Monthly EE liability" value={peso(line.monthly_due)} />
                            <Pair label="This cutoff cash" value={peso(line.this_cutoff)} />
                        </div>
                    ))}
                </div>
                {settlement.can_edit ? (
                    <form onSubmit={submit} className="grid gap-4">
                        <div className="grid gap-1.5 md:max-w-xl">
                            <Label htmlFor="settlement-mode">Settlement action</Label>
                            <Select value={form.data.mode} onValueChange={(mode) => form.setData('mode', mode)}>
                                <SelectTrigger id="settlement-mode" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="auto_cap">Auto Cap — collect only what net pay can cover</SelectItem>
                                    <SelectItem value="employer_advance">Employer Advance — do not collect positive EE share this cutoff</SelectItem>
                                    <SelectItem value="collect_full">Collect Full — reject if it makes net pay negative</SelectItem>
                                </SelectContent>
                            </Select>
                            <p className="text-xs text-muted-foreground">
                                Recommended for resigned/no-pay cases: Employer Advance. The government liability is still retained for remittance/accounting.
                            </p>
                            {form.errors.mode && <p className="text-xs text-destructive">{form.errors.mode}</p>}
                        </div>
                        <div className="grid gap-3 sm:grid-cols-3">
                            {reimbursement('sss_employee_reimbursement', 'SSS reimbursement / credit', settlement.caps.sss)}
                            {reimbursement('philhealth_employee_reimbursement', 'PhilHealth reimbursement / credit', settlement.caps.philhealth)}
                            {reimbursement('pagibig_employee_reimbursement', 'Pag-IBIG reimbursement / credit', settlement.caps.pagibig)}
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Manual reimbursement is capped after any automatic monthly true-up credit, so the employee cannot be refunded more than was actually
                            withheld. It is a payroll credit, not a cancellation of statutory contributions.
                        </p>
                        <div className="grid gap-1.5">
                            <Label htmlFor="settlement-reason">HR / payroll reason</Label>
                            <Textarea
                                id="settlement-reason"
                                rows={2}
                                required
                                placeholder="Example: Employee separated before 2nd cutoff; company will advance remaining employee share and reimburse prior cutoff EE deductions as approved by HR/accounting."
                                value={form.data.reason}
                                onChange={(event) => form.setData('reason', event.target.value)}
                                aria-invalid={Boolean(form.errors.reason)}
                            />
                            {form.errors.reason && <p className="text-xs text-destructive">{form.errors.reason}</p>}
                        </div>
                        <div>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? <LoaderCircle className="animate-spin" /> : <Save />}
                                Save & recalculate
                            </Button>
                        </div>
                    </form>
                ) : (
                    settlement.values.reason && (
                        <div className="text-sm">
                            <div className="text-xs text-muted-foreground">Reason</div>
                            <div>{settlement.values.reason}</div>
                        </div>
                    )
                )}
            </CardContent>
        </Card>
    );
}

function AuditTable({ rows }: { rows: AuditRow[] }) {
    const issueCount = rows.filter((row) => row.issues.length > 0).length;
    const sum = (key: keyof AuditRow) => rows.reduce((total, row) => total + Number(row[key] ?? 0), 0);

    return (
        <Card className="gap-0 py-0">
            <CardHeader className="border-b py-4">
                <CardTitle>Attendance audit review</CardTitle>
                <CardDescription>Red rows need checking. Yellow rows have late, undertime, or partial-day issues.</CardDescription>
                <div className="mt-3 flex flex-wrap gap-2 text-sm">
                    <Chip label="Total days" value={String(rows.length)} />
                    <Chip label="Clean records" value={String(rows.length - issueCount)} />
                    <Chip label="Needs checking" value={String(issueCount)} alert={issueCount > 0} />
                </div>
            </CardHeader>
            <CardContent className="px-0">
                <Table className="min-w-[1300px] text-xs">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="pl-6">Date</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Issue detected</TableHead>
                            <TableHead>Schedule</TableHead>
                            <TableHead>Actual log</TableHead>
                            <TableHead className="text-center">Late</TableHead>
                            <TableHead className="text-center">UT</TableHead>
                            <TableHead className="text-center">Worked</TableHead>
                            <TableHead className="text-center">OT</TableHead>
                            <TableHead className="text-center">Payable</TableHead>
                            <TableHead className="pr-6">Remarks</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {rows.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={11} className="py-10 text-center text-muted-foreground">
                                    No daily attendance summary found for this employee.
                                </TableCell>
                            </TableRow>
                        )}
                        {rows.map((row, index) => (
                            <TableRow
                                key={index}
                                className={cn(
                                    'border-l-4',
                                    row.severity === 'danger' && 'border-l-destructive bg-destructive/5',
                                    row.severity === 'warning' && 'border-l-amber-400 bg-amber-50/60 dark:bg-amber-950/20',
                                    row.severity === 'info' && 'border-l-sky-400',
                                    row.severity === 'clean' && 'border-l-emerald-400',
                                )}
                            >
                                <TableCell className="pl-6">
                                    <div className="font-medium">{row.date}</div>
                                    <div className="text-muted-foreground">{row.weekday}</div>
                                </TableCell>
                                <TableCell>{row.status_label}</TableCell>
                                <TableCell>
                                    <div className="flex flex-wrap gap-1">
                                        {row.issues.length === 0 && <span className="text-muted-foreground">Clean</span>}
                                        {row.issues.map((issue) => (
                                            <Badge key={issue.label} variant="outline" className={ISSUE_TONE[issue.tone]}>
                                                {issue.label}
                                            </Badge>
                                        ))}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {row.is_flexible ? (
                                        <>
                                            <div className="font-medium">Flexible shift</div>
                                            <div className="text-muted-foreground">
                                                {row.clock_hours} clock hr(s) · {row.paid_hours} paid hr(s){row.has_lunch ? ' + lunch' : ''}
                                            </div>
                                        </>
                                    ) : (
                                        <div className="tabular-nums">
                                            In {row.scheduled_in} · Out {row.scheduled_out}
                                        </div>
                                    )}
                                </TableCell>
                                <TableCell className="tabular-nums">
                                    In {row.actual_in} · Out {row.actual_out}
                                </TableCell>
                                <TableCell className="text-center tabular-nums">{row.late_minutes} min</TableCell>
                                <TableCell className="text-center tabular-nums">{row.undertime_minutes} min</TableCell>
                                <TableCell className="text-center tabular-nums">{row.worked_hours.toFixed(2)} hr</TableCell>
                                <TableCell className="text-center tabular-nums">{row.overtime_hours.toFixed(2)} hr</TableCell>
                                <TableCell className="text-center tabular-nums">
                                    <div>{row.payable_days.toFixed(2)}</div>
                                    <div className="text-muted-foreground">{row.payable_hours.toFixed(2)} hr</div>
                                </TableCell>
                                <TableCell className="pr-6 text-muted-foreground">
                                    <div className="w-72 whitespace-normal">{row.remarks ?? '—'}</div>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                    {rows.length > 0 && (
                        <TableFooter>
                            <TableRow>
                                <TableCell colSpan={5} className="pl-6 text-right font-medium">
                                    Total
                                </TableCell>
                                <TableCell className="text-center tabular-nums">{sum('late_minutes')} min</TableCell>
                                <TableCell className="text-center tabular-nums">{sum('undertime_minutes')} min</TableCell>
                                <TableCell className="text-center tabular-nums">{sum('worked_hours').toFixed(2)} hr</TableCell>
                                <TableCell className="text-center tabular-nums">{sum('overtime_hours').toFixed(2)} hr</TableCell>
                                <TableCell className="text-center tabular-nums">
                                    {sum('payable_days').toFixed(2)} day
                                    <div className="text-muted-foreground">{sum('payable_hours').toFixed(2)} hr</div>
                                </TableCell>
                                <TableCell />
                            </TableRow>
                        </TableFooter>
                    )}
                </Table>
            </CardContent>
        </Card>
    );
}

function Chip({ label, value, alert }: { label: string; value: string; alert?: boolean }): ReactNode {
    return (
        <div className="rounded-lg border px-3 py-2">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className={cn('font-semibold tabular-nums', alert && 'text-amber-700 dark:text-amber-400')}>{value}</div>
        </div>
    );
}
