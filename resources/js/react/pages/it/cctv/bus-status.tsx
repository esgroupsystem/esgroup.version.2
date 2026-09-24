import { Link } from '@inertiajs/react';
import { ArrowLeft, Check } from 'lucide-react';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { useModal } from '@/components/modal/modal-context';
import { openModal } from '@/components/modal/modal-store';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { definePage } from '@/lib/define-page';
import type { Paginated } from '@/types';

interface BusRow {
    id: number;
    body_number: string | null;
    detail: string;
    summary: Record<string, number>;
    total: number;
    url: string | null;
}

interface Props {
    buses: Paginated<BusRow>;
    columns: string[];
    filters: { q: string };
    urls: { index: string; concerns: string };
}

export default definePage<Props>({
    title: () => 'CCTV Bus Status',
    description: () => 'Open and in-progress CCTV concerns per bus. A bus with no active issue is ready for deployment. Click a bus for its full CCTV history.',
    actions: (props) => <BackLink {...props} />,
    size: 'xl',
    Content: BusStatus,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.concerns}>
                <ArrowLeft />
                CCTV concerns
            </Link>
        </Button>
    );
}

function BusStatus({ buses, columns, filters, urls }: Props) {
    const issueCount = buses.data.filter((bus) => bus.total > 0).length;

    const tableColumns: DataTableColumn<BusRow>[] = [
        {
            key: 'bus',
            header: 'Bus',
            cell: (bus) => (
                <>
                    <div className="font-semibold whitespace-nowrap">{bus.body_number || '—'}</div>
                    <div className="max-w-52 truncate text-xs text-muted-foreground" title={bus.detail}>
                        {bus.detail || 'No body number'}
                    </div>
                </>
            ),
        },
        ...columns.map<DataTableColumn<BusRow>>((column) => ({
            key: column,
            header: column,
            align: 'center',
            hideBelow: 'lg',
            cell: (bus) => {
                const count = bus.summary[column] ?? 0;

                return count > 0 ? (
                    <Badge variant="outline" className="border-red-300 text-red-700 dark:border-red-800 dark:text-red-400">
                        {count}
                    </Badge>
                ) : (
                    <Check className="mx-auto size-4 text-emerald-600" aria-label="No issues" />
                );
            },
        })),
        {
            key: 'total',
            header: 'Status',
            align: 'right',
            cell: (bus) =>
                bus.total > 0 ? (
                    <Badge variant="destructive">{bus.total} active</Badge>
                ) : (
                    <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">
                        Ready
                    </Badge>
                ),
        },
    ];

    return (
        <DataTable
            title="Bus issue monitoring"
            description={`${buses.total.toLocaleString()} bus${buses.total === 1 ? '' : 'es'} · ${issueCount} on this page with active CCTV issues`}
            noun="bus"
            paginator={buses}
            url={urls.index}
            filters={filters}
            searchParam="q"
            columns={tableColumns}
            rowKey={(bus) => bus.id}
            rowClassName={(bus) => (bus.total > 0 ? 'bg-red-50/40 dark:bg-red-950/10' : undefined)}
            searchPlaceholder="Search bus, plate, name, garage..."
            emptyText="No buses found."
            minWidth={420}
            onRowClick={(bus) => bus.url && openModal(bus.url, { size: 'xl' })}
        />
    );
}
