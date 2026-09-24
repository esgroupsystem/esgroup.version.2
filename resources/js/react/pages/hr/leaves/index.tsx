import { useForm } from '@inertiajs/react';
import { Ban, CheckCircle2, Clock, FileSignature, Info, Loader2, Lock, MoreHorizontal, Pencil, Plus, Send, UserCheck, UserX, Warehouse } from 'lucide-react';
import { useEffect, useState, type FormEvent, type ReactNode } from 'react';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { ModalLink } from '@/components/modal/modal-link';
import { openModal } from '@/components/modal/modal-store';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { initials } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

export interface LeaveKind {
    key: string;
    noun: string;
    title: string;
}

export interface LeaveEmployee {
    name: string;
    employee_no: string;
    position: string;
    garage: string;
    company: string;
    status: string;
}

interface Notice {
    sent_at: string | null;
    proof_url: string | null;
}

interface Tone {
    label: string;
    tone: string;
}

interface Leave {
    id: number;
    employee: LeaveEmployee | null;
    leave_type: string;
    reason: string | null;
    start_date: string | null;
    end_date: string | null;
    days: number;
    notices: { first: Notice; second: Notice; final: Notice };
    status: Tone;
    remaining: Tone;
    ready_at: string | null;
    last_action_note: string | null;
    locked: boolean;
    after_leave: boolean;
    can_update: boolean;
    edit_url: string;
    action_url: string;
}

interface GarageRow {
    garage: string;
    total: number;
    active: number;
    second_notice: number;
    inactive: number;
}

interface Props {
    kind: LeaveKind;
    leaves: Paginated<Leave>;
    counts: Record<string, number>;
    garageSummary: GarageRow[];
    filters: { search: string; status: string; leave_type: string; garage: string };
    leaveTypes: string[];
    garages: string[];
    can: { create: boolean; update: boolean };
    urls: { index: string; create: string };
}

type ActionKey = 'first' | 'second' | 'terminate' | 'cancel' | 'ready';

const ACTIONS: Record<ActionKey, { title: string; subtitle: string; submit: string; warning: string; proof: boolean; variant: 'default' | 'destructive' | 'secondary' }> = {
    first: {
        title: 'Mark 1st notice sent',
        subtitle: 'Record the first warning and upload picture proof.',
        submit: 'Mark 1st notice',
        warning: 'The first warning will be recorded. The employee remains active or on leave.',
        proof: true,
        variant: 'default',
    },
    second: {
        title: 'Mark 2nd notice sent',
        subtitle: 'Record the second warning and set the employee to Inactive.',
        submit: 'Mark 2nd notice + set inactive',
        warning: 'The leave record and employee record will automatically become Inactive.',
        proof: true,
        variant: 'default',
    },
    terminate: {
        title: 'Mark final notice sent',
        subtitle: 'Record the final warning and terminate the employee record.',
        submit: 'Mark final notice',
        warning: 'The leave record and employee record will become Terminated.',
        proof: true,
        variant: 'destructive',
    },
    cancel: {
        title: 'Cancel leave',
        subtitle: 'Cancel the leave and return the employee to Active.',
        submit: 'Cancel leave',
        warning: 'No picture proof is required. The employee will return to Active.',
        proof: false,
        variant: 'secondary',
    },
    ready: {
        title: 'Mark ready for duty',
        subtitle: 'Complete the leave and return the employee to Active.',
        submit: 'Set ready for duty',
        warning: 'No picture proof is required. The leave will become Completed.',
        proof: false,
        variant: 'default',
    },
};

const TONE: Record<string, string> = {
    success: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
    primary: 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400',
    info: 'border-cyan-300 text-cyan-700 dark:border-cyan-800 dark:text-cyan-400',
    warning: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    danger: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    secondary: 'text-muted-foreground',
};

const STATUS_OPTIONS = [
    { value: 'active', label: 'Active / on leave' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'terminated', label: 'Terminated' },
];

const toOptions = (values: string[]) => values.map((value) => ({ value, label: value }));

export default definePage<Props>({
    title: ({ kind }) => `${kind.title} Monitoring`,
    description: () => 'Leave schedules, garage assignment, notices with picture proof, duty status and employee status changes.',
    actions: ({ kind, can, urls }) =>
        can.create && (
            <Button asChild>
                <ModalLink href={urls.create} mode="form">
                    <Plus />
                    Add {kind.noun.toLowerCase()} leave
                </ModalLink>
            </Button>
        ),
    size: 'xl',
    Content: LeavesIndex,
});

function LeavesIndex({ kind, leaves, counts, garageSummary, filters, leaveTypes, garages, urls }: Props) {
    const [action, setAction] = useState<{ leave: Leave; type: ActionKey } | null>(null);
    const noun = kind.noun.toLowerCase();

    const columns: DataTableColumn<Leave>[] = [
        {
            key: 'employee',
            header: kind.noun,
            className: 'align-top',
            cell: (leave) => (
                <div className="flex items-start gap-3">
                    <Avatar className="size-8">
                        <AvatarFallback className="text-xs">{initials(leave.employee?.name ?? 'E')}</AvatarFallback>
                    </Avatar>
                    <div className="min-w-0">
                        <div className="max-w-48 truncate font-medium" title={leave.employee?.name}>
                            {leave.employee?.name ?? 'No employee record'}
                        </div>
                        <div className="text-xs text-muted-foreground">{leave.employee?.employee_no}</div>
                        <div className="max-w-48 truncate text-xs text-muted-foreground">{leave.employee?.position}</div>
                    </div>
                </div>
            ),
        },
        {
            key: 'garage',
            header: 'Garage',
            className: 'align-top',
            cell: (leave) => (
                <>
                    <div className="font-medium whitespace-nowrap">{leave.employee?.garage ?? 'No Garage Assigned'}</div>
                    <div className="text-xs whitespace-nowrap text-muted-foreground">{leave.employee?.company ?? 'No Company'}</div>
                </>
            ),
            filter: { type: 'select', param: 'garage', options: toOptions(garages), placeholder: 'All garages' },
        },
        {
            key: 'leave',
            header: 'Leave',
            className: 'align-top',
            cell: (leave) => (
                <div className="grid max-w-60 gap-1">
                    <div className="flex flex-wrap items-center gap-1.5">
                        <Badge variant="secondary">{leave.leave_type}</Badge>
                        <span className="text-xs font-semibold tabular-nums">
                            {leave.days} day{leave.days === 1 ? '' : 's'}
                        </span>
                    </div>
                    <div className="text-xs whitespace-nowrap">
                        {leave.start_date ?? '-'} → {leave.end_date ?? '-'}
                    </div>
                    <div className="truncate text-xs text-muted-foreground" title={leave.reason ?? 'No reason provided'}>
                        {leave.reason || 'No reason provided'}
                    </div>
                </div>
            ),
            filter: { type: 'select', param: 'leave_type', options: toOptions(leaveTypes), placeholder: 'All types' },
        },
        {
            key: 'notices',
            header: 'Notices',
            hideBelow: 'lg',
            className: 'align-top',
            cell: (leave) => (
                <ol className="grid gap-2">
                    <NoticeStep label="1st notice" notice={leave.notices.first} doneClass="bg-cyan-500" />
                    <NoticeStep label="2nd notice" notice={leave.notices.second} doneClass="bg-amber-500" />
                    <NoticeStep label="Final notice" notice={leave.notices.final} doneClass="bg-red-500" />
                </ol>
            ),
        },
        {
            key: 'status',
            header: 'Record status',
            className: 'align-top',
            cell: (leave) => (
                <div className="grid max-w-56 gap-1">
                    <div className="flex flex-wrap gap-1">
                        <Badge variant="outline" className={TONE[leave.status.tone]}>
                            {leave.status.label}
                        </Badge>
                        {leave.remaining.label !== leave.status.label && (
                            <Badge variant="outline" className={TONE[leave.remaining.tone]}>
                                {leave.remaining.label}
                            </Badge>
                        )}
                    </div>
                    {leave.ready_at && <div className="text-xs text-emerald-700 dark:text-emerald-400">Ready: {leave.ready_at}</div>}
                    {leave.last_action_note && (
                        <div className="truncate text-xs text-muted-foreground" title={leave.last_action_note}>
                            {leave.last_action_note}
                        </div>
                    )}
                </div>
            ),
            filter: { type: 'select', param: 'status', options: STATUS_OPTIONS, placeholder: 'All status' },
        },
    ];

    return (
        <>
            <Alert>
                <Info />
                <AlertTitle>Notice workflow</AlertTitle>
                <AlertDescription>
                    <ol className="grid gap-0.5 sm:grid-cols-3 sm:gap-4">
                        <li>
                            <strong>1st notice</strong> — first warning, picture proof required.
                        </li>
                        <li>
                            <strong>2nd notice</strong> — proof required; the leave and employee become <strong>Inactive</strong> automatically.
                        </li>
                        <li>
                            <strong>Final notice</strong> — proof required; the employee record becomes <strong>Terminated</strong>.
                        </li>
                    </ol>
                    <span className="mt-1 block">Ready for Duty and Cancel Leave need no picture. Use Ready for Duty only when the {noun} has returned and is cleared to work.</span>
                </AlertDescription>
            </Alert>

            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Stat icon={<UserCheck className="text-emerald-600" />} label="Active / on leave" value={counts.active ?? 0} hint="Current active leave records" />
                <Stat icon={<Send className="text-cyan-600" />} label="1st notice" value={counts.first ?? 0} hint="With first notice" />
                <Stat icon={<UserX className="text-amber-600" />} label="2nd notice / inactive" value={counts.second ?? 0} hint="Automatically set to Inactive" />
                <Stat icon={<FileSignature className="text-red-600" />} label="Final / terminated" value={counts.termination ?? 0} hint="Final notice or terminated" />
            </div>

            {garageSummary.length > 0 && (
                <div className="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    {garageSummary.map((row) => (
                        <div key={row.garage} className="flex items-center gap-3 rounded-xl border bg-card px-4 py-3 shadow-xs">
                            <Warehouse className="size-4 shrink-0 text-muted-foreground" />
                            <div className="min-w-0 flex-1">
                                <div className="truncate text-sm font-medium">{row.garage}</div>
                                <div className="text-xs text-muted-foreground tabular-nums">
                                    {row.active} active · {row.second_notice} 2nd notice · {row.inactive} inactive
                                </div>
                            </div>
                            <span className="text-lg font-semibold tabular-nums" title="Total leave records">
                                {row.total}
                            </span>
                        </div>
                    ))}
                </div>
            )}

            <DataTable
                title={`${kind.noun} leave records`}
                noun="record"
                paginator={leaves}
                url={urls.index}
                filters={filters}
                columns={columns}
                rowKey={(leave) => leave.id}
                searchPlaceholder="Search employee, garage, company, status, leave type..."
                emptyText={`No ${noun} leave records found. Create a new leave record or adjust your search.`}
                minWidth={720}
                rowActions={(leave) => (leave.can_update ? <LeaveActions leave={leave} onAction={(type) => setAction({ leave, type })} /> : null)}
            />

            {action && <ActionDialog leave={action.leave} type={action.type} onClose={() => setAction(null)} />}
        </>
    );
}

function LeaveActions({ leave, onAction }: { leave: Leave; onAction: (type: ActionKey) => void }) {
    const showNotices = leave.after_leave && !leave.locked;
    const { first, second, final } = leave.notices;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" aria-label={`Actions for ${leave.employee?.name ?? 'leave'}`}>
                    Actions
                    <MoreHorizontal />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-60">
                {leave.locked ? (
                    <DropdownMenuItem disabled>
                        <Pencil />
                        Edit leave
                    </DropdownMenuItem>
                ) : (
                    <DropdownMenuItem onSelect={() => openModal(leave.edit_url, { mode: 'form' })}>
                        <Pencil />
                        Edit leave
                    </DropdownMenuItem>
                )}
                {!leave.locked && (
                    <DropdownMenuItem onSelect={() => onAction('ready')}>
                        <UserCheck className="text-emerald-600" />
                        Ready for duty
                    </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                {showNotices ? (
                    <>
                        <DropdownMenuItem disabled={!!first.sent_at} onSelect={() => onAction('first')}>
                            {first.sent_at ? <CheckCircle2 className="text-cyan-600" /> : <Send className="text-cyan-600" />}
                            {first.sent_at ? '1st notice sent' : 'Mark 1st notice sent'}
                        </DropdownMenuItem>
                        <DropdownMenuItem disabled={!!second.sent_at || !first.sent_at} onSelect={() => onAction('second')}>
                            {second.sent_at ? <CheckCircle2 className="text-amber-600" /> : !first.sent_at ? <Lock /> : <UserX className="text-amber-600" />}
                            {second.sent_at ? '2nd notice sent / inactive' : !first.sent_at ? 'Send 1st notice first' : 'Mark 2nd notice + inactive'}
                        </DropdownMenuItem>
                        <DropdownMenuItem disabled={!!final.sent_at || !second.sent_at} onSelect={() => onAction('terminate')}>
                            {final.sent_at ? <CheckCircle2 className="text-red-600" /> : !second.sent_at ? <Lock /> : <FileSignature className="text-red-600" />}
                            {final.sent_at ? 'Final notice sent' : !second.sent_at ? 'Send 2nd notice first' : 'Mark final notice'}
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                    </>
                ) : (
                    !leave.locked && (
                        <>
                            <DropdownMenuItem disabled>
                                <Clock />
                                Notices available after leave
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                        </>
                    )
                )}
                <DropdownMenuItem disabled={leave.locked} onSelect={() => onAction('cancel')}>
                    <Ban />
                    Cancel leave
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function ActionDialog({ leave, type, onClose }: { leave: Leave; type: ActionKey; onClose: () => void }) {
    const config = ACTIONS[type];
    const form = useForm<{ action_type: ActionKey; note: string; proof_image: File | null }>({ action_type: type, note: '', proof_image: null });
    const [preview, setPreview] = useState<string | null>(null);
    const errors = form.errors as Record<string, string>;
    const modal = useModal();

    useEffect(() => () => {
        if (preview) URL.revokeObjectURL(preview);
    }, [preview]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(leave.action_url, modal.visit({ forceFormData: true, preserveScroll: true, preserveState: true, onSuccess: onClose }));
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{config.title}</DialogTitle>
                        <DialogDescription>{config.subtitle}</DialogDescription>
                    </DialogHeader>
                    <div className="rounded-lg border bg-muted/40 p-3 text-sm">
                        <div className="font-semibold">{leave.employee?.name ?? 'No employee record'}</div>
                        <div className="text-muted-foreground">{leave.leave_type}</div>
                        <div className="text-muted-foreground">{leave.employee?.garage}</div>
                    </div>
                    <FormField id="action-note" label="Action note" error={errors.note}>
                        <Textarea id="action-note" rows={4} placeholder="Enter notice reference, HR note, or reason." value={form.data.note} onChange={(event) => form.setData('note', event.target.value)} />
                    </FormField>
                    {config.proof && (
                        <FormField id="action-proof" label="Picture proof" required error={errors.proof_image} hint="Required for 1st, 2nd, and Final Notice. Maximum file size: 4 MB.">
                            <Input
                                id="action-proof"
                                type="file"
                                required
                                accept="image/jpeg,image/png,image/webp"
                                aria-invalid={!!errors.proof_image}
                                onChange={(event) => {
                                    const file = event.target.files?.[0] ?? null;
                                    form.setData('proof_image', file);
                                    setPreview(file ? URL.createObjectURL(file) : null);
                                }}
                            />
                            {preview && <img src={preview} alt="Selected proof preview" className="mt-2 max-h-48 rounded-lg border object-contain" />}
                        </FormField>
                    )}
                    {errors.action_type && <p className="text-xs text-destructive">{errors.action_type}</p>}
                    <Alert variant={config.variant === 'destructive' ? 'destructive' : 'default'}>
                        <AlertDescription>{config.warning}</AlertDescription>
                    </Alert>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>
                            Close
                        </Button>
                        <Button type="submit" variant={config.variant} disabled={form.processing || (config.proof && !form.data.proof_image)}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            {config.submit}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function NoticeStep({ label, notice, doneClass }: { label: string; notice: Notice; doneClass: string }) {
    return (
        <li className="flex items-start gap-2 text-xs">
            <span className={cn('mt-1 size-2.5 shrink-0 rounded-full', notice.sent_at ? doneClass : 'bg-muted-foreground/30')} />
            <div>
                <div className="font-medium">{label}</div>
                <div className="text-muted-foreground">{notice.sent_at ?? 'Pending'}</div>
                {notice.proof_url ? (
                    <a href={notice.proof_url} target="_blank" rel="noopener" className="mt-1 inline-flex items-center gap-2 font-medium text-primary hover:underline">
                        <img src={notice.proof_url} alt={`${label} proof`} className="size-10 rounded-md border object-cover" />
                        View proof
                    </a>
                ) : (
                    notice.sent_at && <div className="text-amber-600">No proof uploaded</div>
                )}
            </div>
        </li>
    );
}

function Stat({ icon, label, value, hint }: { icon: ReactNode; label: string; value: number; hint: string }) {
    return (
        <div className="rounded-xl border bg-card p-4 shadow-xs">
            <div className="flex items-center justify-between text-sm text-muted-foreground [&_svg]:size-4">
                {label}
                {icon}
            </div>
            <div className="mt-1 text-2xl font-semibold tabular-nums">{value.toLocaleString()}</div>
            <div className="mt-1 text-xs text-muted-foreground">{hint}</div>
        </div>
    );
}
