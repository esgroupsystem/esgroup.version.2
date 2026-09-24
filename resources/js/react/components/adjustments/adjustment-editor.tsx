import { Link, useForm } from '@inertiajs/react';
import { CircleAlert, Info, LoaderCircle, Plus, Save, Search, X } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { EmployeeCombobox, type PersonOption } from '@/components/employee-combobox';
import { useModal } from '@/components/modal/modal-context';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import {
    disasterHours,
    effectSwitches,
    isDisasterType,
    manualTimeText,
    ruleGuide,
    sectionsFor,
    workDateLabel,
} from '@/pages/payroll/adjustments/rules';

interface OffsetSourceRow {
    date: string;
    hours: string;
}

interface AdjustmentData {
    id: number;
    employee_biometric_id: number | null;
    adjustment_type: string;
    work_date: string;
    date_from: string;
    date_to: string;
    adjusted_time_in: string;
    adjusted_time_out: string;
    offset_sources: OffsetSourceRow[];
    amount: string;
    is_paid: boolean;
    ignore_late: boolean;
    ignore_undertime: boolean;
    reason: string;
    remarks: string;
    status: string | null;
    is_locked: boolean;
}

export interface AdjustmentEditorProps {
    adjustment: AdjustmentData | null;
    people: PersonOption[];
    types: Record<string, string>;
    urls: { index?: string; submit: string; offsetProof: string };
    /** Lock the editor to one employee and a date window (payroll item "File Adjustment"). */
    lock?: { employeeBiometricId: number; minDate?: string | null; maxDate?: string | null };
    /** Extra fields posted with the form, e.g. recompute_payroll_item_id. */
    extraData?: Record<string, string | number>;
    submitLabel?: string;
    onCancel?: () => void;
    /** Called after a successful save (e.g. to close a dialog). */
    onSuccess?: () => void;
    layout?: "page" | "dialog";
}

interface OffsetCheckResult {
    found: boolean;
    message: string;
    proof?: {
        employee_name?: string;
        target_date?: string;
        requested_hours?: number;
        target_capacity_hours?: number | null;
    };
    sources?: {
        date: string;
        requested_hours: number;
        available_hours: number;
        time_in: string | null;
        time_out: string | null;
        has_proof: boolean;
        error: string | null;
    }[];
    errors?: Record<string, string[]>;
}

const BLANK_SOURCE: OffsetSourceRow = { date: '', hours: '' };

export function AdjustmentEditor({
    adjustment,
    people,
    types,
    urls,
    lock,
    extraData,
    submitLabel,
    onCancel,
    onSuccess,
    layout = "page",
}: AdjustmentEditorProps) {
    const isEdit = adjustment !== null;
    const initialType = adjustment?.adjustment_type ?? '';
    const lockedId = lock?.employeeBiometricId ?? adjustment?.employee_biometric_id;
    const initialPerson = people.find((person) => person.employee_biometric_id === lockedId) ?? null;
    const initialSwitches = effectSwitches(initialType);

    const modal = useModal();
    const form = useForm({
        employee_biometric_id: initialPerson?.employee_biometric_id ?? adjustment?.employee_biometric_id ?? null,
        biometric_employee_id: initialPerson?.biometric_employee_id ?? '',
        employee_no: initialPerson?.employee_no ?? '',
        employee_name: initialPerson?.employee_name ?? '',
        crosschex_id: initialPerson?.crosschex_id ?? '',
        adjustment_type: initialType,
        work_date: adjustment?.work_date ?? '',
        date_from: adjustment?.date_from ?? '',
        date_to: adjustment?.date_to ?? '',
        adjusted_time_in: adjustment?.adjusted_time_in ?? '',
        adjusted_time_out: adjustment?.adjusted_time_out ?? '',
        offset_sources: adjustment?.offset_sources.length ? adjustment.offset_sources : [BLANK_SOURCE],
        amount: adjustment?.amount ?? '',
        // Leave pay may be switched off per record; every other switch follows the type rule.
        is_paid: initialType === 'sick_leave' || initialType === 'medical_leave'
            ? (adjustment?.is_paid ?? true)
            : initialSwitches.is_paid.checked,
        ignore_late: initialSwitches.ignore_late.checked,
        ignore_undertime: initialSwitches.ignore_undertime.checked,
        reason: adjustment?.reason ?? '',
        remarks: adjustment?.remarks ?? '',
        ...(extraData ?? {}),
    });

    const { data, setData, errors } = form;

    // Clear a field's server error as soon as it is edited.
    const updateField = <K extends keyof typeof data>(key: K, value: (typeof data)[K]) => {
        setData((current) => ({ ...current, [key]: value }));
        form.clearErrors(key as never);
    };
    const type = data.adjustment_type;
    const sections = sectionsFor(type);
    const switches = effectSwitches(type);
    const disaster = isDisasterType(type);
    const manualText = manualTimeText[type];

    const changeType = (next: string) => {
        const rule = effectSwitches(next);
        setData((current) => ({
            ...current,
            adjustment_type: next,
            is_paid: rule.is_paid.checked,
            ignore_late: rule.ignore_late.checked,
            ignore_undertime: rule.ignore_undertime.checked,
        }));
    };

    const choosePerson = (person: PersonOption | null) =>
        setData((current) => ({
            ...current,
            employee_biometric_id: person?.employee_biometric_id ?? null,
            biometric_employee_id: person?.biometric_employee_id ?? '',
            employee_no: person?.employee_no ?? '',
            employee_name: person?.employee_name ?? '',
            crosschex_id: person?.crosschex_id ?? '',
        }));

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // modal.visit(): in a modal page the save stays on the page underneath and the modal closes/refreshes.
        const options = modal.visit({ preserveScroll: true, onSuccess: () => onSuccess?.() });

        if (isEdit) {
            form.put(urls.submit, options);
        } else {
            form.post(urls.submit, options);
        }
    };

    const fieldError = (key: string) => (errors as Record<string, string>)[key];

    return (
        <>

            {adjustment?.is_locked && (
                <Alert variant="destructive">
                    <CircleAlert />
                    <AlertTitle>Linked to a generated payroll</AlertTitle>
                    <AlertDescription>
                        This adjustment can no longer be edited. Delete/regenerate the affected draft payroll first if a correction is required.
                    </AlertDescription>
                </Alert>
            )}

            <form onSubmit={submit} className={layout === "page" ? "grid gap-4 lg:grid-cols-3 lg:gap-6" : "grid gap-4"}>
                <div className={layout === "page" ? "grid content-start gap-4 lg:col-span-2 lg:gap-6" : "grid content-start gap-4"}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Employee and adjustment type</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 md:grid-cols-[1fr_18rem]">
                            <Field htmlFor="adj-employee_biometric_id"
                                label="Biometrics employee"
                                error={fieldError('employee_biometric_id') ?? fieldError('employee_name')}
                                help={
                                    disaster
                                        ? 'Employee selection is skipped. This applies to all payroll-active employees; full-day pay is granted only after the selected biometric work-hour threshold is completed.'
                                        : 'Required for individual adjustments. Automatically skipped for Typhoon / Disaster.'
                                }
                            >
                                <EmployeeCombobox id="adj-employee_biometric_id"
                                    people={people}
                                    value={disaster ? null : data.employee_biometric_id}
                                    onChange={choosePerson}
                                    disabled={disaster || Boolean(lock)}
                                    invalid={Boolean(fieldError('employee_biometric_id'))}
                                    placeholder={disaster ? 'All payroll-active employees' : undefined}
                                />
                            </Field>
                            <Field htmlFor="adj-adjustment_type" label="Adjustment type" error={fieldError('adjustment_type')}>
                                <Select value={type || undefined} onValueChange={changeType}>
                                    <SelectTrigger id="adj-adjustment_type" className="w-full" aria-invalid={Boolean(fieldError('adjustment_type'))}>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(types).map(([value, label]) => (
                                            <SelectItem key={value} value={value}>
                                                {label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>
                        </CardContent>
                    </Card>

                    {sections.includes('leave') && (
                        <SectionCard title="Sick Leave / Medical Leave" description="Leave adjustments only need a date range. No time in and time out required.">
                            <div className="grid gap-4 md:grid-cols-2">
                                <Field htmlFor="adj-date_from" label="Date from" error={fieldError('date_from')}>
                                    <Input id="adj-date_from" type="date" min={lock?.minDate ?? undefined} max={lock?.maxDate ?? undefined} required value={data.date_from} onChange={(event) => updateField('date_from', event.target.value)} />
                                </Field>
                                <Field htmlFor="adj-date_to" label="Date to" error={fieldError('date_to')}>
                                    <Input id="adj-date_to" type="date" min={lock?.minDate ?? undefined} max={lock?.maxDate ?? undefined} required value={data.date_to} onChange={(event) => updateField('date_to', event.target.value)} />
                                </Field>
                            </div>
                        </SectionCard>
                    )}

                    {sections.includes('single-date') && (
                        <SectionCard title="Work date / transfer date">
                            <Field htmlFor="adj-work_date" label={workDateLabel(type)} error={fieldError('work_date')} className="md:max-w-xs">
                                <Input id="adj-work_date" type="date" min={lock?.minDate ?? undefined} max={lock?.maxDate ?? undefined} required value={data.work_date} onChange={(event) => updateField('work_date', event.target.value)} />
                            </Field>
                        </SectionCard>
                    )}

                    {sections.includes('manual-time') && (
                        <SectionCard title={manualText?.title ?? 'Manual Time In / Time Out'} description={manualText?.help}>
                            <div className="grid gap-4 md:grid-cols-2">
                                <Field htmlFor="adj-adjusted_time_in" label={manualText?.inLabel ?? 'Adjusted Time In'} error={fieldError('adjusted_time_in')}>
                                    <Input id="adj-adjusted_time_in" type="time" required value={data.adjusted_time_in} onChange={(event) => updateField('adjusted_time_in', event.target.value)} />
                                </Field>
                                <Field htmlFor="adj-adjusted_time_out" label={manualText?.outLabel ?? 'Adjusted Time Out'} error={fieldError('adjusted_time_out')}>
                                    <Input id="adj-adjusted_time_out" type="time" required value={data.adjusted_time_out} onChange={(event) => updateField('adjusted_time_out', event.target.value)} />
                                </Field>
                            </div>
                        </SectionCard>
                    )}

                    {sections.includes('offset') && (
                        <OffsetSection
                            sources={data.offset_sources}
                            onChange={(rows) => updateField('offset_sources', rows)}
                            errors={errors as Record<string, string>}
                            checkUrl={urls.offsetProof}
                            checkParams={{
                                employee_biometric_id: data.employee_biometric_id ? String(data.employee_biometric_id) : '',
                                biometric_employee_id: data.biometric_employee_id ?? '',
                                employee_no: data.employee_no ?? '',
                                employee_name: data.employee_name ?? '',
                                work_date: data.work_date,
                                adjustment_id: adjustment ? String(adjustment.id) : '',
                            }}
                        />
                    )}

                    {sections.includes('cash') && (
                        <SectionCard
                            title="Salary adjustment amount"
                            description="A one-time salary adjustment for the cutoff that contains the work date above. Enter a positive amount (e.g. 500) to add pay, or a negative amount (e.g. -1000) to deduct from net pay. Applied immediately on save and does not affect attendance, late, or undertime."
                        >
                            <Field htmlFor="adj-amount" label="Amount" error={fieldError('amount')} className="md:max-w-xs">
                                <div className="flex items-center rounded-md border shadow-xs focus-within:ring-[3px] focus-within:ring-ring/50">
                                    <span className="pl-3 text-sm text-muted-foreground">₱</span>
                                    <Input id="adj-amount"
                                        type="number"
                                        step="0.01"
                                        required
                                        className="border-0 shadow-none focus-visible:ring-0"
                                        placeholder="e.g. 500.00 or -1000.00"
                                        value={data.amount}
                                        aria-invalid={Boolean(fieldError('amount'))}
                                        onChange={(event) => updateField('amount', event.target.value)}
                                    />
                                </div>
                                {data.amount !== '' && Number(data.amount) !== 0 && (
                                    <Badge variant="outline" className="mt-2">
                                        {Number(data.amount) > 0 ? 'Addition to gross pay' : 'Deduction after gross pay'}
                                    </Badge>
                                )}
                            </Field>
                        </SectionCard>
                    )}

                    {sections.includes('disaster') && (
                        <SectionCard title={`Typhoon / Disaster adjustment for all employees (${disasterHours(type)} hrs)`}>
                            <p className="text-sm text-muted-foreground">
                                This adjustment does not require employee selection. The selected 3/4/5/6-hour rule is checked against each
                                payroll-active employee's valid biometric Time In / Time Out. The completed work threshold uses paid work minutes
                                and excludes the configured unpaid lunch break. Employees who meet the selected threshold receive 100% full-day pay
                                with late and undertime ignored. Employees below the threshold stay on normal attendance computation only.
                            </p>
                        </SectionCard>
                    )}

                    <SectionCard title="Reason and remarks">
                        <div className="grid gap-4">
                            <Field htmlFor="adj-reason" label="Reason" error={fieldError('reason')}>
                                <Textarea id="adj-reason"
                                    rows={3}
                                    required
                                    placeholder="Example: Typhoon Egay early dismissal / Employee submitted approved OB form / Medical certificate / Offset request."
                                    value={data.reason}
                                    onChange={(event) => updateField('reason', event.target.value)}
                                />
                            </Field>
                            <Field htmlFor="adj-remarks" label="Remarks" error={fieldError('remarks')}>
                                <Textarea id="adj-remarks" rows={2} placeholder="Optional payroll notes." value={data.remarks} onChange={(event) => updateField('remarks', event.target.value)} />
                            </Field>
                        </div>
                    </SectionCard>
                </div>

                <div className={layout === "page" ? "grid content-start gap-4 lg:sticky lg:top-20 lg:self-start" : "grid content-start gap-4"}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Payroll effect</CardTitle>
                            <CardDescription>Adjustment rules are type-controlled.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <EffectSwitch
                                id="is_paid"
                                label="Paid adjustment"
                                checked={data.is_paid}
                                state={type ? switches.is_paid : null}
                                fallbackHelp="Include this adjustment as payable attendance where the selected type allows it."
                                onChange={(checked) => updateField('is_paid', checked)}
                            />
                            <Separator />
                            <EffectSwitch
                                id="ignore_late"
                                label="Ignore late"
                                checked={data.ignore_late}
                                state={type ? switches.ignore_late : null}
                                fallbackHelp="Late deduction will not apply when the adjustment type allows this effect."
                                onChange={(checked) => updateField('ignore_late', checked)}
                            />
                            <Separator />
                            <EffectSwitch
                                id="ignore_undertime"
                                label="Ignore undertime"
                                checked={data.ignore_undertime}
                                state={type ? switches.ignore_undertime : null}
                                fallbackHelp="Undertime deduction will not apply when the adjustment type allows this effect."
                                onChange={(checked) => updateField('ignore_undertime', checked)}
                            />
                            <Alert>
                                <Info />
                                <AlertDescription>{ruleGuide(type)}</AlertDescription>
                            </Alert>
                        </CardContent>
                    </Card>

                    <div className="flex gap-2">
                        {onCancel || modal.inModal ? (
                            <Button type="button" variant="outline" className="flex-1" onClick={onCancel ?? modal.close}>
                                Cancel
                            </Button>
                        ) : (
                            <Button type="button" variant="outline" className="flex-1" asChild>
                                <Link href={urls.index ?? "/"}>Cancel</Link>
                            </Button>
                        )}
                        <Button type="submit" className="flex-1" disabled={form.processing || adjustment?.is_locked}>
                            {form.processing ? <LoaderCircle className="animate-spin" /> : <Save />}
                            {submitLabel ?? (isEdit ? 'Update Adjustment' : 'Save Adjustment')}
                        </Button>
                    </div>
                </div>
            </form>
        </>
    );
}

function SectionCard({ title, description, children }: { title: string; description?: string; children: ReactNode }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                {description && <CardDescription>{description}</CardDescription>}
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}

function Field({
    htmlFor,
    label,
    error,
    help,
    className,
    children,
}: {
    htmlFor?: string;
    label: string;
    error?: string;
    help?: string;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={cn('grid content-start gap-1.5', className)}>
            <Label htmlFor={htmlFor}>{label}</Label>
            {children}
            {error ? (
                <p className="text-sm text-destructive">{error}</p>
            ) : (
                help && <p className="text-xs text-muted-foreground">{help}</p>
            )}
        </div>
    );
}

function EffectSwitch({
    id,
    label,
    checked,
    state,
    fallbackHelp,
    onChange,
}: {
    id: string;
    label: string;
    checked: boolean;
    state: { disabled: boolean; help: string } | null;
    fallbackHelp: string;
    onChange: (checked: boolean) => void;
}) {
    return (
        <div className="flex items-start justify-between gap-4">
            <div className="grid gap-1">
                <Label htmlFor={id}>{label}</Label>
                <p className="text-xs text-muted-foreground">{state?.help ?? fallbackHelp}</p>
            </div>
            <Switch id={id} checked={checked} disabled={state?.disabled ?? false} onCheckedChange={onChange} />
        </div>
    );
}

function OffsetSection({
    sources,
    onChange,
    errors,
    checkUrl,
    checkParams,
}: {
    sources: OffsetSourceRow[];
    onChange: (rows: OffsetSourceRow[]) => void;
    errors: Record<string, string>;
    checkUrl: string;
    checkParams: Record<string, string>;
}) {
    const [checking, setChecking] = useState(false);
    const [result, setResult] = useState<OffsetCheckResult | null>(null);

    const total = sources.reduce((sum, row) => sum + (Number.parseFloat(row.hours) || 0), 0);
    const sourceErrors = Object.entries(errors)
        .filter(([key]) => key === 'offset_sources' || key.startsWith('offset_sources.') || key === 'offset_source_date' || key === 'offset_hours')
        .map(([, message]) => message);

    const update = (index: number, changes: Partial<OffsetSourceRow>) =>
        onChange(sources.map((row, i) => (i === index ? { ...row, ...changes } : row)));

    const check = async () => {
        const filled = sources.filter((row) => row.date !== '' || row.hours !== '');

        if (!checkParams.employee_biometric_id || !checkParams.work_date || filled.length === 0 || filled.some((row) => !row.date || !row.hours)) {
            setResult({
                found: false,
                message: 'Please complete the Offset details first: employee, target date, and every source row needs both a date and the hours to transfer.',
            });
            return;
        }

        const params = new URLSearchParams(Object.entries(checkParams).filter(([, value]) => value !== ''));
        filled.forEach((row, index) => {
            params.set(`offset_sources[${index}][date]`, row.date);
            params.set(`offset_sources[${index}][hours]`, row.hours);
        });

        setChecking(true);
        try {
            const response = await fetch(`${checkUrl}?${params}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const body = (await response.json()) as OffsetCheckResult;
            // Laravel validation failures return {message, errors} without `found`.
            setResult({ ...body, found: response.ok && Boolean(body.found) });
        } catch (error) {
            setResult({ found: false, message: `Unable to check biometric proof: ${String(error)}` });
        } finally {
            setChecking(false);
        }
    };

    return (
        <SectionCard
            title="Offset / company compensatory leave proof"
            description="Select one or more earlier source dates where the employee rendered verified excess time beyond the required shift (example: 1 extra hour on each of Monday to Friday can be pooled to cover an absence on the next Monday). Only excess minutes not already assigned to another Offset request may be used. No separate cash Offset payment is created, and approved OT remains payable separately."
        >
            <div className="grid gap-2">
                <div className="hidden grid-cols-[1fr_10rem_2.25rem] gap-2 text-sm font-medium md:grid">
                    <span>Source excess-time date</span>
                    <span>Hours to transfer</span>
                </div>
                {sources.map((row, index) => (
                    <div key={index} className="grid grid-cols-[1fr_8rem_2.25rem] gap-2 md:grid-cols-[1fr_10rem_2.25rem]">
                        <Input
                            type="date"
                            required
                            aria-label="Source excess-time date"
                            value={row.date}
                            aria-invalid={Boolean(errors[`offset_sources.${index}.date`])}
                            onChange={(event) => update(index, { date: event.target.value })}
                        />
                        <div className="flex items-center rounded-md border shadow-xs focus-within:ring-[3px] focus-within:ring-ring/50">
                            <Input
                                type="number"
                                min={0.01}
                                max={24}
                                step={0.01}
                                required
                                placeholder="1.00"
                                aria-label="Hours to transfer"
                                className="border-0 shadow-none focus-visible:ring-0"
                                value={row.hours}
                                onChange={(event) => update(index, { hours: event.target.value })}
                            />
                            <span className="pr-3 text-sm text-muted-foreground">hr</span>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            aria-label="Remove this source date"
                            disabled={sources.length === 1}
                            onClick={() => onChange(sources.filter((_, i) => i !== index))}
                        >
                            <X />
                        </Button>
                    </div>
                ))}
                {sourceErrors.map((message, index) => (
                    <p key={index} className="text-sm text-destructive">
                        {message}
                    </p>
                ))}
                <div className="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <Button type="button" variant="outline" onClick={() => onChange([...sources, BLANK_SOURCE])}>
                        <Plus />
                        Add source date
                    </Button>
                    <span className="text-sm font-medium tabular-nums">Total credit: {total.toFixed(2)} hr</span>
                    <Button type="button" variant="secondary" onClick={check} disabled={checking}>
                        {checking ? <LoaderCircle className="animate-spin" /> : <Search />}
                        Check available offset credit
                    </Button>
                </div>
            </div>

            <OffsetResultDialog result={result} onClose={() => setResult(null)} />
        </SectionCard>
    );
}

function OffsetResultDialog({ result, onClose }: { result: OffsetCheckResult | null; onClose: () => void }) {
    const firstValidationError = result?.errors ? Object.values(result.errors)[0]?.[0] : undefined;

    return (
        <Dialog open={result !== null} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{result?.found ? 'Offset sources verified' : 'Offset needs attention'}</DialogTitle>
                    <DialogDescription>
                        {result?.proof?.target_date ? `Target date ${result.proof.target_date}` : 'Biometrics proof check'}
                    </DialogDescription>
                </DialogHeader>
                {result && (
                    <div className="grid gap-4">
                        <Alert variant={result.found ? 'default' : 'destructive'}>
                            {result.found ? <Info /> : <CircleAlert />}
                            <AlertDescription>
                                {firstValidationError ?? result.message}
                                {result.proof?.requested_hours !== undefined && (
                                    <div className="mt-1 text-xs">
                                        Total requested: {Number(result.proof.requested_hours).toFixed(2)} hr
                                        {result.proof.target_capacity_hours !== null && result.proof.target_capacity_hours !== undefined
                                            ? ` · Target shortage: ${Number(result.proof.target_capacity_hours).toFixed(2)} hr`
                                            : ' · Target attendance not built yet; credit is capped by the actual shortage later.'}
                                    </div>
                                )}
                            </AlertDescription>
                        </Alert>
                        {result.sources && result.sources.length > 0 && (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Source date</TableHead>
                                        <TableHead>Biometrics</TableHead>
                                        <TableHead className="text-right">Unused excess</TableHead>
                                        <TableHead className="text-right">Requested</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {result.sources.map((source) => (
                                        <TableRow key={source.date} className={cn(source.error && 'bg-destructive/5')}>
                                            <TableCell className="font-medium">{source.date}</TableCell>
                                            <TableCell>
                                                {source.has_proof ? `${source.time_in ?? '--:--'} – ${source.time_out ?? '--:--'}` : 'No biometrics'}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">{Number(source.available_hours).toFixed(2)} hr</TableCell>
                                            <TableCell className="text-right tabular-nums">{Number(source.requested_hours).toFixed(2)} hr</TableCell>
                                            <TableCell className="max-w-64 text-xs whitespace-normal">
                                                {source.error ? <span className="text-destructive">{source.error}</span> : 'OK'}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
