import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CircleAlert, CircleCheck, LoaderCircle, Save, X } from 'lucide-react';
import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { postJson, TestPanel } from '@/components/payroll/settings/test-panel';
import type { Option, TestOptions } from '@/components/payroll/settings/types';
import { SearchSelect } from '@/components/search-select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { peso } from '@/lib/format';
import { cn } from '@/lib/utils';

interface Values {
    name: string;
    code: string;
    kind: 'earning' | 'deduction';
    method: 'fixed' | 'percent' | 'per_unit' | 'formula';
    amount: string;
    base: string;
    unit: string;
    formula: string;
    cutoff: string;
    rate_type: string;
    payroll_groups: string[];
    employee_biometric_ids: number[];
    effective_from: string;
    effective_to: string;
    is_active: boolean;
    sort_order: number;
    notes: string;
}

interface Variable {
    name: string;
    label: string;
    group: string;
    money: boolean;
    stage: 'both' | 'deduction';
}

interface Props {
    rule: { id: number; name: string } | null;
    values: Values;
    options: {
        kinds: Option[];
        methods: Option[];
        cutoffs: Option[];
        rateTypes: Option[];
        bases: Option[];
        units: Option[];
        variables: Variable[];
        otherRules: { code: string; name: string; kind: string }[];
        groups: Option[];
        employees: Option[];
    };
    test: TestOptions;
    urls: { index: string; submit: string; check: string };
}

const EXAMPLES = [
    { label: 'Perfect attendance', formula: 'IF(days_absent = 0 AND minutes_late = 0, 1000, 0)' },
    { label: 'Per trip day, max ₱2,000', formula: 'MIN(days_worked * 150, 2000)' },
    { label: 'Only on the 2nd cutoff', formula: 'IF(cutoff = 2, 500, 0)' },
    { label: '5% of basic, monthly-paid only', formula: 'IF(is_monthly = 1, basic_pay * 5%, 0)' },
    { label: 'Late penalty ₱5 per minute over 30', formula: 'MAX(minutes_late - 30, 0) * 5' },
];

const slug = (text: string) =>
    text
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '')
        .replace(/^(\d)/, 'r_$1')
        .slice(0, 60);

export default definePage<Props>({
    title: ({ rule }) => (rule ? `Edit rule — ${rule.name}` : 'New payroll rule'),
    description: () => 'Applies to payrolls generated or recomputed after saving. Finalized payrolls never change.',
    actions: (props) => <BackLink {...props} />,
    size: 'full',
    Content: RuleForm,
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

function RuleForm({ rule, values, options, test, urls }: Props) {
    const modal = useModal();
    const form = useForm<Values>(values);
    const { data, errors, processing } = form;
    const fieldErrors = errors as Record<string, string | undefined>;
    const [codeTouched, setCodeTouched] = useState(rule !== null);
    const formulaRef = useRef<HTMLTextAreaElement>(null);
    const check = useFormulaCheck(urls.check, data.formula, data.kind, rule?.id ?? null, data.method === 'formula');

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = modal.visit({ preserveScroll: true });
        if (rule) {
            form.put(urls.submit, options);
        } else {
            form.post(urls.submit, options);
        }
    };

    const insert = (text: string) => {
        const area = formulaRef.current;
        const start = area?.selectionStart ?? data.formula.length;
        const end = area?.selectionEnd ?? data.formula.length;
        const next = data.formula.slice(0, start) + text + data.formula.slice(end);
        form.setData('formula', next);
        requestAnimationFrame(() => {
            area?.focus();
            area?.setSelectionRange(start + text.length, start + text.length);
        });
    };

    const usable = options.variables.filter((variable) => variable.stage === 'both' || data.kind === 'deduction');
    const ruleCodes = options.otherRules.filter((other) => data.kind === 'deduction' || other.kind === 'earning');
    const groups = useMemo(() => [...new Set(usable.map((variable) => variable.group))], [usable]);
    const employeeLabel = (id: number) => options.employees.find((option) => option.value === String(id))?.label ?? `#${id}`;
    const addable = options.employees.filter((option) => !data.employee_biometric_ids.includes(Number(option.value)));

    const validate = () => {
        if (!data.name.trim()) return 'Give the rule a name first.';
        if (data.method !== 'formula' && data.amount === '') return 'Enter the amount first.';
        if (data.method === 'formula' && !data.formula.trim()) return 'Write the formula first.';
        if (data.method === 'formula' && check.state === 'error') return check.message;
        return null;
    };

    return (
        <div className="grid min-w-0 gap-4 2xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <form onSubmit={submit} className="grid min-w-0 content-start gap-4">
                <Card>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <FormField id="rule-name" label="Name" required error={errors.name} hint="Shown on the payroll item and payslip.">
                            <Input
                                id="rule-name"
                                required
                                maxLength={150}
                                value={data.name}
                                onChange={(event) =>
                                    form.setData((current) => ({ ...current, name: event.target.value, ...(codeTouched ? {} : { code: slug(event.target.value) }) }))
                                }
                            />
                        </FormField>
                        <FormField id="rule-code" label="Code" required error={errors.code} hint="Short name other formulas can use, e.g. rice_allowance.">
                            <Input
                                id="rule-code"
                                required
                                maxLength={60}
                                className="font-mono"
                                value={data.code}
                                onChange={(event) => {
                                    setCodeTouched(true);
                                    form.setData('code', event.target.value.toLowerCase());
                                }}
                            />
                        </FormField>
                        <div className="grid gap-1.5 md:col-span-2">
                            <Label>Type</Label>
                            <div className="grid gap-2 sm:grid-cols-2" role="radiogroup" aria-label="Type">
                                {(
                                    [
                                        ['earning', 'Earning', 'Added to gross pay (counts toward the SSS basis like other additions).'],
                                        ['deduction', 'Deduction', 'Taken from net pay, after SSS, PhilHealth and Pag-IBIG.'],
                                    ] as const
                                ).map(([value, label, hint]) => (
                                    <button
                                        key={value}
                                        type="button"
                                        role="radio"
                                        aria-checked={data.kind === value}
                                        onClick={() => form.setData('kind', value)}
                                        className={cn(
                                            'rounded-lg border p-3 text-left transition-colors',
                                            data.kind === value ? (value === 'earning' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30' : 'border-destructive bg-destructive/5') : 'hover:bg-accent',
                                        )}
                                    >
                                        <div className="text-sm font-medium">{label}</div>
                                        <div className="text-xs text-muted-foreground">{hint}</div>
                                    </button>
                                ))}
                            </div>
                            {errors.kind && <p className="text-xs text-destructive">{errors.kind}</p>}
                        </div>
                    </CardContent>
                </Card>

                <Card className="gap-4">
                    <CardHeader>
                        <CardTitle className="text-base">How it computes</CardTitle>
                        <CardDescription>The result is rounded to centavos and is never below zero.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        <div className="inline-flex max-w-full flex-wrap gap-1 rounded-lg bg-muted p-1" role="radiogroup" aria-label="Method">
                            {options.methods.map((method) => (
                                <button
                                    key={method.value}
                                    type="button"
                                    role="radio"
                                    aria-checked={data.method === method.value}
                                    onClick={() => form.setData('method', method.value as Values['method'])}
                                    className={cn(
                                        'h-8 rounded-md px-3 text-sm font-medium',
                                        data.method === method.value ? 'bg-background shadow-sm' : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    {method.label}
                                </button>
                            ))}
                        </div>

                        {data.method !== 'formula' && (
                            <div className="grid gap-4 sm:grid-cols-[minmax(0,12rem)_minmax(0,1fr)]">
                                <FormField id="rule-amount" label={data.method === 'percent' ? 'Percent' : 'Amount'} required error={errors.amount}>
                                    <div className="relative">
                                        {data.method !== 'percent' && <span className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground">₱</span>}
                                        <Input
                                            id="rule-amount"
                                            type="number"
                                            step="any"
                                            min={0}
                                            inputMode="decimal"
                                            className={cn('tabular-nums', data.method !== 'percent' && 'pl-7', data.method === 'percent' && 'pr-8')}
                                            value={data.amount}
                                            onChange={(event) => form.setData('amount', event.target.value)}
                                        />
                                        {data.method === 'percent' && <span className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-sm text-muted-foreground">%</span>}
                                    </div>
                                </FormField>
                                {data.method === 'percent' && (
                                    <FormField id="rule-base" label="Of" required error={errors.base}>
                                        <OptionSelect id="rule-base" value={data.base} options={options.bases.filter((option) => usable.some((variable) => variable.name === option.value))} onChange={(value) => form.setData('base', value)} />
                                    </FormField>
                                )}
                                {data.method === 'per_unit' && (
                                    <FormField id="rule-unit" label="Times" required error={errors.unit}>
                                        <OptionSelect id="rule-unit" value={data.unit} options={options.units} onChange={(value) => form.setData('unit', value)} />
                                    </FormField>
                                )}
                                {data.method === 'fixed' && <p className="self-end pb-2 text-sm text-muted-foreground">per cutoff, to everyone the rule applies to.</p>}
                            </div>
                        )}

                        {data.method === 'formula' && (
                            <div className="grid gap-3">
                                <FormField id="rule-formula" label="Formula" required error={errors.formula}>
                                    <Textarea
                                        id="rule-formula"
                                        ref={formulaRef}
                                        rows={3}
                                        spellCheck={false}
                                        className="font-mono text-sm"
                                        placeholder="IF(days_absent = 0, 1000, 0)"
                                        value={data.formula}
                                        onChange={(event) => form.setData('formula', event.target.value)}
                                    />
                                </FormField>
                                <FormulaStatus check={check} />

                                <div className="grid gap-2 rounded-lg border bg-muted/30 p-3">
                                    <p className="text-xs text-muted-foreground">
                                        Click a value to add it. Use <code>+ − * /</code>, <code>%</code> (5% = 0.05), <code>= &lt; &gt; &lt;= &gt;=</code>, <code>AND OR NOT</code> and{' '}
                                        <code>IF(test, yes, no)</code>, <code>MIN</code>, <code>MAX</code>, <code>ROUND(x, 2)</code>, <code>FLOOR</code>, <code>CEIL</code>, <code>ABS</code>. Dividing by zero gives 0.
                                    </p>
                                    {groups.map((group) => (
                                        <div key={group} className="flex flex-wrap items-center gap-1">
                                            <span className="w-20 shrink-0 text-xs font-medium text-muted-foreground">{group}</span>
                                            {usable
                                                .filter((variable) => variable.group === group)
                                                .map((variable) => (
                                                    <button key={variable.name} type="button" title={variable.label} onClick={() => insert(variable.name)} className="rounded border bg-background px-1.5 py-0.5 font-mono text-xs hover:bg-accent">
                                                        {variable.name}
                                                    </button>
                                                ))}
                                        </div>
                                    ))}
                                    {ruleCodes.length > 0 && (
                                        <div className="flex flex-wrap items-center gap-1">
                                            <span className="w-20 shrink-0 text-xs font-medium text-muted-foreground">Rules</span>
                                            {ruleCodes.map((other) => (
                                                <button key={other.code} type="button" title={other.name} onClick={() => insert(other.code)} className="rounded border border-dashed bg-background px-1.5 py-0.5 font-mono text-xs hover:bg-accent">
                                                    {other.code}
                                                </button>
                                            ))}
                                        </div>
                                    )}
                                    <div className="flex flex-wrap items-center gap-1">
                                        <span className="w-20 shrink-0 text-xs font-medium text-muted-foreground">Examples</span>
                                        {EXAMPLES.map((example) => (
                                            <button key={example.label} type="button" title={example.formula} onClick={() => form.setData('formula', example.formula)} className="rounded border bg-background px-1.5 py-0.5 text-xs hover:bg-accent">
                                                {example.label}
                                            </button>
                                        ))}
                                    </div>
                                    <p className="text-xs text-muted-foreground">Hover a value to see what it means. Net pay and government values work in deduction rules only.</p>
                                </div>
                            </div>
                        )}

                        <p className="rounded-md border bg-muted/40 px-3 py-2 text-sm">{sentence(data, options)}</p>
                    </CardContent>
                </Card>

                <Card className="gap-4">
                    <CardHeader>
                        <CardTitle className="text-base">When and who</CardTitle>
                        <CardDescription>Leave groups and employees empty to apply to everyone.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <FormField id="rule-cutoff" label="Cutoff" error={errors.cutoff}>
                            <OptionSelect id="rule-cutoff" value={data.cutoff} options={options.cutoffs} onChange={(value) => form.setData('cutoff', value)} />
                        </FormField>
                        <FormField id="rule-rate-type" label="Pay type" error={errors.rate_type}>
                            <OptionSelect id="rule-rate-type" value={data.rate_type} options={options.rateTypes} onChange={(value) => form.setData('rate_type', value)} />
                        </FormField>
                        <div className="grid gap-1.5 md:col-span-2">
                            <Label>Payroll groups</Label>
                            <div className="flex flex-wrap gap-4">
                                {options.groups.map((group) => (
                                    <label key={group.value} className="flex items-center gap-2 text-sm">
                                        <Checkbox
                                            checked={data.payroll_groups.includes(group.value)}
                                            onCheckedChange={(checked) =>
                                                form.setData('payroll_groups', checked === true ? [...data.payroll_groups, group.value] : data.payroll_groups.filter((value) => value !== group.value))
                                            }
                                        />
                                        {group.label}
                                    </label>
                                ))}
                            </div>
                            {(errors.payroll_groups || fieldErrors['payroll_groups.0']) && <p className="text-xs text-destructive">{errors.payroll_groups ?? fieldErrors['payroll_groups.0']}</p>}
                        </div>
                        <div className="grid gap-1.5 md:col-span-2">
                            <Label htmlFor="rule-employee-add">Only these employees</Label>
                            <SearchSelect
                                id="rule-employee-add"
                                ariaLabel="Add employee"
                                options={addable}
                                value=""
                                onChange={(value) => value && form.setData('employee_biometric_ids', [...data.employee_biometric_ids, Number(value)])}
                                placeholder="Add an employee..."
                                searchPlaceholder="Search name or number..."
                            />
                            {data.employee_biometric_ids.length > 0 && (
                                <div className="flex flex-wrap gap-1.5">
                                    {data.employee_biometric_ids.map((id) => (
                                        <Badge key={id} variant="secondary" className="gap-1 pr-1">
                                            {employeeLabel(id)}
                                            <button
                                                type="button"
                                                aria-label={`Remove ${employeeLabel(id)}`}
                                                className="rounded-sm p-0.5 hover:bg-background"
                                                onClick={() => form.setData('employee_biometric_ids', data.employee_biometric_ids.filter((value) => value !== id))}
                                            >
                                                <X className="size-3" />
                                            </button>
                                        </Badge>
                                    ))}
                                </div>
                            )}
                            {errors.employee_biometric_ids && <p className="text-xs text-destructive">{errors.employee_biometric_ids}</p>}
                        </div>
                        <FormField id="rule-from" label="Start date" error={errors.effective_from} hint="Empty = no start date.">
                            <Input id="rule-from" type="date" value={data.effective_from} onChange={(event) => form.setData('effective_from', event.target.value)} />
                        </FormField>
                        <FormField id="rule-to" label="End date" error={errors.effective_to} hint="Empty = no end date.">
                            <Input id="rule-to" type="date" value={data.effective_to} onChange={(event) => form.setData('effective_to', event.target.value)} />
                        </FormField>
                        <FormField id="rule-order" label="Order" error={errors.sort_order} hint="Lower runs first (within earnings or deductions).">
                            <Input id="rule-order" type="number" min={0} max={9999} value={data.sort_order} onChange={(event) => form.setData('sort_order', Number(event.target.value || 0))} />
                        </FormField>
                        <div className="flex items-center justify-between gap-4 rounded-lg border px-3 py-2">
                            <div>
                                <Label htmlFor="rule-active">Active</Label>
                                <p className="text-xs text-muted-foreground">Off = kept but not used.</p>
                            </div>
                            <Switch id="rule-active" checked={data.is_active} onCheckedChange={(checked) => form.setData('is_active', checked)} />
                        </div>
                        <FormField id="rule-notes" label="Notes" error={errors.notes} className="md:col-span-2">
                            <Textarea id="rule-notes" rows={2} value={data.notes} onChange={(event) => form.setData('notes', event.target.value)} />
                        </FormField>
                    </CardContent>
                </Card>

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
                        {rule ? 'Save rule' : 'Create rule'}
                    </Button>
                </div>
            </form>

            <div className="min-w-0 content-start">
                <TestPanel
                    options={test}
                    compare={{ rule: { ...data, code: data.code || 'draft_rule', id: rule?.id ?? null } }}
                    compareLabel="With this rule"
                    validate={validate}
                />
            </div>
        </div>
    );
}

function OptionSelect({ id, value, options, onChange }: { id: string; value: string; options: Option[]; onChange: (value: string) => void }) {
    return (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger id={id} className="w-full">
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

type Check = { state: 'idle' | 'checking' | 'ok' | 'error'; message: string };

function useFormulaCheck(url: string, formula: string, kind: string, ruleId: number | null, enabled: boolean): Check {
    const [check, setCheck] = useState<Check>({ state: 'idle', message: '' });

    useEffect(() => {
        if (!enabled || !formula.trim()) {
            setCheck({ state: 'idle', message: '' });
            return;
        }

        setCheck((current) => ({ ...current, state: 'checking' }));
        const timer = window.setTimeout(async () => {
            try {
                const result = await postJson<{ ok: boolean; message: string }>(url, { formula, kind, rule_id: ruleId });
                if (result) setCheck({ state: result.ok ? 'ok' : 'error', message: result.message });
            } catch {
                setCheck({ state: 'idle', message: '' });
            }
        }, 400);

        return () => window.clearTimeout(timer);
    }, [url, formula, kind, ruleId, enabled]);

    return check;
}

function FormulaStatus({ check }: { check: Check }) {
    if (check.state === 'idle') return null;

    return (
        <p
            className={cn(
                'flex items-center gap-1.5 text-xs',
                check.state === 'ok' && 'text-emerald-700 dark:text-emerald-400',
                check.state === 'error' && 'text-destructive',
                check.state === 'checking' && 'text-muted-foreground',
            )}
            role="status"
        >
            {check.state === 'checking' && <LoaderCircle className="size-3.5 animate-spin" />}
            {check.state === 'ok' && <CircleCheck className="size-3.5" />}
            {check.state === 'error' && <CircleAlert className="size-3.5" />}
            {check.state === 'checking' ? 'Checking...' : check.message}
        </p>
    );
}

/** Plain-language summary of the rule as it is filled in. */
function sentence(data: Values, options: Props['options']): string {
    const label = (list: Option[], value: string) => list.find((option) => option.value === value)?.label.toLowerCase() ?? value;
    const amount = Number(data.amount || 0);
    const how = {
        fixed: peso(amount),
        percent: `${amount}% of ${label(options.bases, data.base)}`,
        per_unit: `${peso(amount)} × ${label(options.units, data.unit)}`,
        formula: data.formula.trim() ? `the result of ${data.formula.trim()}` : 'the formula result',
    }[data.method];

    const who = [
        data.rate_type !== 'all' ? label(options.rateTypes, data.rate_type) : null,
        data.payroll_groups.length ? `in ${data.payroll_groups.map((value) => options.groups.find((group) => group.value === value)?.label ?? value).join(' / ')}` : null,
        data.employee_biometric_ids.length ? `(${data.employee_biometric_ids.length} selected employee${data.employee_biometric_ids.length === 1 ? '' : 's'})` : null,
    ]
        .filter(Boolean)
        .join(' ');

    const when = data.cutoff === 'every' ? 'Every cutoff' : `On the ${label(options.cutoffs, data.cutoff).replace(' only', '')}`;
    const action = data.kind === 'earning' ? 'gets' : 'has';
    const effect = data.kind === 'earning' ? 'added to gross pay' : 'deducted from net pay';

    return `${when}, ${who ? `each employee ${who}` : 'each employee'} ${action} ${how} ${effect}${data.is_active ? '' : ' (rule is off)'}.`;
}
