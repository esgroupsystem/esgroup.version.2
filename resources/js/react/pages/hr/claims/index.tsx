import { router, useForm } from '@inertiajs/react';
import { Loader2, Pencil, Plus, Save, Trash2 } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface Claim {
    id: number;
    employee_id: string;
    employee: string | null;
    claim_type: string;
    status: string;
    reference_no: string | null;
    date_of_notification: string | null;
    date_filed: string | null;
    approval_date: string | null;
    fund_request_date: string | null;
    fund_released_date: string | null;
    amount: string | null;
    remarks: string | null;
}

interface Filters {
    q: string;
    employee_id: string;
    claim_type: string;
    status: string;
    date_field: string;
    date_from: string;
    date_to: string;
}

interface Props {
    claims: Paginated<Claim>;
    statusCounts: Record<string, number>;
    employees: SearchOption[];
    types: string[];
    statuses: string[];
    dateFields: Record<string, string>;
    filters: Filters;
    can: { create: boolean; update: boolean; delete: boolean };
    urls: { index: string; store: string; update: string; destroy: string };
}

const STATUS_TONE: Record<string, string> = {
    Draft: 'text-muted-foreground',
    Ongoing: 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400',
    Approved: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
    Requested: 'border-violet-300 text-violet-700 dark:border-violet-800 dark:text-violet-400',
    Released: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    Rejected: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
};

const TILES: [string, string][] = [
    ['Draft', 'Pending input'],
    ['Ongoing', 'Submitted claims'],
    ['Approved', 'Approved by SSS'],
    ['Released', 'Fund released'],
];

/** The claim's progress, in order. */
const STEPS: { key: keyof Claim; label: string }[] = [
    { key: 'date_of_notification', label: 'Notified' },
    { key: 'date_filed', label: 'Filed' },
    { key: 'approval_date', label: 'Approved' },
    { key: 'fund_request_date', label: 'Fund requested' },
    { key: 'fund_released_date', label: 'Released' },
];

const date = (value: string | null) => (value ? new Date(`${value}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '');
const toOptions = (values: string[]) => values.map((value) => ({ value, label: value }));

export default definePage<Props>({
    title: () => 'Claims',
    description: () => 'SSS, maternity and paternity claims per employee, from notification to fund release.',
    actions: (props) => <Actions {...props} />,
    size: 'xl',
    Content: ClaimsIndex,
});

function Actions(props: Props) {
    const [creating, setCreating] = useState(false);
    if (!props.can.create) return null;

    return (
        <>
            <Button onClick={() => setCreating(true)}>
                <Plus />
                New claim
            </Button>
            {creating && <ClaimDialog {...props} onClose={() => setCreating(false)} />}
        </>
    );
}

function ClaimsIndex(props: Props) {
    const { claims, statusCounts, employees, types, statuses, dateFields, filters, can, urls } = props;
    const modal = useModal();
    const [editing, setEditing] = useState<Claim | null>(null);
    const go = (next: Partial<Filters>) => modal.get(urls.index, { ...filters, ...next });

    const columns: DataTableColumn<Claim>[] = [
        {
            key: 'employee',
            header: 'Employee',
            cell: (claim) => (
                <>
                    <div className="max-w-52 truncate font-medium" title={claim.employee ?? undefined}>
                        {claim.employee ?? '—'}
                    </div>
                    <div className="text-xs text-muted-foreground">{claim.reference_no ? `Ref # ${claim.reference_no}` : 'No reference # yet'}</div>
                </>
            ),
        },
        {
            key: 'type',
            header: 'Type',
            className: 'font-medium whitespace-nowrap',
            cell: (claim) => claim.claim_type,
            filter: { type: 'select', param: 'claim_type', options: toOptions(types), placeholder: 'All types' },
        },
        {
            key: 'status',
            header: 'Status',
            cell: (claim) => (
                <Badge variant="outline" className={STATUS_TONE[claim.status]}>
                    {claim.status}
                </Badge>
            ),
            filter: { type: 'select', param: 'status', options: toOptions(statuses), placeholder: 'All status' },
        },
        {
            key: 'timeline',
            header: `Progress (${dateFields[filters.date_field || 'date_filed'] ?? 'dates'})`,
            hideBelow: 'md',
            cell: (claim) => <Timeline claim={claim} />,
            filter: { type: 'daterange', from: 'date_from', to: 'date_to' },
        },
        {
            key: 'amount',
            header: 'Amount',
            align: 'right',
            className: 'tabular-nums whitespace-nowrap',
            cell: (claim) => (claim.amount ? Number(claim.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : <span className="text-muted-foreground">—</span>),
        },
    ];

    return (
        <>
            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {TILES.map(([status, hint]) => (
                    <div key={status} className="rounded-xl border bg-card p-4 shadow-xs">
                        <div className="flex items-center justify-between text-sm text-muted-foreground">
                            {status}
                            <span className={cn('size-2 rounded-full', status === 'Draft' ? 'bg-muted-foreground/40' : status === 'Ongoing' ? 'bg-sky-500' : status === 'Approved' ? 'bg-emerald-500' : 'bg-amber-500')} />
                        </div>
                        <div className="mt-1 text-2xl font-semibold tabular-nums">{statusCounts[status] ?? 0}</div>
                        <div className="text-xs text-muted-foreground">{hint}</div>
                    </div>
                ))}
            </div>

            <DataTable
                title="Claims"
                noun="claim"
                paginator={claims}
                url={urls.index}
                filters={filters}
                searchParam="q"
                columns={columns}
                rowKey={(claim) => claim.id}
                searchPlaceholder="Search employee / ref # ..."
                emptyText="No claims found. Try clearing filters or create a new claim."
                minWidth={640}
                toolbar={
                    <>
                        <div className="w-52">
                            <SearchSelect
                                ariaLabel="Filter by employee"
                                options={employees}
                                value={filters.employee_id}
                                onChange={(employee_id) => go({ employee_id })}
                                placeholder="All employees"
                                searchPlaceholder="Search employee..."
                            />
                        </div>
                        {filters.employee_id && (
                            <Button variant="ghost" size="sm" onClick={() => go({ employee_id: '' })}>
                                Clear employee
                            </Button>
                        )}
                        <Select value={filters.date_field || 'date_filed'} onValueChange={(date_field) => go({ date_field })}>
                            <SelectTrigger size="sm" className="w-44" aria-label="Date range applies to">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(dateFields).map(([key, label]) => (
                                    <SelectItem key={key} value={key}>
                                        Dates: {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </>
                }
                onRowClick={(claim) => setEditing(claim)}
                rowActions={(claim) => (
                    <span className="flex gap-1" onClick={(event) => event.stopPropagation()}>
                        <IconButton label={can.update ? 'Edit claim' : 'View claim'} onClick={() => setEditing(claim)}>
                            <Pencil />
                        </IconButton>
                        {can.delete && (
                            <ConfirmAction
                                title="Delete this claim?"
                                description={`The ${claim.claim_type} claim of ${claim.employee ?? 'this employee'} will be deleted.`}
                                confirmLabel="Delete"
                                destructive
                                onConfirm={() => router.delete(urls.destroy.replace('__ID__', String(claim.id)), modal.visit({ preserveScroll: true, preserveState: true }))}
                                trigger={
                                    <IconButton label="Delete claim">
                                        <Trash2 className="text-destructive" />
                                    </IconButton>
                                }
                            />
                        )}
                    </span>
                )}
            />

            {editing && <ClaimDialog {...props} claim={editing} onClose={() => setEditing(null)} />}
        </>
    );
}

/** Five claim dates as one compact progress line; the latest reached step is labelled. */
function Timeline({ claim }: { claim: Claim }) {
    const reached = STEPS.filter((step) => claim[step.key]);
    const last = reached[reached.length - 1];

    return (
        <div className="grid gap-1">
            <div className="flex items-center gap-1" aria-label={`${reached.length} of ${STEPS.length} steps`}>
                {STEPS.map((step) => (
                    <span
                        key={step.key}
                        title={`${step.label}: ${date(claim[step.key] as string | null) || 'pending'}`}
                        className={cn('h-1.5 w-6 rounded-full', claim[step.key] ? (claim.status === 'Rejected' ? 'bg-red-400' : 'bg-emerald-500') : 'bg-muted')}
                    />
                ))}
            </div>
            <div className="text-xs whitespace-nowrap text-muted-foreground">{last ? `${last.label} ${date(claim[last.key] as string)}` : 'No dates yet'}</div>
        </div>
    );
}

function ClaimDialog({ claim, employees, types, statuses, can, urls, onClose }: Props & { claim?: Claim; onClose: () => void }) {
    const modal = useModal();
    const readOnly = !!claim && !can.update;
    const form = useForm({
        employee_id: claim?.employee_id ?? '',
        claim_type: claim?.claim_type ?? types[0],
        status: claim?.status ?? statuses[0],
        reference_no: claim?.reference_no ?? '',
        amount: claim?.amount ?? '',
        date_of_notification: claim?.date_of_notification ?? '',
        date_filed: claim?.date_filed ?? '',
        approval_date: claim?.approval_date ?? '',
        fund_request_date: claim?.fund_request_date ?? '',
        fund_released_date: claim?.fund_released_date ?? '',
        remarks: claim?.remarks ?? '',
    });
    const errors = form.errors as Record<string, string>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = modal.visit({ preserveScroll: true, preserveState: true, onSuccess: onClose });

        if (claim) {
            form.put(urls.update.replace('__ID__', String(claim.id)), options);
        } else {
            form.post(urls.store, options);
        }
    };

    const choice = (key: 'claim_type' | 'status', options: string[]) => (
        <Select value={form.data[key]} onValueChange={(value) => form.setData(key, value)} disabled={readOnly}>
            <SelectTrigger id={`claim-${key}`} className="w-full">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {options.map((option) => (
                    <SelectItem key={option} value={option}>
                        {option}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                <form onSubmit={submit} className="grid gap-5">
                    <DialogHeader>
                        <DialogTitle>{claim ? (readOnly ? 'Claim details' : 'Update claim') : 'Create claim'}</DialogTitle>
                        <DialogDescription>{claim ? 'Dates, status and reference number of this claim.' : 'Fill up the details then save.'}</DialogDescription>
                    </DialogHeader>

                    <fieldset className="grid gap-4 sm:grid-cols-2">
                        <legend className="mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">Claim</legend>
                        <FormField id="claim-employee" label="Employee" required error={errors.employee_id} className="sm:col-span-2">
                            <SearchSelect
                                id="claim-employee"
                                options={employees}
                                value={form.data.employee_id}
                                onChange={(value) => form.setData('employee_id', value)}
                                placeholder="-- Select employee --"
                                searchPlaceholder="Search employee..."
                                invalid={!!errors.employee_id}
                                disabled={readOnly}
                            />
                        </FormField>
                        <FormField id="claim-claim_type" label="Claim type" required error={errors.claim_type}>
                            {choice('claim_type', types)}
                        </FormField>
                        <FormField id="claim-status" label="Status" required error={errors.status}>
                            {choice('status', statuses)}
                        </FormField>
                        <FormField id="claim-reference" label="Reference #" error={errors.reference_no}>
                            <Input id="claim-reference" placeholder="SSS reference #" disabled={readOnly} value={form.data.reference_no} onChange={(event) => form.setData('reference_no', event.target.value)} />
                        </FormField>
                        <FormField id="claim-amount" label="Amount (optional)" error={errors.amount}>
                            <Input id="claim-amount" type="number" step="0.01" min={0} disabled={readOnly} value={form.data.amount} onChange={(event) => form.setData('amount', event.target.value)} />
                        </FormField>
                    </fieldset>

                    <fieldset className="grid gap-4 sm:grid-cols-2">
                        <legend className="mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">Progress dates</legend>
                        {STEPS.map((step, index) => {
                            const key = step.key as 'date_of_notification' | 'date_filed' | 'approval_date' | 'fund_request_date' | 'fund_released_date';

                            return (
                                <FormField key={key} id={`claim-${key}`} label={`${index + 1}. ${step.label}`} error={errors[key]}>
                                    <Input id={`claim-${key}`} type="date" disabled={readOnly} value={form.data[key]} aria-invalid={!!errors[key]} onChange={(event) => form.setData(key, event.target.value)} />
                                </FormField>
                            );
                        })}
                    </fieldset>

                    <FormField id="claim-remarks" label="Remarks" error={errors.remarks}>
                        <Textarea id="claim-remarks" rows={3} disabled={readOnly} value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Close
                        </Button>
                        {!readOnly && (claim || can.create) && (
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                {claim ? 'Update' : 'Save'}
                            </Button>
                        )}
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

