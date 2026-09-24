import { Eye } from 'lucide-react';
import { useState } from 'react';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { DetailDialog, DetailGrid } from '@/components/modal/detail-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface LogRow {
    id: number;
    date: string | null;
    time: string | null;
    user_name: string;
    user_email: string | null;
    module: string;
    action: string;
    action_label: string;
    payroll_number: string | null;
    employee_name: string | null;
    employee_no: string | null;
    garage_group: string;
    description: string;
    request_id: string | null;
    ip_address: string;
    user_agent: string;
    old_values: string | null;
    new_values: string | null;
    context: string | null;
}

interface Filters {
    search: string;
    module: string;
    action: string;
    user_id: string;
    date_from: string;
    date_to: string;
}

interface Props {
    logs: Paginated<LogRow>;
    modules: Record<string, string>;
    actions: Record<string, string>;
    users: Record<string, string>;
    filters: Filters;
    urls: { index: string };
}

const ACTION_TONE: Record<string, string> = {
    created: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
    deleted: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
};
const actionTone = (action: string) => ACTION_TONE[action] ?? 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400';
const options = (record: Record<string, string>) => Object.entries(record).map(([value, label]) => ({ value, label }));

export default definePage<Props>({
    title: () => 'Payroll Transaction Logs',
    description: () => 'Audit trail of payroll, employee salary, benefits, adjustments, schedules, and settlement changes.',
    Content: PayrollAuditLogs,
});

function PayrollAuditLogs({ logs, modules, actions, users, filters, urls }: Props) {
    const [viewing, setViewing] = useState<LogRow | null>(null);

    const columns: DataTableColumn<LogRow>[] = [
        {
            key: 'when',
            header: 'Date / Time',
            className: 'whitespace-nowrap',
            cell: (log) => (
                <>
                    <div className="font-medium">{log.date}</div>
                    <div className="text-xs text-muted-foreground">{log.time}</div>
                </>
            ),
            filter: { type: 'daterange', from: 'date_from', to: 'date_to' },
        },
        {
            key: 'user',
            header: 'User',
            cell: (log) => (
                <>
                    <div className="font-medium">{log.user_name}</div>
                    {log.user_email && <div className="max-w-44 truncate text-xs text-muted-foreground">{log.user_email}</div>}
                </>
            ),
            filter: { type: 'select', param: 'user_id', options: options(users), placeholder: 'All users' },
        },
        { key: 'module', header: 'Module', cell: (log) => log.module, filter: { type: 'select', param: 'module', options: options(modules), placeholder: 'All modules' } },
        {
            key: 'action',
            header: 'Action',
            cell: (log) => (
                <Badge variant="outline" className={actionTone(log.action)}>
                    {log.action_label}
                </Badge>
            ),
            filter: { type: 'select', param: 'action', options: options(actions), placeholder: 'All actions' },
        },
        {
            key: 'subject',
            header: 'Payroll / Employee',
            hideBelow: 'lg',
            className: 'text-sm',
            cell: (log) => (
                <>
                    {log.payroll_number && <div className="font-medium">{log.payroll_number}</div>}
                    {log.employee_name && <div className="max-w-48 truncate">{log.employee_name}</div>}
                    <div className="text-xs text-muted-foreground">Group: {log.garage_group}</div>
                </>
            ),
        },
        {
            key: 'description',
            header: 'Description',
            className: 'text-sm whitespace-normal',
            cell: (log) => <p className="line-clamp-2 max-w-md">{log.description}</p>,
        },
    ];

    return (
        <>
            <DataTable
                title="Audit trail"
                noun="log"
                paginator={logs}
                url={urls.index}
                filters={filters}
                columns={columns}
                rowKey={(log) => log.id}
                searchPlaceholder="Search description, request ID, payroll, employee or user..."
                emptyText="No payroll audit logs match the current search and filters."
                minWidth={960}
                onRowClick={setViewing}
                rowActions={(log) => (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={(event) => {
                            event.stopPropagation();
                            setViewing(log);
                        }}
                    >
                        <Eye />
                        View
                    </Button>
                )}
            />

            {viewing && (
                <DetailDialog
                    open
                    onOpenChange={(open) => !open && setViewing(null)}
                    size={viewing.old_values || viewing.new_values ? 'xl' : 'lg'}
                    title={`${viewing.action_label} · ${viewing.module}`}
                    description={`${viewing.date ?? ''} ${viewing.time ?? ''} · ${viewing.user_name}`}
                    actions={
                        <Badge variant="outline" className={cn(actionTone(viewing.action))}>
                            {viewing.action_label}
                        </Badge>
                    }
                >
                    <div className="grid gap-4">
                        <DetailGrid
                            columns={3}
                            items={[
                                { label: 'Payroll', value: viewing.payroll_number },
                                { label: 'Employee', value: viewing.employee_name ? `${viewing.employee_name}${viewing.employee_no ? ` (${viewing.employee_no})` : ''}` : null },
                                { label: 'Group', value: viewing.garage_group },
                                { label: 'Description', value: viewing.description, wide: true },
                                { label: 'Request ID', value: <span className="font-mono text-xs">{viewing.request_id || 'N/A'}</span> },
                                { label: 'IP address', value: viewing.ip_address },
                                { label: 'User agent', value: <span className="text-xs">{viewing.user_agent}</span> },
                            ]}
                        />
                        <div className="grid gap-3 lg:grid-cols-3">
                            <JsonBlock title="Before" value={viewing.old_values} />
                            <JsonBlock title="After" value={viewing.new_values} />
                            <JsonBlock title="Context" value={viewing.context} />
                        </div>
                    </div>
                </DetailDialog>
            )}
        </>
    );
}

function JsonBlock({ title, value }: { title: string; value: string | null }) {
    return (
        <div className="min-w-0 rounded-lg border bg-background">
            <div className="border-b px-3 py-2 text-xs font-semibold text-muted-foreground uppercase">{title}</div>
            <pre className="max-h-80 overflow-auto p-3 font-mono text-xs leading-relaxed">{value ?? '—'}</pre>
        </div>
    );
}
