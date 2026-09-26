import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Lock, Save } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { useModal } from '@/components/modal/modal-context';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';

interface Employee {
    id: number;
    display_name: string | null;
    display_employee_no: string | null;
    company_name: string | null;
    payroll_group_label: string;
    legacy_id: string;
    source_employee_name: string;
    source_employee_no: string;
    source_crosschex_id: string;
    source_employee_id: string;
    crosschex_account: string;
    crosschex_account_name: string;
    device_name: string;
    device_sn: string;
    last_check_time: string;
    total_logs: number;
}

interface Values {
    biometric_company_id: string;
    group_name: string;
    employment_status: string;
    is_payroll_active: boolean;
    display_employee_no: string;
    display_name: string;
    remarks: string;
    hr_employee_id: string;
}

interface Props {
    employee: Employee;
    values: Values;
    companies: { id: number; name: string }[];
    /** HR employees that can be linked (name matches first). */
    hrEmployees: SearchOption[];
    groupOptions: Record<string, string>;
    can: { update: boolean };
    urls: { index: string; update: string };
}

const NONE = 'none';

export default definePage<Props>({
    title: ({ employee }) => `Edit ${employee.display_name || 'biometric employee'}`,
    description: () => 'Update the manual display fields. CrossChex source fields stay read-only and are preserved during sync.',
    actions: (props) => <BackLink {...props} />,
    size: 'xl',
    Content: BiometricEmployeeEdit,
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

function BiometricEmployeeEdit({ employee, values, companies, hrEmployees, groupOptions, can, urls }: Props) {
    const modal = useModal();
    const form = useForm<Values>(values);
    const errors = form.errors as Partial<Record<keyof Values, string>>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(urls.update, modal.visit({ preserveScroll: true }));
    };


    return (
        <>
            <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div className="grid min-w-0 gap-4">
                    <form onSubmit={submit}>
                        <Card>
                            <CardHeader>
                                <CardTitle>Editable information</CardTitle>
                                <CardDescription>These fields are used across adjustments, summaries, plotting, salary sync, and payroll.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-5 md:grid-cols-2">
                                <Field id="biometric_company_id" label="Company tag" error={errors.biometric_company_id} hint="Company, branch, or internal grouping.">
                                    <Select
                                        value={form.data.biometric_company_id || NONE}
                                        onValueChange={(value) => form.setData('biometric_company_id', value === NONE ? '' : value)}
                                    >
                                        <SelectTrigger id="biometric_company_id" className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value={NONE}>No Company / Not Tagged</SelectItem>
                                            {companies.map((company) => (
                                                <SelectItem key={company.id} value={String(company.id)}>
                                                    {company.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field id="group_name" label="Payroll group" required error={errors.group_name} hint="Determines the payroll source group.">
                                    <Select value={form.data.group_name} onValueChange={(value) => form.setData('group_name', value)}>
                                        <SelectTrigger id="group_name" className="w-full" aria-invalid={!!errors.group_name}>
                                            <SelectValue placeholder="Select payroll group" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(groupOptions).map(([key, label]) => (
                                                <SelectItem key={key} value={key}>
                                                    {label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field
                                    id="employment_status"
                                    label="Employment status"
                                    required
                                    error={errors.employment_status}
                                    hint="Inactive records remain stored but are excluded from new processing."
                                >
                                    <Select value={form.data.employment_status} onValueChange={(value) => form.setData('employment_status', value)}>
                                        <SelectTrigger id="employment_status" className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field id="is_payroll_active" label="Payroll inclusion" error={errors.is_payroll_active}>
                                    <label htmlFor="is_payroll_active" className="flex cursor-pointer items-center justify-between gap-3 rounded-lg border p-3">
                                        <span>
                                            <span className="block text-sm font-medium">Include in payroll workflow</span>
                                            <span className="block text-xs text-muted-foreground">Controls adjustments, summaries, plotting, salary sync, and payroll.</span>
                                        </span>
                                        <Switch
                                            id="is_payroll_active"
                                            checked={form.data.is_payroll_active}
                                            onCheckedChange={(checked) => form.setData('is_payroll_active', checked)}
                                        />
                                    </label>
                                </Field>

                                <Field
                                    id="display_employee_no"
                                    label="Display employee no."
                                    error={errors.display_employee_no}
                                    hint="Employee number displayed in biometric and payroll lists."
                                >
                                    <Input
                                        id="display_employee_no"
                                        placeholder="EMP-0001"
                                        autoComplete="off"
                                        value={form.data.display_employee_no}
                                        aria-invalid={!!errors.display_employee_no}
                                        onChange={(event) => form.setData('display_employee_no', event.target.value)}
                                    />
                                </Field>

                                <Field id="display_name" label="Display name" required error={errors.display_name} hint="Primary employee name shown throughout the system.">
                                    <Input
                                        id="display_name"
                                        required
                                        placeholder="Employee full name"
                                        autoComplete="off"
                                        value={form.data.display_name}
                                        aria-invalid={!!errors.display_name}
                                        onChange={(event) => form.setData('display_name', event.target.value)}
                                    />
                                </Field>

                                <div className="md:col-span-2">
                                    <Field
                                        id="hr_employee_id"
                                        label="Linked HR employee (201 file)"
                                        error={errors.hr_employee_id}
                                        hint="Connect this biometric record to the employee's HR profile when the Employee IDs do not match. Name matches are listed first."
                                    >
                                        <SearchSelect
                                            id="hr_employee_id"
                                            ariaLabel="Linked HR employee"
                                            options={[{ value: '', label: '— Not linked —' }, ...hrEmployees]}
                                            value={form.data.hr_employee_id}
                                            onChange={(value) => form.setData('hr_employee_id', value)}
                                            placeholder="Not linked"
                                            searchPlaceholder="Search HR employee..."
                                            invalid={!!errors.hr_employee_id}
                                        />
                                    </Field>
                                </div>

                                <div className="md:col-span-2">
                                    <Field id="remarks" label="Remarks" error={errors.remarks} hint="Optional internal notes. Do not place passwords or API credentials here.">
                                        <Textarea
                                            id="remarks"
                                            rows={4}
                                            placeholder="Add HR, payroll, transfer, resignation, or duplicate-record notes."
                                            value={form.data.remarks}
                                            aria-invalid={!!errors.remarks}
                                            onChange={(event) => form.setData('remarks', event.target.value)}
                                        />
                                    </Field>
                                </div>

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
                                    {can.update && (
                                        <Button type="submit" disabled={form.processing}>
                                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                            Save changes
                                        </Button>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </form>

                    <Card>
                        <CardHeader className="flex flex-row items-start justify-between gap-2">
                            <div className="grid gap-1.5">
                                <CardTitle>Source biometrics data</CardTitle>
                                <CardDescription>Read-only information captured from the employee's CrossChex source account.</CardDescription>
                            </div>
                            <Badge variant="outline">
                                <Lock />
                                Protected
                            </Badge>
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <SourceItem label="Canonical Bio ID" value={`#${employee.id}`} note="Primary ID used by payroll relationships." />
                            <SourceItem label="Legacy biometric ID" value={employee.legacy_id} note="Retained as a legacy snapshot reference." mono />
                            <SourceItem label="Source employee name" value={employee.source_employee_name} />
                            <SourceItem label="Source employee no." value={employee.source_employee_no} mono />
                            <SourceItem label="CrossChex ID" value={employee.source_crosschex_id} mono />
                            <SourceItem label="Source employee ID" value={employee.source_employee_id} mono />
                            <SourceItem label="CrossChex account" value={employee.crosschex_account} note={employee.crosschex_account_name} />
                            <SourceItem label="Device" value={employee.device_name} note={`SN: ${employee.device_sn}`} />
                            <SourceItem label="Last check time" value={employee.last_check_time} />
                            <SourceItem label="Total logs" value={employee.total_logs.toLocaleString()} />
                        </CardContent>
                    </Card>
                </div>

                <div className="grid content-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Usage guide</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3 text-sm">
                            <Guide title="Company and group">Tag the company and payroll group so the employee appears in the right payroll.</Guide>
                            <Guide title="Active">Active employees are included in attendance and new payroll processing.</Guide>
                            <Guide title="Inactive">Inactive records stay stored for history but are excluded from new processing.</Guide>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

function Field({ id, label, required, error, hint, children }: { id: string; label: string; required?: boolean; error?: string; hint?: string; children: ReactNode }) {
    return (
        <div className="grid content-start gap-1.5">
            <Label htmlFor={id}>
                {label}
                {required && <span className="text-destructive">*</span>}
            </Label>
            {children}
            {(error || hint) && <p className={cn('text-xs', error ? 'text-destructive' : 'text-muted-foreground')}>{error ?? hint}</p>}
        </div>
    );
}

function SourceItem({ label, value, note, mono }: { label: string; value: string; note?: string; mono?: boolean }) {
    return (
        <div className="min-w-0 rounded-lg border bg-muted/30 p-3">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className={cn('truncate font-medium', mono && 'font-mono text-sm')} title={value}>
                {value}
            </div>
            {note && <div className="mt-0.5 truncate text-xs text-muted-foreground">{note}</div>}
        </div>
    );
}

function Guide({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div>
            <div className="font-medium">{title}</div>
            <p className="text-muted-foreground">{children}</p>
        </div>
    );
}
