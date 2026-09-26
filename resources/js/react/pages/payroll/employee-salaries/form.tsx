import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, LoaderCircle, Plus, Save, Trash2 } from 'lucide-react';
import { useMemo, useState, type FormEvent, type ReactNode } from 'react';
import { EmployeeCombobox, type PersonOption } from '@/components/employee-combobox';
import { useModal } from '@/components/modal/modal-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { peso } from '@/lib/format';
import {
    estimatedLastPayment,
    fixedDeductionToCutoff,
    monthlyBasicSalary,
    monthlyToCutoff,
    num,
    pagibigEmployeeShare,
    philhealthEmployeeShare,
    salaryRates,
    sssBreakdown,
    type CutoffKey,
    type SssRules,
} from '@/lib/salary-preview';

type SalaryPerson = PersonOption & { paid_work_hours: number; workday_label: string };

interface OtherDeduction {
    name: string;
    total_amount: string;
    payment_amount: string;
    deduction_schedule: string;
    start_date: string;
    remarks: string;
}

type Values = Record<string, string | number | boolean | null | OtherDeduction[]> & {
    employee_biometric_id: number | null;
    other_deductions: OtherDeduction[];
};

interface Props {
    salary: { id: number; name: string } | null;
    values: Values;
    people: SalaryPerson[];
    workday: { paid_hours: number; label: string };
    scheduleOptions: Record<string, string>;
    cutoffLabels: { first: string; second: string };
    sssRules: SssRules;
    sssCircular: { number: string; effective: string };
    urls: { index: string; submit: string };
}

const LOANS = [
    { prefix: 'sss_loan', label: 'SSS Loan', help: 'SSS salary loan or other SSS deduction.' },
    { prefix: 'pagibig_loan', label: 'Pag-IBIG Loan', help: 'Pag-IBIG MPL, calamity loan, or other Pag-IBIG deduction.' },
    { prefix: 'philhealth_loan', label: 'PhilHealth Loan', help: 'Use only if your company tracks PhilHealth-related deductions.' },
    { prefix: 'cash_advance', label: 'Cash Advance / Vale', help: 'Cash advance deduction from payroll.' },
    { prefix: 'other_loan', label: 'Other Loan', help: 'Company loan or other employee deduction.' },
];

const BLANK_DEDUCTION: OtherDeduction = { name: '', total_amount: '0', payment_amount: '0', deduction_schedule: 'none', start_date: '', remarks: '' };

export default definePage<Props>({
    title: ({ salary }) => (salary ? `Edit salary — ${salary.name}` : 'Add employee salary'),
    description: () => 'Rates, government schedules, allowances, loans and deductions used by payroll generation.',
    actions: (props) => <BackLink {...props} />,
    // Long form; the modal widens itself to fit the loan tables.
    size: 'xl',
    Content: EmployeeSalaryForm,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.index}>
                <ArrowLeft />
                Back
            </Link>
        </Button>
    );
}

function EmployeeSalaryForm({ salary, values, people, workday, scheduleOptions, cutoffLabels, sssRules, sssCircular, urls }: Props) {
    const isEdit = salary !== null;
    const modal = useModal();
    const form = useForm<Values>(values);
    const { data, errors, processing } = form;
    const [hours, setHours] = useState({ paid: workday.paid_hours, label: workday.label });

    const str = (key: string) => String(data[key] ?? '');
    const set = (key: string, value: string | boolean | number | null | OtherDeduction[]) => {
        form.setData((current) => ({ ...current, [key]: value }));
        form.clearErrors(key as never);
    };
    const error = (key: string) => (errors as Record<string, string>)[key];

    const preview = useMemo(() => {
        const rateType = str('rate_type');
        const basic = num(data.basic_salary);
        const rates = salaryRates(rateType, basic, hours.paid);
        const monthlyBasic = monthlyBasicSalary(rateType, basic);
        const sss = sssBreakdown(monthlyBasic, sssRules);
        const pagibig = pagibigEmployeeShare(monthlyBasic);
        const philhealth = philhealthEmployeeShare(monthlyBasic);

        const cutoff = (key: CutoffKey) => {
            const government =
                monthlyToCutoff(sss.employee, str('sss_contribution_cutoff'), key) +
                monthlyToCutoff(pagibig, str('pagibig_contribution_cutoff'), key) +
                monthlyToCutoff(philhealth, str('philhealth_contribution_cutoff'), key);
            const allowance =
                monthlyToCutoff(num(data.allowance), str('allowance_release_schedule'), key) +
                monthlyToCutoff(num(data.sim_load_allowance), str('sim_load_release_schedule'), key);
            const loans =
                LOANS.reduce((sum, loan) => sum + fixedDeductionToCutoff(num(data[`${loan.prefix}_payment_amount`]), str(`${loan.prefix}_deduction_schedule`), key), 0) +
                data.other_deductions.reduce((sum, row) => sum + fixedDeductionToCutoff(num(row.payment_amount), row.deduction_schedule, key), 0);
            const gross = monthlyBasic / 2 + allowance;
            const deductions = government + loans;

            return { gross, deductions, net: gross - deductions };
        };

        return { rates, monthlyBasic, sss, pagibig, philhealth, first: cutoff('first'), second: cutoff('second') };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data, hours.paid, sssRules]);

    const choosePerson = (person: PersonOption | null) => {
        const chosen = person as SalaryPerson | null;
        form.setData((current) => ({
            ...current,
            employee_biometric_id: chosen?.employee_biometric_id ?? null,
            employee_no: chosen?.employee_no ?? '',
            employee_name: chosen?.employee_name ?? '',
            crosschex_id: chosen?.crosschex_id ?? '',
            biometric_employee_id: chosen?.biometric_employee_id ?? '',
        }));
        if (chosen) setHours({ paid: chosen.paid_work_hours || 8, label: chosen.workday_label });
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (isEdit) {
            form.put(urls.submit, modal.visit({ preserveScroll: true }));
        } else {
            form.post(urls.submit, modal.visit({ preserveScroll: true }));
        }
    };

    const updateDeduction = (index: number, changes: Partial<OtherDeduction>) =>
        set('other_deductions', data.other_deductions.map((row, i) => (i === index ? { ...row, ...changes } : row)));

    return (
            <form onSubmit={submit} className="grid gap-4 lg:gap-6">
                <Section title="Employee">
                    <div className="grid gap-4 md:grid-cols-3">
                        {!isEdit && (
                            <Field id="salary-employee" label="Biometrics employee" error={error('employee_biometric_id')} className="md:col-span-3 md:max-w-xl">
                                <EmployeeCombobox
                                    id="salary-employee"
                                    people={people}
                                    value={data.employee_biometric_id}
                                    onChange={choosePerson}
                                    invalid={Boolean(error('employee_biometric_id'))}
                                />
                            </Field>
                        )}
                        <Field id="salary-employee-no" label="Employee no" error={error('employee_no')}>
                            <Input id="salary-employee-no" value={str('employee_no')} readOnly />
                        </Field>
                        <Field id="salary-employee-name" label="Employee name" error={error('employee_name')} className="md:col-span-2">
                            <Input id="salary-employee-name" value={str('employee_name')} readOnly />
                        </Field>
                    </div>
                </Section>

                <Section
                    title="Rate computation"
                    description={`OT, late, undertime, and absent deductions are computed from basic salary using the employee's saved Work Schedule. Current preview: ${hours.label}.`}
                >
                    <div className="grid gap-4 md:grid-cols-4">
                        <Field id="salary-rate-type" label="Rate type" error={error('rate_type')}>
                            <Select value={str('rate_type')} onValueChange={(value) => set('rate_type', value)}>
                                <SelectTrigger id="salary-rate-type" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="daily">Daily</SelectItem>
                                    <SelectItem value="monthly">Monthly</SelectItem>
                                </SelectContent>
                            </Select>
                        </Field>
                        <Field id="salary-basic" label="Basic salary" error={error('basic_salary')}>
                            <Input id="salary-basic" type="number" step="0.01" min={0} required value={str('basic_salary')} onChange={(event) => set('basic_salary', event.target.value)} />
                        </Field>
                        <ReadOnlyAmount label="OT rate / hour" value={preview.rates.hourlyRate.toFixed(2)} />
                        <ReadOnlyAmount label="Absent / day" value={preview.rates.dailyRate.toFixed(2)} />
                        <ReadOnlyAmount label="Late deduction / minute" value={preview.rates.perMinute.toFixed(4)} />
                        <ReadOnlyAmount label="Undertime deduction / minute" value={preview.rates.perMinute.toFixed(4)} />
                        <div className="grid gap-1.5 md:col-span-2">
                            <Label>Paid night differential</Label>
                            <RadioGroup
                                className="flex gap-4"
                                value={data.paid_night_differential ? '1' : '0'}
                                onValueChange={(value) => set('paid_night_differential', value === '1')}
                            >
                                <label className="flex items-center gap-2 text-sm">
                                    <RadioGroupItem value="1" /> Yes
                                </label>
                                <label className="flex items-center gap-2 text-sm">
                                    <RadioGroupItem value="0" /> No
                                </label>
                            </RadioGroup>
                            <p className="text-xs text-muted-foreground">Whether Night Differential (10PM-6AM) pay is computed for this employee during payroll generation.</p>
                        </div>
                        <div className="grid gap-1.5 md:col-span-2">
                            <Label>Day off</Label>
                            <RadioGroup
                                className="flex gap-4"
                                value={data.paid_day_off ? '1' : '0'}
                                onValueChange={(value) => set('paid_day_off', value === '1')}
                            >
                                <label className="flex items-center gap-2 text-sm">
                                    <RadioGroupItem value="1" /> Paid
                                </label>
                                <label className="flex items-center gap-2 text-sm">
                                    <RadioGroupItem value="0" /> Not paid
                                </label>
                            </RadioGroup>
                            <p className="text-xs text-muted-foreground">
                                {data.paid_day_off
                                    ? data.rate_type === 'monthly'
                                        ? 'Paid: fixed monthly salary ÷ 2 per cutoff; the day off is already included.'
                                        : 'Paid: each unworked day off is paid 1 day when the cutoff has at least 3 days with valid biometric logs (or an approved leave/adjustment).'
                                    : 'Not paid: paid only for days actually worked (working the 31st counts).' +
                                      (data.rate_type === 'monthly' ? ' Monthly rate becomes daily rate × days worked (monthly × 12 ÷ 365 per day).' : '')}
                            </p>
                        </div>
                    </div>
                </Section>

                <Section
                    title="Government contributions"
                    description={`SSS uses Circular No. ${sssCircular.number}, effective ${sssCircular.effective}. The employee share is selected from the official Monthly Salary Credit bracket, not from a flat estimate.`}
                >
                    <div className="grid gap-4">
                        <div className="grid gap-4 md:grid-cols-3">
                            <ScheduleField id="sss_contribution_cutoff" label="SSS deduction schedule" value={str('sss_contribution_cutoff')} options={scheduleOptions} onChange={set} />
                            <ScheduleField id="pagibig_contribution_cutoff" label="Pag-IBIG deduction schedule" value={str('pagibig_contribution_cutoff')} options={scheduleOptions} onChange={set} />
                            <ScheduleField id="philhealth_contribution_cutoff" label="PhilHealth deduction schedule" value={str('philhealth_contribution_cutoff')} options={scheduleOptions} onChange={set} />
                        </div>
                        <div className="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
                            <PreviewBox label="SSS monthly salary credit" value={preview.sss.msc} hint="Official bracket basis" />
                            <PreviewBox label="SSS employee share (5%)" value={preview.sss.employee} hint="Payroll deduction" />
                            <PreviewBox label="SSS employer share (10%)" value={preview.sss.employer} hint="Excludes EC" />
                            <PreviewBox label="Employer EC" value={preview.sss.ec} hint="Employer-only" />
                            <PreviewBox label="Total SSS contribution" value={preview.sss.total} />
                            <PreviewBox label="Pag-IBIG employee / month" value={preview.pagibig} />
                            <PreviewBox label="PhilHealth employee / month" value={preview.philhealth} />
                        </div>
                    </div>
                </Section>

                <Section title="Allowances">
                    <div className="grid gap-4 md:grid-cols-4">
                        <Field id="salary-allowance" label="Regular allowance" error={error('allowance')}>
                            <Input id="salary-allowance" type="number" step="0.01" min={0} value={str('allowance')} onChange={(event) => set('allowance', event.target.value)} />
                        </Field>
                        <ScheduleField id="allowance_release_schedule" label="Allowance release" value={str('allowance_release_schedule')} options={scheduleOptions} onChange={set} />
                        <Field id="salary-sim" label="SIM / cellular load allowance" error={error('sim_load_allowance')}>
                            <Input id="salary-sim" type="number" step="0.01" min={0} value={str('sim_load_allowance')} onChange={(event) => set('sim_load_allowance', event.target.value)} />
                        </Field>
                        <ScheduleField id="sim_load_release_schedule" label="SIM load release" value={str('sim_load_release_schedule')} options={scheduleOptions} onChange={set} />
                    </div>
                </Section>

                <Section title="Loans and cash advance" description="Input total amount, deduction amount per selected cutoff, schedule, and start date.">
                    <Table className="min-w-[900px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Deduction type</TableHead>
                                <TableHead>Total amount</TableHead>
                                <TableHead>Deduction amount</TableHead>
                                <TableHead>Schedule</TableHead>
                                <TableHead>Start date</TableHead>
                                <TableHead>Estimated last payment</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {LOANS.map((loan) => (
                                <TableRow key={loan.prefix}>
                                    <TableCell className="whitespace-normal">
                                        <div className="font-medium">{loan.label}</div>
                                        <div className="text-xs text-muted-foreground">{loan.help}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Input aria-label={`${loan.label} total amount`} type="number" step="0.01" min={0} className="w-32" value={str(`${loan.prefix}_total_amount`)} onChange={(event) => set(`${loan.prefix}_total_amount`, event.target.value)} />
                                    </TableCell>
                                    <TableCell>
                                        <Input aria-label={`${loan.label} deduction amount`} type="number" step="0.01" min={0} className="w-32" value={str(`${loan.prefix}_payment_amount`)} onChange={(event) => set(`${loan.prefix}_payment_amount`, event.target.value)} />
                                    </TableCell>
                                    <TableCell>
                                        <ScheduleSelect ariaLabel={`${loan.label} schedule`} value={str(`${loan.prefix}_deduction_schedule`)} options={scheduleOptions} onChange={(value) => set(`${loan.prefix}_deduction_schedule`, value)} />
                                    </TableCell>
                                    <TableCell>
                                        <Input aria-label={`${loan.label} start date`} type="date" className="w-40" value={str(`${loan.prefix}_start_date`)} onChange={(event) => set(`${loan.prefix}_start_date`, event.target.value)} />
                                    </TableCell>
                                    <TableCell className="text-sm tabular-nums">
                                        {estimatedLastPayment(num(data[`${loan.prefix}_total_amount`]), num(data[`${loan.prefix}_payment_amount`]), str(`${loan.prefix}_deduction_schedule`), str(`${loan.prefix}_start_date`))}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Section>

                <Section
                    title="Additional other loans / deductions"
                    description="Extra deductions such as uniform deduction, damage charge, cooperative loan, or other payroll deduction."
                    action={
                        <Button type="button" variant="outline" size="sm" onClick={() => set('other_deductions', [...data.other_deductions, { ...BLANK_DEDUCTION }])}>
                            <Plus />
                            Add deduction
                        </Button>
                    }
                >
                    {data.other_deductions.length === 0 ? (
                        <p className="py-4 text-center text-sm text-muted-foreground">No additional other deduction added yet.</p>
                    ) : (
                        <Table className="min-w-[1100px]">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Total amount</TableHead>
                                    <TableHead>Deduction / cutoff</TableHead>
                                    <TableHead>Schedule</TableHead>
                                    <TableHead>Start date</TableHead>
                                    <TableHead>Estimated last payment</TableHead>
                                    <TableHead>Remarks</TableHead>
                                    <TableHead />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.other_deductions.map((row, index) => (
                                    <TableRow key={index}>
                                        <TableCell>
                                            <Input aria-label="Deduction name" placeholder="e.g. Uniform Deduction" className="w-44" value={row.name} onChange={(event) => updateDeduction(index, { name: event.target.value })} />
                                        </TableCell>
                                        <TableCell>
                                            <Input aria-label="Total amount" type="number" step="0.01" min={0} className="w-28" value={row.total_amount} onChange={(event) => updateDeduction(index, { total_amount: event.target.value })} />
                                        </TableCell>
                                        <TableCell>
                                            <Input aria-label="Deduction per cutoff" type="number" step="0.01" min={0} className="w-28" value={row.payment_amount} onChange={(event) => updateDeduction(index, { payment_amount: event.target.value })} />
                                        </TableCell>
                                        <TableCell>
                                            <ScheduleSelect ariaLabel="Deduction schedule" value={row.deduction_schedule} options={scheduleOptions} onChange={(value) => updateDeduction(index, { deduction_schedule: value })} />
                                        </TableCell>
                                        <TableCell>
                                            <Input aria-label="Start date" type="date" className="w-40" value={row.start_date} onChange={(event) => updateDeduction(index, { start_date: event.target.value })} />
                                        </TableCell>
                                        <TableCell className="text-sm tabular-nums">
                                            {estimatedLastPayment(num(row.total_amount), num(row.payment_amount), row.deduction_schedule, row.start_date)}
                                        </TableCell>
                                        <TableCell>
                                            <Input aria-label="Remarks" placeholder="Optional" className="w-40" value={row.remarks} onChange={(event) => updateDeduction(index, { remarks: event.target.value })} />
                                        </TableCell>
                                        <TableCell>
                                            <Button type="button" variant="ghost" size="icon" aria-label="Remove deduction" onClick={() => set('other_deductions', data.other_deductions.filter((_, i) => i !== index))}>
                                                <Trash2 className="text-destructive" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </Section>

                <Section title="Live payroll preview">
                    <div className="grid gap-4 md:grid-cols-3">
                        <div className="rounded-lg border bg-muted/40 p-4">
                            <div className="text-sm text-muted-foreground">Monthly basic salary equivalent</div>
                            <div className="mt-1 text-2xl font-semibold tabular-nums">{peso(preview.monthlyBasic)}</div>
                        </div>
                        <CutoffPreview title={`${cutoffLabels.first} preview`} values={preview.first} />
                        <CutoffPreview title={`${cutoffLabels.second} preview`} values={preview.second} />
                    </div>
                </Section>

                <Section title="Status">
                    <div className="grid gap-4">
                        <Field id="salary-remarks" label="Remarks" error={error('remarks')}>
                            <Textarea id="salary-remarks" rows={3} value={str('remarks')} onChange={(event) => set('remarks', event.target.value)} />
                        </Field>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox checked={Boolean(data.is_active)} onCheckedChange={(checked) => set('is_active', checked === true)} />
                            Active
                        </label>
                    </div>
                </Section>

                <div className="flex justify-end gap-2">
                    {modal.inModal ? (
                        <Button type="button" variant="outline" onClick={modal.close}>
                            Cancel
                        </Button>
                    ) : (
                        <Button type="button" variant="outline" asChild>
                            <Link href={urls.index}>Cancel</Link>
                        </Button>
                    )}
                    <Button type="submit" disabled={processing}>
                        {processing ? <LoaderCircle className="animate-spin" /> : <Save />}
                        {isEdit ? 'Update salary' : 'Save salary'}
                    </Button>
                </div>
            </form>
    );
}

function Section({ title, description, action, children }: { title: string; description?: string; action?: ReactNode; children: ReactNode }) {
    return (
        <Card>
            <CardHeader className="flex flex-row items-start justify-between gap-4">
                <div className="grid gap-1.5">
                    <CardTitle>{title}</CardTitle>
                    {description && <CardDescription>{description}</CardDescription>}
                </div>
                {action}
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}

function Field({ id, label, error, className, children }: { id: string; label: string; error?: string; className?: string; children: ReactNode }) {
    return (
        <div className={`grid content-start gap-1.5 ${className ?? ''}`}>
            <Label htmlFor={id}>{label}</Label>
            {children}
            {error && <p className="text-xs text-destructive">{error}</p>}
        </div>
    );
}

function ReadOnlyAmount({ label, value }: { label: string; value: string }) {
    const id = `ro-${label.toLowerCase().replace(/[^a-z]+/g, '-')}`;

    return (
        <Field id={id} label={label}>
            <Input id={id} value={value} readOnly className="bg-muted/50 tabular-nums" />
        </Field>
    );
}

function ScheduleSelect({
    value,
    options,
    onChange,
    ariaLabel,
    id,
}: {
    value: string;
    options: Record<string, string>;
    onChange: (value: string) => void;
    ariaLabel?: string;
    id?: string;
}) {
    return (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger id={id} aria-label={ariaLabel} className="w-full min-w-44">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {Object.entries(options).map(([key, label]) => (
                    <SelectItem key={key} value={key}>
                        {label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

function ScheduleField({
    id,
    label,
    value,
    options,
    onChange,
}: {
    id: string;
    label: string;
    value: string;
    options: Record<string, string>;
    onChange: (key: string, value: string) => void;
}) {
    return (
        <Field id={`salary-${id}`} label={label}>
            <ScheduleSelect id={`salary-${id}`} value={value} options={options} onChange={(next) => onChange(id, next)} />
        </Field>
    );
}

function PreviewBox({ label, value, hint }: { label: string; value: number; hint?: string }) {
    return (
        <div className="rounded-lg border p-3">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="text-lg font-semibold tabular-nums">{peso(value)}</div>
            {hint && <div className="text-xs text-muted-foreground">{hint}</div>}
        </div>
    );
}

function CutoffPreview({ title, values }: { title: string; values: { gross: number; deductions: number; net: number } }) {
    return (
        <div className="rounded-lg border p-4 text-sm">
            <div className="mb-2 font-medium">{title}</div>
            <div className="flex justify-between">
                <span className="text-muted-foreground">Gross</span>
                <span className="tabular-nums">{peso(values.gross)}</span>
            </div>
            <div className="flex justify-between">
                <span className="text-muted-foreground">Deductions</span>
                <span className="text-destructive tabular-nums">{peso(values.deductions)}</span>
            </div>
            <div className="mt-1 flex justify-between border-t pt-1 font-semibold">
                <span>Net</span>
                <span className="tabular-nums">{peso(values.net)}</span>
            </div>
        </div>
    );
}
