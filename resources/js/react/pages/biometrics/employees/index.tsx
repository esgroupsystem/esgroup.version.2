import { router, useForm } from '@inertiajs/react';
import { Building2, CloudDownload, Link2, Loader2, Pencil, Plus, Save, UserCheck, UserX, Users } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { ModalLink } from '@/components/modal/modal-link';
import { openModal } from '@/components/modal/modal-store';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { definePage } from '@/lib/define-page';
import { initials } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface EmployeeRow {
    id: number;
    display_name: string;
    display_no: string;
    source_no: string;
    group_label: string | null;
    company: string | null;
    active: boolean;
    payroll_included: boolean;
    device_name: string;
    device_sn: string;
    last_check_date: string | null;
    last_check_time: string | null;
    total_logs: number;
    hr_employee: { name: string; url: string } | null;
    edit_url: string;
}

interface Filters {
    search: string;
    employment_status: string;
    biometric_company_id: string;
    group_name: string;
    payroll_active: string;
}

interface Props {
    employees: Paginated<EmployeeRow>;
    companies: { id: number; name: string }[];
    counts: { total: number; active: number; payroll_active: number; inactive: number; without_company: number };
    groups: string[];
    filters: Filters;
    can: { sync: boolean; edit: boolean; createCompany: boolean };
    urls: { index: string; sync: string; companyStore: string };
}

const GROUP_LABELS: Record<string, string> = { '1': 'Mirasol / Balintawak Payroll', '2': 'Gonzales Payroll' };
const pct = (value: number, total: number) => (total > 0 ? Math.round((value / total) * 100) : 0);
const ACTIVE_CLASS = 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400';

export default definePage<Props>({
    title: () => 'Employee Biometrics',
    description: () =>
        'One clean employee record per person, built from every CrossChex source. Sync merges by employee ID, number or name and keeps your manual display fields.',
    actions: (props) => <Actions {...props} />,
    size: 'xl',
    Content: BiometricEmployeesIndex,
});

function Actions({ can, urls }: Props) {
    return (
        <>
            {can.createCompany && <CompanyTagDialog url={urls.companyStore} />}
            {can.sync && <SyncButton url={urls.sync} />}
        </>
    );
}

function SyncButton({ url }: { url: string }) {
    const modal = useModal();
    const [syncing, setSyncing] = useState(false);

    return (
        <Button
            onClick={() => router.post(url, {}, modal.visit({ preserveScroll: true, onStart: () => setSyncing(true), onFinish: () => setSyncing(false) }))}
            disabled={syncing}
        >
            {syncing ? <Loader2 className="animate-spin" /> : <CloudDownload />}
            {syncing ? 'Syncing...' : 'Sync employees from logs'}
        </Button>
    );
}

function BiometricEmployeesIndex({ employees, companies, counts, groups, filters, can, urls }: Props) {
    const modal = useModal();
    // The count cards double as one-click filters.
    const quickFilter = (next: Partial<Filters>) => modal.get(urls.index, { ...filters, employment_status: '', payroll_active: '', biometric_company_id: '', ...next });
    const isOnly = (key: keyof Filters, value: string) => filters[key] === value;

    const columns: DataTableColumn<EmployeeRow>[] = [
        {
            key: 'employee',
            header: 'Employee',
            cell: (row) => (
                <div className="flex items-center gap-3">
                    <Avatar className="size-8 rounded-lg">
                        <AvatarFallback className="rounded-lg text-xs">{initials(row.display_name)}</AvatarFallback>
                    </Avatar>
                    <div className="min-w-0">
                        <div className="max-w-56 truncate font-medium" title={row.display_name}>
                            {row.display_name}
                        </div>
                        <div className="text-xs whitespace-nowrap text-muted-foreground">
                            No. {row.display_no}
                            {row.source_no !== row.display_no && ` · Source ${row.source_no}`}
                        </div>
                        {row.hr_employee ? (
                            <button
                                type="button"
                                className="inline-flex max-w-56 items-center gap-1 truncate text-xs text-primary hover:underline"
                                title={`Open HR profile of ${row.hr_employee.name}`}
                                onClick={(event) => {
                                    event.stopPropagation();
                                    openModal(row.hr_employee!.url, { size: 'xl' });
                                }}
                            >
                                <Link2 className="size-3 shrink-0" />
                                HR: {row.hr_employee.name}
                            </button>
                        ) : (
                            <div className="text-xs text-muted-foreground/70">Not linked to HR</div>
                        )}
                    </div>
                </div>
            ),
            filter: { type: 'select', param: 'group_name', options: groups.map((group) => ({ value: group, label: GROUP_LABELS[group] ?? group })), placeholder: 'All groups' },
        },
        {
            key: 'company',
            header: 'Company / group',
            cell: (row) => (
                <div className="grid justify-items-start gap-1">
                    {row.company ? (
                        <span className="text-sm">{row.company}</span>
                    ) : (
                        <Badge variant="outline" className="border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400">
                            Not tagged
                        </Badge>
                    )}
                    <span className="text-xs text-muted-foreground">{row.group_label ?? 'Ungrouped'}</span>
                </div>
            ),
            filter: { type: 'select', param: 'biometric_company_id', options: companies.map((company) => ({ value: String(company.id), label: company.name })), placeholder: 'All companies' },
        },
        {
            key: 'status',
            header: 'Status',
            cell: (row) => (
                <Badge variant="outline" className={row.active ? ACTIVE_CLASS : 'text-muted-foreground'}>
                    {row.active ? 'Active' : 'Inactive'}
                </Badge>
            ),
            filter: {
                type: 'select',
                param: 'employment_status',
                options: [
                    { value: 'active', label: 'Active' },
                    { value: 'inactive', label: 'Inactive' },
                ],
                placeholder: 'All status',
            },
        },
        {
            key: 'payroll',
            header: 'Payroll',
            cell: (row) => (
                <Badge variant="outline" className={row.payroll_included ? ACTIVE_CLASS : 'text-muted-foreground'}>
                    {row.payroll_included ? 'Included' : 'Excluded'}
                </Badge>
            ),
            filter: {
                type: 'select',
                param: 'payroll_active',
                options: [
                    { value: '1', label: 'Included' },
                    { value: '0', label: 'Excluded' },
                ],
                placeholder: 'All',
            },
        },
        {
            key: 'device',
            header: 'Device',
            hideBelow: 'xl',
            cell: (row) => (
                <>
                    <div className="max-w-44 truncate text-sm" title={row.device_name}>
                        {row.device_name}
                    </div>
                    <div className="text-xs text-muted-foreground">SN {row.device_sn}</div>
                </>
            ),
        },
        {
            key: 'activity',
            header: 'Last check / logs',
            hideBelow: 'lg',
            cell: (row) =>
                row.last_check_date ? (
                    <>
                        <div className="text-sm whitespace-nowrap">
                            {row.last_check_date} <span className="text-muted-foreground">{row.last_check_time}</span>
                        </div>
                        <div className="text-xs text-muted-foreground tabular-nums">{row.total_logs.toLocaleString()} logs</div>
                    </>
                ) : (
                    <span className="text-xs text-muted-foreground">No logs yet</span>
                ),
        },
    ];

    return (
        <>
            <div className="grid grid-cols-2 gap-3 xl:grid-cols-4">
                <Metric
                    icon={<Users />}
                    label="Total records"
                    value={counts.total}
                    caption="Unique biometric employees after sync."
                    selected={!filters.employment_status && !filters.payroll_active && !filters.biometric_company_id}
                    onClick={() => quickFilter({})}
                />
                <Metric
                    icon={<UserCheck />}
                    label="Active"
                    value={counts.active}
                    percent={pct(counts.active, counts.total)}
                    caption={`${counts.payroll_active.toLocaleString()} included in payroll`}
                    tone="bg-emerald-500"
                    selected={isOnly('employment_status', 'active')}
                    onClick={() => quickFilter({ employment_status: 'active' })}
                />
                <Metric
                    icon={<UserX />}
                    label="Inactive"
                    value={counts.inactive}
                    percent={pct(counts.inactive, counts.total)}
                    caption="Archived or resigned."
                    tone="bg-muted-foreground"
                    selected={isOnly('employment_status', 'inactive')}
                    onClick={() => quickFilter({ employment_status: 'inactive' })}
                />
                <Metric
                    icon={<Building2 />}
                    label="No company tagged"
                    value={counts.without_company}
                    percent={pct(counts.without_company, counts.total)}
                    caption="Need a company assignment."
                    tone="bg-amber-500"
                />
            </div>

            <DataTable
                title="Biometric employees"
                description={`${employees.total.toLocaleString()} unique record${employees.total === 1 ? '' : 's'} · edit company, display name, number, status and remarks; source fields stay read-only.`}
                noun="record"
                paginator={employees}
                url={urls.index}
                filters={filters}
                columns={columns}
                rowKey={(row) => row.id}
                rowClassName={(row) => (row.active ? undefined : 'opacity-70')}
                searchPlaceholder="Name, employee no. or CrossChex ID..."
                emptyText="No biometric employees found. Sync from the Biometrics Sync logs to generate records."
                minWidth={640}
                onRowClick={can.edit ? (row) => openModal(row.edit_url, { mode: 'form' }) : undefined}
                rowActions={
                    can.edit
                        ? (row) => (
                              <span onClick={(event) => event.stopPropagation()}>
                                  <Tooltip>
                                      <TooltipTrigger asChild>
                                          <Button variant="ghost" size="icon" className="size-8" asChild>
                                              <ModalLink href={row.edit_url} mode="form" aria-label={`Edit ${row.display_name}`}>
                                                  <Pencil />
                                              </ModalLink>
                                          </Button>
                                      </TooltipTrigger>
                                      <TooltipContent>Edit employee</TooltipContent>
                                  </Tooltip>
                              </span>
                          )
                        : undefined
                }
            />
        </>
    );
}

function CompanyTagDialog({ url }: { url: string }) {
    const modal = useModal();
    const [open, setOpen] = useState(false);
    const form = useForm({ name: '', remarks: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(
            url,
            modal.visit({
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    setOpen(false);
                },
            }),
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline">
                    <Plus />
                    Company tag
                </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-md">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Add company tag</DialogTitle>
                        <DialogDescription>Company, branch or internal grouping you can assign to biometric employees.</DialogDescription>
                    </DialogHeader>
                    <FormField id="company-name" label="Company name" required error={form.errors.name}>
                        <Input
                            id="company-name"
                            required
                            placeholder="Example: Jell Transport"
                            value={form.data.name}
                            aria-invalid={!!form.errors.name}
                            onChange={(event) => form.setData('name', event.target.value)}
                        />
                    </FormField>
                    <FormField id="company-remarks" label="Remarks" error={form.errors.remarks} hint="Optional note for this company tag.">
                        <Input
                            id="company-remarks"
                            placeholder="Optional description"
                            value={form.data.remarks}
                            aria-invalid={!!form.errors.remarks}
                            onChange={(event) => form.setData('remarks', event.target.value)}
                        />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            Save tag
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function Metric({
    icon,
    label,
    value,
    caption,
    percent,
    tone,
    selected,
    onClick,
}: {
    icon: ReactNode;
    label: string;
    value: number;
    caption: string;
    percent?: number;
    tone?: string;
    selected?: boolean;
    onClick?: () => void;
}) {
    const body = (
        <>
            <div className="flex items-center justify-between text-sm text-muted-foreground [&_svg]:size-4">
                {label}
                {icon}
            </div>
            <div className="mt-1 text-2xl font-semibold tabular-nums">{value.toLocaleString()}</div>
            {percent !== undefined && (
                <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-muted">
                    <div className={cn('h-full rounded-full', tone)} style={{ width: `${percent}%` }} />
                </div>
            )}
            <p className="mt-2 text-xs text-muted-foreground">
                {percent !== undefined && `${percent}% · `}
                {caption}
            </p>
        </>
    );
    const className = cn('rounded-xl border bg-card p-4 text-left shadow-xs', selected && 'border-primary ring-1 ring-primary');

    return onClick ? (
        <button type="button" onClick={onClick} aria-pressed={selected} className={cn(className, 'transition-colors hover:bg-accent/50')}>
            {body}
        </button>
    ) : (
        <div className={className}>{body}</div>
    );
}
