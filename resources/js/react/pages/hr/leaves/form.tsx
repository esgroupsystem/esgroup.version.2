import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Info, Loader2, Save, User } from 'lucide-react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { SearchSelect } from '@/components/search-select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';

interface Employee {
    value: string;
    name: string;
    employee_no: string;
    position: string;
    garage: string;
    company: string;
    status: string;
}

interface Values {
    employee_id: string;
    leave_type: string;
    start_date: string;
    end_date: string;
    reason: string;
}

interface Props {
    kind: { key: string; noun: string; title: string };
    leave: {
        id: number;
        status: { label: string; tone: string };
        notices: { first: string | null; second: string | null; final: string | null };
        last_action_note: string | null;
    } | null;
    values: Values;
    employees: Employee[];
    leaveTypes: string[];
    urls: { index: string; submit: string };
}

const TONE: Record<string, string> = {
    success: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
    primary: 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400',
    warning: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    danger: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    secondary: 'text-muted-foreground',
};

const dayCount = (start: string, end: string) => {
    if (!start || !end || end < start) return null;

    return Math.round((new Date(`${end}T00:00:00`).getTime() - new Date(`${start}T00:00:00`).getTime()) / 86_400_000) + 1;
};

export default definePage<Props>({
    title: ({ kind, leave }) => `${leave ? 'Edit' : 'Create'} ${kind.title}`,
    description: ({ kind }) => `Record ${kind.noun.toLowerCase()} leave details with garage visibility and notice workflow tracking.`,
    actions: (props) => <BackLink {...props} />,
    size: 'xl',
    Content: LeaveForm,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.index}>
                <ArrowLeft />
                Back to list
            </Link>
        </Button>
    );
}

function LeaveForm({ kind, leave, values, employees, leaveTypes, urls }: Props) {
    const modal = useModal();
    const form = useForm<Values>(values);
    const errors = form.errors as Partial<Record<keyof Values, string>>;
    const selected = employees.find((employee) => employee.value === form.data.employee_id);
    const days = dayCount(form.data.start_date, form.data.end_date);
    const noun = kind.noun.toLowerCase();

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = modal.visit({ preserveScroll: true });
        if (leave) {
            form.put(urls.submit, options);
        } else {
            form.post(urls.submit, options);
        }
    };

    return (
        <>
            <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <form onSubmit={submit}>
                    <Card>
                        <CardHeader className="flex flex-row items-start justify-between gap-2">
                            <div className="grid gap-1.5">
                                <CardTitle>Leave information</CardTitle>
                                <CardDescription>Select the {noun}, leave type and period.</CardDescription>
                            </div>
                            {leave && (
                                <Badge variant="outline" className={TONE[leave.status.tone]}>
                                    {leave.status.label}
                                </Badge>
                            )}
                        </CardHeader>
                        <CardContent className="grid gap-4 md:grid-cols-2">
                            <FormField id="employee_id" label={kind.noun} required error={errors.employee_id} className="md:col-span-2">
                                <SearchSelect
                                    id="employee_id"
                                    options={employees.map((employee) => ({ value: employee.value, label: employee.name, hint: `${employee.garage} · ${employee.position}` }))}
                                    value={form.data.employee_id}
                                    onChange={(value) => form.setData('employee_id', value)}
                                    placeholder={`Select ${noun}`}
                                    searchPlaceholder={`Search ${noun} by name, garage, or position...`}
                                    empty={`No active ${noun} found.`}
                                    invalid={!!errors.employee_id}
                                />
                            </FormField>
                            <FormField id="leave_type" label="Leave type" required error={errors.leave_type}>
                                <Select value={form.data.leave_type} onValueChange={(value) => form.setData('leave_type', value)}>
                                    <SelectTrigger id="leave_type" className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {leaveTypes.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {type}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <div className="hidden md:block" />
                            <FormField id="start_date" label="Start date" required error={errors.start_date}>
                                <Input id="start_date" type="date" required value={form.data.start_date} aria-invalid={!!errors.start_date} onChange={(event) => form.setData('start_date', event.target.value)} />
                            </FormField>
                            <FormField id="end_date" label="End date" required error={errors.end_date} hint={days ? `${days} day(s)` : undefined}>
                                <Input id="end_date" type="date" required min={form.data.start_date || undefined} value={form.data.end_date} aria-invalid={!!errors.end_date} onChange={(event) => form.setData('end_date', event.target.value)} />
                            </FormField>
                            <FormField id="reason" label="Reason / remarks" error={errors.reason} className="md:col-span-2">
                                <Textarea id="reason" rows={4} placeholder="Enter leave reason, supporting details, or HR note." value={form.data.reason} onChange={(event) => form.setData('reason', event.target.value)} />
                            </FormField>
                            <div className="flex justify-end gap-2 md:col-span-2">
                                {modal.inModal ? (
                                    <Button type="button" variant="outline" onClick={modal.close}>
                                        Cancel
                                    </Button>
                                ) : (
                                    <Button type="button" variant="outline" asChild>
                                        <Link href={urls.index}>Cancel</Link>
                                    </Button>
                                )}
                                <Button type="submit" disabled={form.processing}>
                                    {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                    {leave ? 'Update leave' : 'Save leave'}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>

                <div className="grid content-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Selected {noun} details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {selected ? (
                                <div className="grid gap-3 text-sm">
                                    <div>
                                        <div className="text-base font-semibold">{selected.name}</div>
                                        <div className="text-muted-foreground">{selected.employee_no}</div>
                                    </div>
                                    {(
                                        [
                                            ['Position', selected.position],
                                            ['Garage', selected.garage],
                                            ['Company', selected.company],
                                            ['Status', selected.status],
                                        ] as const
                                    ).map(([label, value]) => (
                                        <div key={label} className="flex justify-between gap-2 border-b pb-2 last:border-b-0 last:pb-0">
                                            <span className="text-muted-foreground">{label}</span>
                                            <span className="text-right font-medium">{value}</span>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="flex flex-col items-center gap-2 py-6 text-center text-sm text-muted-foreground">
                                    <User className="size-8" />
                                    Select a {noun} to view garage and employee details.
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {leave ? (
                        <>
                            <Card>
                                <CardHeader>
                                    <CardTitle>Current notice status</CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-2 text-sm">
                                    {(
                                        [
                                            ['1st notice', leave.notices.first],
                                            ['2nd notice', leave.notices.second],
                                            ['Final notice', leave.notices.final],
                                        ] as const
                                    ).map(([label, value]) => (
                                        <div key={label} className="flex justify-between gap-2">
                                            <span className="font-medium">{label}</span>
                                            <span className="text-muted-foreground">{value ?? 'Pending'}</span>
                                        </div>
                                    ))}
                                    <p className="border-t pt-2 text-xs text-muted-foreground">2nd Notice automatically makes the employee Inactive.</p>
                                </CardContent>
                            </Card>
                            {leave.last_action_note && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Last action note</CardTitle>
                                    </CardHeader>
                                    <CardContent className="text-sm text-muted-foreground">{leave.last_action_note}</CardContent>
                                </Card>
                            )}
                        </>
                    ) : (
                        <Alert>
                            <Info />
                            <AlertDescription>
                                New leave starts as Active. After the leave ends, send the 1st, 2nd and Final notices from the list; the 2nd Notice automatically sets the {noun} to Inactive.
                            </AlertDescription>
                        </Alert>
                    )}
                </div>
            </div>
        </>
    );
}
