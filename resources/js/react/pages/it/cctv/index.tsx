import { router, useForm } from '@inertiajs/react';
import { FileDown, Loader2, Lock, Pencil, Plus, Printer, Save, Trash2, X } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { useModal } from '@/components/modal/modal-context';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { concernStatusClass } from '@/lib/it-status';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface ItemRow {
    it_inventory_item_id: string;
    qty_used: string;
    remarks: string;
}

interface Concern {
    id: number;
    jo_no: string;
    reported_by: string | null;
    bus_body: string | null;
    bus_detail: string | null;
    bus_display: string | null;
    issue_type: string;
    problem_details: string | null;
    action_taken: string | null;
    status: string;
    assigned_to: string;
    assignee: string | null;
    items: (ItemRow & { name: string })[];
}

interface Props {
    concerns: Paginated<Concern>;
    stats: {
        total: number;
        open: number;
        progress: number;
        done: number;
        topIssue: string | null;
        topIssueCount: number;
        topPart: string | null;
        topPartCount: number;
        topAssignee: string | null;
        topAssigneeCount: number;
    };
    buses: SearchOption[];
    agents: { id: string; name: string }[];
    inventoryItems: SearchOption[];
    statuses: string[];
    issueTypes: string[];
    filters: { q: string; status: string };
    openId: number | null;
    reporter: string;
    can: { create: boolean; update: boolean; delete: boolean; export: boolean };
    urls: { index: string; store: string; update: string; destroy: string; print: string; csv: string };
}

const NONE = 'none';
const emptyItem = (): ItemRow => ({ it_inventory_item_id: '', qty_used: '', remarks: '' });


export default definePage<Props>({
    title: () => 'CCTV Job Orders',
    description: () => 'Monitor, assign and resolve CCTV concerns. Parts used are deducted from IT inventory. Click a row to view or update it.',
    actions: (props) => <Actions {...props} />,
    size: 'xl',
    Content: CctvIndex,
});

function Actions(props: Props) {
    const { can, urls } = props;
    const [creating, setCreating] = useState(false);

    return (
        <>
            {can.export && (
                <>
                    <Button variant="outline" asChild>
                        <a href={urls.print} target="_blank" rel="noopener">
                            <Printer />
                            Print
                        </a>
                    </Button>
                    <Button variant="outline" asChild>
                        <a href={urls.csv}>
                            <FileDown />
                            Export
                        </a>
                    </Button>
                </>
            )}
            {can.create && (
                <Button onClick={() => setCreating(true)}>
                    <Plus />
                    New job order
                </Button>
            )}
            {creating && <ConcernDialog {...props} onClose={() => setCreating(false)} />}
        </>
    );
}

function CctvIndex(props: Props) {
    const { concerns, stats, filters, can, urls } = props;
    const modal = useModal();
    const [editing, setEditing] = useState<Concern | null>(() => concerns.data.find((concern) => concern.id === props.openId) ?? null);

    const columns: DataTableColumn<Concern>[] = [
        {
            key: 'jo',
            header: 'Job order',
            cell: (concern) => (
                <>
                    <div className="font-semibold whitespace-nowrap">{concern.jo_no}</div>
                    <div className="text-xs text-muted-foreground">{concern.reported_by || 'No reporter'}</div>
                </>
            ),
        },
        {
            key: 'bus',
            header: 'Bus',
            cell: (concern) => (
                <>
                    <div className="font-medium whitespace-nowrap">{concern.bus_body ?? '—'}</div>
                    {concern.bus_detail && <div className="max-w-44 truncate text-xs text-muted-foreground">{concern.bus_detail}</div>}
                </>
            ),
        },
        {
            key: 'issue',
            header: 'Issue',
            cell: (concern) => (
                <div className="grid max-w-64 gap-1">
                    <Badge variant="secondary">{concern.issue_type}</Badge>
                    {concern.problem_details && (
                        <span className="truncate text-xs text-muted-foreground" title={concern.problem_details}>
                            {concern.problem_details}
                        </span>
                    )}
                </div>
            ),
        },
        {
            key: 'items',
            header: 'Parts used',
            hideBelow: 'xl',
            className: 'text-sm',
            cell: (concern) =>
                concern.items.length === 0 ? (
                    <span className="text-muted-foreground">—</span>
                ) : (
                    <>
                        {concern.items.slice(0, 2).map((item, index) => (
                            <div key={index} className="whitespace-nowrap">
                                {item.name} <span className="font-semibold">x{item.qty_used}</span>
                            </div>
                        ))}
                        {concern.items.length > 2 && <div className="text-xs text-muted-foreground">+{concern.items.length - 2} more</div>}
                    </>
                ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (concern) => (
                <div className="grid justify-items-start gap-1">
                    <Badge variant="outline" className={concernStatusClass(concern.status)}>
                        {concern.status}
                    </Badge>
                    <span className="text-xs text-muted-foreground">{concern.assignee ?? 'Unassigned'}</span>
                </div>
            ),
            filter: { type: 'select', param: 'status', options: props.statuses.map((status) => ({ value: status, label: status })), placeholder: 'All status' },
        },
    ];

    return (
        <>
            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat dot="bg-primary" label="Total" value={stats.total} />
                <Stat dot="bg-amber-500" label="Open" value={stats.open} />
                <Stat dot="bg-sky-500" label="In progress" value={stats.progress} />
                <Stat dot="bg-emerald-500" label="Fixed / closed" value={stats.done} />
            </div>
            <div className="flex flex-wrap gap-x-6 gap-y-1 rounded-xl border bg-card px-4 py-2.5 text-sm shadow-xs">
                <Top label="Top issue" value={stats.topIssue} count={stats.topIssueCount} />
                <Top label="Most used part" value={stats.topPart} count={stats.topPartCount} />
                <Top label="Top assignee" value={stats.topAssignee} count={stats.topAssigneeCount} />
            </div>

            <DataTable
                title="Job order list"
                noun="job order"
                paginator={concerns}
                url={urls.index}
                filters={filters}
                searchParam="q"
                columns={columns}
                rowKey={(concern) => concern.id}
                searchPlaceholder="Search JO, bus, issue..."
                emptyText="No job orders found. Try changing your filters or create a new job order."
                minWidth={640}
                onRowClick={(concern) => setEditing(concern)}
                rowActions={(concern) => (
                    <span className="flex gap-1" onClick={(event) => event.stopPropagation()}>
                        <IconButton label={can.update ? `Update ${concern.jo_no}` : `View ${concern.jo_no}`} onClick={() => setEditing(concern)}>
                            <Pencil />
                        </IconButton>
                        {can.delete && (
                            <ConfirmAction
                                title="Delete this job order?"
                                description={`${concern.jo_no} will be removed and any items used are returned to IT inventory stock.`}
                                confirmLabel="Delete"
                                destructive
                                onConfirm={() => router.delete(urls.destroy.replace('__ID__', String(concern.id)), modal.visit({ preserveScroll: true, preserveState: true }))}
                                trigger={
                                    <IconButton label={`Delete ${concern.jo_no}`}>
                                        <Trash2 className="text-destructive" />
                                    </IconButton>
                                }
                            />
                        )}
                    </span>
                )}
            />

            {editing && <ConcernDialog {...props} concern={editing} onClose={() => setEditing(null)} />}
        </>
    );
}

function ConcernDialog({ concern, buses, agents, inventoryItems, statuses, issueTypes, reporter, can, urls, onClose }: Props & { concern?: Concern; onClose: () => void }) {
    const isEdit = !!concern;
    const modal = useModal();
    const readOnly = isEdit && !can.update;
    const form = useForm({
        bus_no: '',
        issue_type: issueTypes[0] ?? '',
        problem_details: concern?.problem_details ?? '',
        action_taken: concern?.action_taken ?? '',
        status: concern?.status ?? 'Open',
        assigned_to: concern?.assigned_to ?? '',
        items: concern?.items.length ? concern.items.map(({ it_inventory_item_id, qty_used, remarks }) => ({ it_inventory_item_id, qty_used, remarks })) : [emptyItem()],
    });
    const errors = form.errors as Record<string, string>;

    const setItem = (index: number, patch: Partial<ItemRow>) => form.setData('items', form.data.items.map((item, current) => (current === index ? { ...item, ...patch } : item)));
    const removeItem = (index: number) => form.setData('items', form.data.items.length > 1 ? form.data.items.filter((_, current) => current !== index) : [emptyItem()]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = modal.visit({ preserveScroll: true, preserveState: true, onSuccess: onClose });

        if (isEdit) {
            form.transform(({ action_taken, status, assigned_to, items }) => ({ action_taken, status, assigned_to, items }));
            form.put(urls.update.replace('__ID__', String(concern.id)), options);
        } else {
            form.post(urls.store, options);
        }
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
                <form onSubmit={submit} className="grid gap-5">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            {isEdit ? 'Update CCTV job order' : 'Create CCTV job order'}
                            {concern && (
                                <Badge variant="outline" className={concernStatusClass(concern.status)}>
                                    {concern.jo_no} · {concern.status}
                                </Badge>
                            )}
                        </DialogTitle>
                        <DialogDescription>
                            {isEdit ? (
                                <span className="inline-flex items-center gap-1">
                                    <Lock className="size-3" /> Locked fields cannot be edited.
                                </span>
                            ) : (
                                'Fill in the details to create a new job order.'
                            )}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field id="cctv-bus" label="Bus" error={errors.bus_no} required={!isEdit}>
                            {isEdit ? (
                                <Input id="cctv-bus" value={concern.bus_display ?? '—'} readOnly disabled />
                            ) : (
                                <SearchSelect
                                    id="cctv-bus"
                                    options={buses}
                                    value={form.data.bus_no}
                                    onChange={(value) => form.setData('bus_no', value)}
                                    placeholder="Select bus"
                                    searchPlaceholder="Search bus..."
                                    invalid={!!errors.bus_no}
                                />
                            )}
                        </Field>
                        <Field id="cctv-reporter" label="Reported by">
                            <Input id="cctv-reporter" value={concern?.reported_by ?? reporter} readOnly disabled />
                        </Field>
                        <Field id="cctv-issue" label="Issue type" error={errors.issue_type} required={!isEdit}>
                            {isEdit ? (
                                <Input id="cctv-issue" value={concern.issue_type} readOnly disabled />
                            ) : (
                                <Select value={form.data.issue_type} onValueChange={(value) => form.setData('issue_type', value)}>
                                    <SelectTrigger id="cctv-issue" className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {issueTypes.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {type}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            )}
                        </Field>
                        <Field id="cctv-status" label="Status" error={errors.status} required>
                            <Select value={form.data.status} onValueChange={(value) => form.setData('status', value)} disabled={readOnly}>
                                <SelectTrigger id="cctv-status" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {statuses.map((status) => (
                                        <SelectItem key={status} value={status}>
                                            {status}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>
                        <Field id="cctv-assignee" label="Assign to" error={errors.assigned_to} className="md:col-span-2">
                            <Select value={form.data.assigned_to || NONE} onValueChange={(value) => form.setData('assigned_to', value === NONE ? '' : value)} disabled={readOnly}>
                                <SelectTrigger id="cctv-assignee" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={NONE}>Unassigned</SelectItem>
                                    {agents.map((agent) => (
                                        <SelectItem key={agent.id} value={agent.id}>
                                            {agent.name} (IT Officer / Technician)
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>
                    </div>

                    <div className="grid gap-3 rounded-lg border p-4">
                        <div className="flex items-center justify-between gap-2">
                            <div>
                                <div className="text-sm font-semibold">Items used</div>
                                <div className="text-xs text-muted-foreground">Parts consumed are deducted from IT inventory stock.</div>
                            </div>
                            {!readOnly && (
                                <Button type="button" size="sm" variant="outline" onClick={() => form.setData('items', [...form.data.items, emptyItem()])}>
                                    <Plus />
                                    Add item
                                </Button>
                            )}
                        </div>
                        {form.data.items.map((item, index) => (
                            <div key={index} className="grid gap-2 sm:grid-cols-[minmax(0,1fr)_6rem_minmax(0,10rem)_auto] sm:items-start">
                                <div className="grid gap-1">
                                    <SearchSelect
                                        options={inventoryItems}
                                        value={item.it_inventory_item_id}
                                        onChange={(value) => setItem(index, { it_inventory_item_id: value })}
                                        placeholder="Select inventory item"
                                        ariaLabel={`Inventory item ${index + 1}`}
                                        searchPlaceholder="Search item..."
                                        disabled={readOnly}
                                        invalid={!!errors[`items.${index}.it_inventory_item_id`]}
                                    />
                                    {errors[`items.${index}.it_inventory_item_id`] && <p className="text-xs text-destructive">{errors[`items.${index}.it_inventory_item_id`]}</p>}
                                </div>
                                <div className="grid gap-1">
                                    <Input
                                        type="number"
                                        min={1}
                                        placeholder="Qty"
                                        aria-label={`Quantity ${index + 1}`}
                                        value={item.qty_used}
                                        disabled={readOnly}
                                        aria-invalid={!!errors[`items.${index}.qty_used`]}
                                        onChange={(event) => setItem(index, { qty_used: event.target.value })}
                                    />
                                    {errors[`items.${index}.qty_used`] && <p className="text-xs text-destructive">{errors[`items.${index}.qty_used`]}</p>}
                                </div>
                                <Input placeholder="Remarks" aria-label={`Remarks ${index + 1}`} value={item.remarks} disabled={readOnly} onChange={(event) => setItem(index, { remarks: event.target.value })} />
                                {!readOnly && (
                                    <Button type="button" variant="ghost" size="icon" aria-label={`Remove item ${index + 1}`} onClick={() => removeItem(index)}>
                                        <X />
                                    </Button>
                                )}
                            </div>
                        ))}
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field id="cctv-problem" label="Problem details" error={errors.problem_details} required={!isEdit}>
                            <Textarea
                                id="cctv-problem"
                                rows={4}
                                required={!isEdit}
                                readOnly={isEdit}
                                disabled={isEdit}
                                value={form.data.problem_details}
                                aria-invalid={!!errors.problem_details}
                                onChange={(event) => form.setData('problem_details', event.target.value)}
                            />
                        </Field>
                        <Field id="cctv-action" label="Action taken" error={errors.action_taken}>
                            <Textarea
                                id="cctv-action"
                                rows={4}
                                placeholder="Enter action taken..."
                                disabled={readOnly}
                                value={form.data.action_taken}
                                onChange={(event) => form.setData('action_taken', event.target.value)}
                            />
                        </Field>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>
                            {readOnly ? 'Close' : 'Cancel'}
                        </Button>
                        {!readOnly && (
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                {isEdit ? 'Update job order' : 'Save job order'}
                            </Button>
                        )}
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function Field({ id, label, required, error, className, children }: { id: string; label: string; required?: boolean; error?: string; className?: string; children: ReactNode }) {
    return (
        <div className={cn('grid content-start gap-1.5', className)}>
            <Label htmlFor={id}>
                {label}
                {required && <span className="text-destructive">*</span>}
            </Label>
            {children}
            {error && <p className="text-xs text-destructive">{error}</p>}
        </div>
    );
}

function Stat({ dot, label, value }: { dot: string; label: string; value: number }) {
    return (
        <div className="rounded-xl border bg-card p-4 shadow-xs">
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <span className={cn('size-2 rounded-full', dot)} />
                {label}
            </div>
            <div className="mt-1 text-2xl font-semibold tabular-nums">{value.toLocaleString()}</div>
        </div>
    );
}

function Top({ label, value, count }: { label: string; value: string | null; count: number }) {
    return (
        <span className="min-w-0">
            <span className="text-muted-foreground">{label}: </span>
            <span className="font-medium">{value ?? '—'}</span>
            {value && <span className="text-muted-foreground"> ({count})</span>}
        </span>
    );
}
