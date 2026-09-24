import { Link, router } from '@inertiajs/react';
import { Check, CircleCheck, CircleX, CloudRain, Hourglass, Pencil, Plus, RefreshCw, Trash2, Users, X } from 'lucide-react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { ModalLink } from '@/components/modal/modal-link';
import { StatCard } from '@/components/page-header';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { definePage } from '@/lib/define-page';
import { initials } from '@/lib/format';
import type { Paginated } from '@/types';

interface AdjustmentRow {
    id: number;
    is_disaster: boolean;
    disaster_hours: number | null;
    employee: {
        name: string;
        employee_no: string | null;
        employee_biometric_id: number | null;
        biometric_employee_id: string | null;
    };
    type: string;
    type_label: string;
    status: 'pending' | 'approved' | 'rejected';
    period_label: string;
    day_type_label: string;
    adjusted_time_label: string;
    offset: { proof_label: string; approved_hours: number | null; paid_payroll_number: string | null } | null;
    effect: string;
    effect_positive: boolean;
    ignore_late: boolean;
    ignore_undertime: boolean;
    encoder_name: string | null;
    encoded_at: string | null;
    can_decide: boolean;
    approve_title: string;
    reject_title: string;
    approve_confirm: string;
    reject_confirm: string;
    urls: { edit: string; destroy: string; approve: string; reject: string };
}

interface Props {
    adjustments: Paginated<AdjustmentRow>;
    stats: Record<string, number>;
    filters: { search: string; type: string; date_from: string; date_to: string; group_name: string; status: string };
    types: Record<string, string>;
    groups: Record<string, string>;
    can: { create: boolean; update: boolean; delete: boolean; approve: boolean };
    urls: { index: string; create: string };
}

const options = (record: Record<string, string>) => Object.entries(record).map(([value, label]) => ({ value, label }));

const STATUS_OPTIONS = [
    { value: 'pending', label: 'Pending approval' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
];

export default definePage<Props>({
    title: () => 'Payroll Attendance Adjustments',
    description: () => 'Leave, schedule, offset, official business, overtime, holiday work, salary and disaster adjustments that feed payroll.',
    actions: ({ can, urls }) => (
        <>
            <Button variant="outline" asChild>
                <Link href={urls.index} preserveState={false}>
                    <RefreshCw />
                    Refresh
                </Link>
            </Button>
            {can.create && (
                <Button asChild>
                    <ModalLink href={urls.create} mode="form">
                        <Plus />
                        New Adjustment
                    </ModalLink>
                </Button>
            )}
        </>
    ),
    Content: AdjustmentsIndex,
});

function AdjustmentsIndex({ adjustments, stats, filters, types, groups, can, urls }: Props) {
    const columns: DataTableColumn<AdjustmentRow>[] = [
        {
            key: 'employee',
            header: 'Employee',
            cell: (item) => <EmployeeCell item={item} />,
            filter: { type: 'select', param: 'group_name', options: options(groups), placeholder: 'All groups' },
        },
        {
            key: 'type',
            header: 'Type',
            cell: (item) => (
                <Badge variant="secondary" className="h-auto max-w-48 justify-start text-left whitespace-normal" title={item.type_label}>
                    {item.is_disaster && <CloudRain />}
                    {item.type_label}
                </Badge>
            ),
            filter: { type: 'select', param: 'type', options: options(types), placeholder: 'All types' },
        },
        {
            key: 'status',
            header: 'Status',
            cell: (item) => <StatusBadge status={item.status} />,
            filter: { type: 'select', param: 'status', options: STATUS_OPTIONS, placeholder: 'All statuses' },
        },
        {
            key: 'period',
            header: 'Period / Date',
            cell: (item) => (
                <>
                    <div className="font-medium whitespace-nowrap">{item.period_label}</div>
                    <div className="text-xs text-muted-foreground">{item.day_type_label}</div>
                </>
            ),
            filter: { type: 'daterange', from: 'date_from', to: 'date_to' },
        },
        {
            key: 'effect',
            header: 'Time & payroll effect',
            cell: (item) => (
                <div className="grid max-w-60 gap-1">
                    <span className="text-sm">{item.adjusted_time_label}</span>
                    <div className="flex flex-wrap gap-1">
                        <Badge variant={item.effect_positive ? 'secondary' : 'outline'}>{item.effect}</Badge>
                        {item.ignore_late && <Badge variant="outline">Ignore Late</Badge>}
                        {item.ignore_undertime && <Badge variant="outline">Ignore UT</Badge>}
                    </div>
                    {item.type === 'overtime' && <p className="text-xs text-muted-foreground">Ordinary day: Daily Rate ÷ 8 × 125% × approved OT hours.</p>}
                </div>
            ),
        },
        {
            key: 'offset',
            header: 'Offset proof',
            hideBelow: '2xl',
            cell: (item) =>
                item.offset ? (
                    <div className="max-w-56 space-y-1 text-sm">
                        <div>{item.offset.proof_label}</div>
                        {item.offset.approved_hours !== null && <div className="text-xs">Credit: {item.offset.approved_hours.toFixed(2)} hr(s)</div>}
                        {item.offset.paid_payroll_number && <Badge variant="outline">Applied in {item.offset.paid_payroll_number}</Badge>}
                    </div>
                ) : (
                    <span className="text-xs text-muted-foreground">—</span>
                ),
        },
        {
            key: 'encoded',
            header: 'Encoded by',
            hideBelow: '2xl',
            className: 'text-sm',
            cell: (item) => (
                <>
                    <div>{item.encoder_name ?? 'N/A'}</div>
                    <div className="text-xs whitespace-nowrap text-muted-foreground">{item.encoded_at ?? '—'}</div>
                </>
            ),
        },
    ];

    return (
        <>
            <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <StatCard label="Total adjustments" value={(stats.total ?? 0).toLocaleString()} />
                <StatCard label="Pending approval" value={(stats.pending ?? 0).toLocaleString()} />
                <StatCard label="Leave adjustments" value={(stats.leaves ?? 0).toLocaleString()} />
                <StatCard label="Offset requests" value={(stats.offsets ?? 0).toLocaleString()} />
                <StatCard label="Manual time" value={(stats.manual_time ?? 0).toLocaleString()} />
                <StatCard label="Typhoon / Disaster" value={(stats.disasters ?? 0).toLocaleString()} />
            </div>

            <DataTable
                title="Adjustment records"
                noun="record"
                paginator={adjustments}
                url={urls.index}
                filters={filters}
                columns={columns}
                rowKey={(item) => item.id}
                searchPlaceholder="Search employee, number, type or reason..."
                emptyText="No payroll attendance adjustments match the current search and filters."
                minWidth={1000}
                rowActions={(item) => <RowActions item={item} can={can} />}
            />
        </>
    );
}

function EmployeeCell({ item }: { item: AdjustmentRow }) {
    if (item.is_disaster) {
        return (
            <div className="flex items-start gap-3">
                <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted">
                    <Users className="size-4" />
                </div>
                <div>
                    <div className="font-medium">All Qualified Employees</div>
                    <div className="text-xs text-muted-foreground">{item.disaster_hours ?? 3} paid biometric hour threshold</div>
                </div>
            </div>
        );
    }

    return (
        <div className="flex items-start gap-3">
            <Avatar className="size-8 rounded-lg">
                <AvatarFallback className="rounded-lg text-xs">{initials(item.employee.name)}</AvatarFallback>
            </Avatar>
            <div className="min-w-0">
                <div className="max-w-52 truncate font-medium" title={item.employee.name}>
                    {item.employee.name}
                </div>
                <div className="text-xs whitespace-nowrap text-muted-foreground">
                    No. {item.employee.employee_no || 'N/A'} · Bio ID {item.employee.employee_biometric_id ?? 'N/A'}
                </div>
            </div>
        </div>
    );
}

function StatusBadge({ status }: { status: AdjustmentRow['status'] }) {
    if (status === 'pending') {
        return (
            <Badge variant="outline" className="border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400">
                <Hourglass />
                Pending
            </Badge>
        );
    }

    if (status === 'rejected') {
        return (
            <Badge variant="outline" className="border-destructive/40 text-destructive">
                <CircleX />
                Rejected
            </Badge>
        );
    }

    return (
        <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">
            <CircleCheck />
            Approved
        </Badge>
    );
}

function RowActions({ item, can }: { item: AdjustmentRow; can: Props['can'] }) {
    return (
        <>
            {item.can_decide &&
                (can.approve ? (
                    <>
                        <ConfirmAction
                            title={item.approve_title}
                            description={item.approve_confirm}
                            confirmLabel="Approve"
                            onConfirm={() => router.post(item.urls.approve, {}, { preserveScroll: true, preserveState: true })}
                            trigger={
                                <IconButton label={`${item.approve_title} (Head Manager / Payroll Finalizer)`}>
                                    <Check className="text-emerald-600" />
                                </IconButton>
                            }
                        />
                        <ConfirmAction
                            title={item.reject_title}
                            description={item.reject_confirm}
                            confirmLabel="Reject"
                            destructive
                            onConfirm={() =>
                                router.post(
                                    item.urls.reject,
                                    { rejection_reason: 'Rejected by Head Manager / authorized payroll finalizer from adjustment list.' },
                                    { preserveScroll: true, preserveState: true },
                                )
                            }
                            trigger={
                                <IconButton label={`${item.reject_title} (Head Manager / Payroll Finalizer)`}>
                                    <X className="text-destructive" />
                                </IconButton>
                            }
                        />
                    </>
                ) : (
                    <Badge variant="outline">Manager approval required</Badge>
                ))}
            {can.update && (
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Button variant="ghost" size="icon" className="size-8" asChild>
                            <ModalLink href={item.urls.edit} mode="form" aria-label="Edit adjustment">
                                <Pencil />
                            </ModalLink>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Edit adjustment</TooltipContent>
                </Tooltip>
            )}
            {can.delete && (
                <ConfirmAction
                    title="Delete adjustment?"
                    description="Delete this payroll attendance adjustment? This action cannot be undone."
                    confirmLabel="Delete"
                    destructive
                    onConfirm={() => router.delete(item.urls.destroy, { preserveScroll: true, preserveState: true })}
                    trigger={
                        <IconButton label="Delete adjustment">
                            <Trash2 className="text-destructive" />
                        </IconButton>
                    }
                />
            )}
        </>
    );
}
