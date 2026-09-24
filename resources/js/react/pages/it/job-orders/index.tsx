import { router } from '@inertiajs/react';
import { Check, Clock, Download, Eye, FileSpreadsheet, FileText, ListChecks, Loader, Plus, Sparkles, Trash2, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { useModal } from '@/components/modal/modal-context';
import { ModalLink } from '@/components/modal/modal-link';
import { openModal } from '@/components/modal/modal-store';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { definePage } from '@/lib/define-page';
import { ticketStatusClass } from '@/lib/it-status';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface TicketRow {
    id: number;
    bus: string;
    requester: string;
    issue: string;
    seat: string | null;
    status: string;
    status_label: string;
    date: string;
    actions: { approve: boolean; view: boolean; delete: boolean };
    urls: { view: string; approve: string; disapprove: string; delete: string };
}

interface Props {
    tickets: Paginated<TicketRow>;
    stats: { new: number; pending: number; progress: number; completed: number };
    filters: { tab: string; search: string };
    can: { create: boolean; export: boolean };
    urls: { index: string; create: string; exportPdf: string; exportExcel: string };
}

export default definePage<Props>({
    title: () => 'Tickets Job Order',
    description: () => 'IT job order requests: approve, follow up and close tickets. Click a ticket to open it.',
    actions: ({ can, urls }) => (
        <>
            {can.export && (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline">
                            <Download />
                            Export
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem asChild>
                            <a href={urls.exportPdf}>
                                <FileText />
                                PDF
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a href={urls.exportExcel}>
                                <FileSpreadsheet />
                                Excel
                            </a>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
            {can.create && (
                <Button asChild>
                    <ModalLink href={urls.create} mode="form">
                        <Plus />
                        Create ticket
                    </ModalLink>
                </Button>
            )}
        </>
    ),
    size: 'xl',
    Content: JobOrdersIndex,
});

function JobOrdersIndex({ tickets, stats, filters, urls }: Props) {
    const modal = useModal();
    const tab = (next: string) => modal.get(urls.index, { tab: next, search: filters.search });

    const columns: DataTableColumn<TicketRow>[] = [
        {
            key: 'bus',
            header: 'Bus',
            cell: (ticket) => (
                <>
                    <div className="font-medium whitespace-nowrap">{ticket.bus}</div>
                    <div className="text-xs text-muted-foreground">{ticket.seat ? `Seat ${ticket.seat}` : 'No seat'}</div>
                </>
            ),
        },
        { key: 'issue', header: 'Issue', cell: (ticket) => <Badge variant="secondary">{ticket.issue}</Badge> },
        { key: 'requester', header: 'Requester', hideBelow: 'md', cell: (ticket) => ticket.requester },
        {
            key: 'status',
            header: 'Status',
            cell: (ticket) => (
                <Badge variant="outline" className={ticketStatusClass(ticket.status_label)}>
                    {ticket.status_label}
                </Badge>
            ),
        },
        { key: 'date', header: 'Filed', className: 'tabular-nums whitespace-nowrap', cell: (ticket) => ticket.date },
    ];

    return (
        <>
            <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <TabCard icon={<Sparkles />} label="New today" value={stats.new} />
                <TabCard icon={<Clock className="text-amber-600" />} label="Pending" value={stats.pending} active={filters.tab === 'pending'} onClick={() => tab('pending')} />
                <TabCard icon={<Loader className="text-sky-600" />} label="In progress" value={stats.progress} active={filters.tab === 'progress'} onClick={() => tab('progress')} />
                <TabCard icon={<ListChecks className="text-emerald-600" />} label="Completed" value={stats.completed} active={filters.tab === 'completed'} onClick={() => tab('completed')} />
            </div>

            <DataTable
                title={`${filters.tab === 'progress' ? 'In progress' : filters.tab === 'completed' ? 'Completed' : 'Pending'} tickets`}
                noun="ticket"
                paginator={tickets}
                url={urls.index}
                filters={{ search: filters.search }}
                keepParams={{ tab: filters.tab }}
                columns={columns}
                rowKey={(ticket) => ticket.id}
                searchPlaceholder="Search bus, requester, issue..."
                emptyText="No tickets found."
                minWidth={600}
                onRowClick={(ticket) => ticket.actions.view && openModal(ticket.urls.view, { size: 'xl' })}
                rowClassName={(ticket) => (ticket.actions.view ? undefined : 'cursor-default')}
                rowActions={(ticket) => (
                    <span className="flex gap-1" onClick={(event) => event.stopPropagation()}>
                        {ticket.actions.approve && (
                            <>
                                <ConfirmAction
                                    title="Approve ticket?"
                                    description="This ticket will be approved."
                                    confirmLabel="Yes, approve"
                                    onConfirm={() => router.post(ticket.urls.approve, {}, modal.visit({ preserveScroll: true, preserveState: true }))}
                                    trigger={
                                        <IconButton label="Approve">
                                            <Check className="text-emerald-600" />
                                        </IconButton>
                                    }
                                />
                                <ConfirmAction
                                    title="Disapprove ticket?"
                                    description="This ticket will be disapproved."
                                    confirmLabel="Yes, disapprove"
                                    destructive
                                    onConfirm={() => router.post(ticket.urls.disapprove, {}, modal.visit({ preserveScroll: true, preserveState: true }))}
                                    trigger={
                                        <IconButton label="Disapprove">
                                            <X className="text-destructive" />
                                        </IconButton>
                                    }
                                />
                            </>
                        )}
                        {ticket.actions.view && (
                            <Button variant="ghost" size="icon" className="size-8" asChild>
                                <ModalLink href={ticket.urls.view} size="xl" aria-label="View">
                                    <Eye />
                                </ModalLink>
                            </Button>
                        )}
                        {ticket.actions.delete && (
                            <ConfirmAction
                                title="Delete ticket?"
                                description="This ticket will be permanently removed."
                                confirmLabel="Yes, delete"
                                destructive
                                onConfirm={() => router.delete(ticket.urls.delete, modal.visit({ preserveScroll: true, preserveState: true }))}
                                trigger={
                                    <IconButton label="Delete">
                                        <Trash2 className="text-destructive" />
                                    </IconButton>
                                }
                            />
                        )}
                    </span>
                )}
            />
        </>
    );
}

/** Stat card that doubles as the Pending / In progress / Completed tab. */
function TabCard({ icon, label, value, active, onClick }: { icon: ReactNode; label: string; value: number; active?: boolean; onClick?: () => void }) {
    const body = (
        <>
            <div className="flex items-center justify-between text-sm text-muted-foreground [&_svg]:size-4">
                {label}
                {icon}
            </div>
            <div className="mt-1 text-2xl font-semibold tabular-nums">{value.toLocaleString()}</div>
        </>
    );
    const className = cn('rounded-xl border bg-card p-4 text-left shadow-xs', active && 'border-primary ring-1 ring-primary');

    return onClick ? (
        <button type="button" onClick={onClick} aria-pressed={active} className={cn(className, 'transition-colors hover:bg-accent/50')}>
            {body}
        </button>
    ) : (
        <div className={className}>{body}</div>
    );
}
