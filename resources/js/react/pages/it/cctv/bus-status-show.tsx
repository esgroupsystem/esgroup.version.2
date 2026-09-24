import { Link } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, CheckCircle2, ChevronLeft, ChevronRight, Clock, History, Package } from 'lucide-react';
import type { ReactNode } from 'react';
import { useModal } from '@/components/modal/modal-context';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { definePage } from '@/lib/define-page';
import { concernStatusClass } from '@/lib/it-status';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface ConcernCard {
    id: number;
    jo_no: string;
    issue_type: string;
    status: string;
    overdue: boolean;
    assignee: string | null;
    problem_details: string | null;
    action_taken: string | null;
    parts: string[];
}

interface Props {
    bus: { body_number: string; display_name: string; plate_number: string | null; garage: string | null };
    statusSummary: Record<string, number>;
    totalIssues: number;
    completedCount: number;
    partsSummary: { name: string; qty: number; unit: string }[];
    activeJobOrders: Paginated<ConcernCard>;
    completedJobOrders: Paginated<ConcernCard>;
    timeline: { id: number; jo_no: string; issue_type: string; status: string; updated_at: string | null }[];
    issueOptions: string[];
    statusOptions: string[];
    filters: { issue: string; status: string };
    urls: { self: string; back: string };
}

const ALL = 'all';

export default definePage<Props>({
    title: ({ bus }) => `${bus.body_number} CCTV details`,
    description: ({ bus }) => [bus.display_name, bus.plate_number && `Plate ${bus.plate_number}`, bus.garage].filter(Boolean).join(' · '),
    actions: (props) => <BackLink {...props} />,
    size: 'xl',
    Content: BusStatusShow,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.back}>
                <ArrowLeft />
                Bus dashboard
            </Link>
        </Button>
    );
}

function BusStatusShow(props: Props) {
    const { statusSummary, totalIssues, completedCount, partsSummary, activeJobOrders, completedJobOrders, timeline, filters, urls } = props;
    const modal = useModal();
    const go = (next: Record<string, string | number | undefined>) => modal.get(urls.self, { issue: filters.issue, status: filters.status, ...next });

    return (
        <>
            <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <Card className="gap-4">
                    <CardHeader className="flex flex-row items-center justify-between gap-3">
                        <CardTitle>Active issues by category</CardTitle>
                        <div className="flex gap-4 text-sm">
                            <span>
                                <span className="text-xl font-semibold text-red-600 tabular-nums dark:text-red-400">{totalIssues}</span> <span className="text-muted-foreground">active</span>
                            </span>
                            <span>
                                <span className="text-xl font-semibold text-emerald-600 tabular-nums dark:text-emerald-400">{completedCount}</span> <span className="text-muted-foreground">fixed</span>
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent className="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                        {Object.entries(statusSummary).map(([label, count]) => {
                            const percent = totalIssues > 0 ? Math.round((count / totalIssues) * 100) : 0;

                            return (
                                <button
                                    key={label}
                                    type="button"
                                    onClick={() => go({ issue: filters.issue === label ? '' : label })}
                                    className={cn('rounded-lg border p-3 text-left transition-colors hover:bg-accent/50', filters.issue === label && 'border-primary ring-1 ring-primary')}
                                    title={`Show ${label} job orders`}
                                >
                                    <div className="flex items-center justify-between text-sm font-medium">
                                        {label}
                                        <span className={cn('font-semibold tabular-nums', count > 0 ? 'text-red-600 dark:text-red-400' : 'text-muted-foreground')}>{count}</span>
                                    </div>
                                    <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-muted">
                                        <div className={cn('h-full rounded-full', count > 0 ? 'bg-red-500' : 'bg-emerald-500')} style={{ width: `${count > 0 ? percent : 100}%` }} />
                                    </div>
                                </button>
                            );
                        })}
                    </CardContent>
                </Card>

                <Card className="gap-3">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Package className="size-4" />
                            Parts used
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2">
                        {partsSummary.length === 0 && <p className="text-sm text-muted-foreground">No parts used yet.</p>}
                        {partsSummary.map((part) => (
                            <div key={part.name} className="flex items-center justify-between gap-2 text-sm">
                                <span className="truncate">{part.name}</span>
                                <Badge variant="secondary">
                                    x{part.qty} {part.unit}
                                </Badge>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </div>

            <div className="flex flex-wrap items-center gap-2">
                <span className="text-sm text-muted-foreground">Show</span>
                <Choice value={filters.issue} allLabel="All issue types" options={props.issueOptions} onChange={(issue) => go({ issue })} label="Filter issue type" />
                <Choice value={filters.status} allLabel="All status" options={props.statusOptions} onChange={(status) => go({ status })} label="Filter status" />
                {(filters.issue || filters.status) && (
                    <Button variant="ghost" size="sm" onClick={() => go({ issue: '', status: '' })}>
                        Clear
                    </Button>
                )}
            </div>

            <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div className="grid min-w-0 content-start gap-4">
                    <ConcernList title="Active job orders" icon={<AlertTriangle className="size-4 text-amber-500" />} items={activeJobOrders} empty="No active job orders." onPage={(page) => go({ active_page: page })} />
                    <ConcernList title="Fixed / closed" icon={<CheckCircle2 className="size-4 text-emerald-500" />} items={completedJobOrders} empty="No fixed or closed job orders." onPage={(page) => go({ completed_page: page })} />
                </div>

                <Card className="content-start gap-3">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <History className="size-4" />
                            Timeline
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {timeline.length === 0 && <p className="text-sm text-muted-foreground">No history found.</p>}
                        <ol className="grid gap-3 border-l pl-4">
                            {timeline.map((item) => (
                                <li key={item.id} className="relative">
                                    <span className="absolute top-1.5 -left-[21px] size-2.5 rounded-full border-2 border-background bg-primary" />
                                    <div className="text-sm font-semibold">{item.jo_no}</div>
                                    <div className="text-xs text-muted-foreground">
                                        {item.issue_type} · {item.status}
                                    </div>
                                    <div className="text-xs text-muted-foreground">{item.updated_at}</div>
                                </li>
                            ))}
                        </ol>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function ConcernList({ title, icon, items, empty, onPage }: { title: string; icon: ReactNode; items: Paginated<ConcernCard>; empty: string; onPage: (page: number) => void }) {
    return (
        <Card className="gap-3">
            <CardHeader className="flex flex-row items-center justify-between gap-2">
                <CardTitle className="flex items-center gap-2">
                    {icon}
                    {title}
                </CardTitle>
                <Badge variant="secondary">{items.total}</Badge>
            </CardHeader>
            <CardContent className="grid gap-2">
                {items.data.length === 0 && <p className="text-sm text-muted-foreground">{empty}</p>}
                {items.data.map((concern) => (
                    <div key={concern.id} className="grid gap-2 rounded-lg border p-3">
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="font-semibold">{concern.jo_no}</span>
                            <Badge variant="secondary">{concern.issue_type}</Badge>
                            <Badge variant="outline" className={concernStatusClass(concern.status)}>
                                {concern.status}
                            </Badge>
                            {concern.overdue && (
                                <Badge variant="destructive">
                                    <Clock />
                                    Overdue
                                </Badge>
                            )}
                            {concern.assignee !== undefined && <span className="ml-auto text-xs text-muted-foreground">{concern.assignee ?? 'Unassigned'}</span>}
                        </div>
                        <dl className="grid gap-x-4 gap-y-1 text-sm sm:grid-cols-[6rem_minmax(0,1fr)]">
                            <dt className="text-muted-foreground">Problem</dt>
                            <dd className="break-words">{concern.problem_details ?? '—'}</dd>
                            {concern.action_taken && (
                                <>
                                    <dt className="text-muted-foreground">Action</dt>
                                    <dd className="break-words">{concern.action_taken}</dd>
                                </>
                            )}
                            {concern.parts.length > 0 && (
                                <>
                                    <dt className="text-muted-foreground">Parts</dt>
                                    <dd className="break-words">{concern.parts.join(', ')}</dd>
                                </>
                            )}
                        </dl>
                    </div>
                ))}
                {items.last_page > 1 && (
                    <div className="flex items-center justify-end gap-2 text-sm">
                        <span className="text-muted-foreground">
                            Page {items.current_page} of {items.last_page}
                        </span>
                        <Button variant="outline" size="icon" className="size-8" aria-label="Previous page" disabled={items.current_page <= 1} onClick={() => onPage(items.current_page - 1)}>
                            <ChevronLeft />
                        </Button>
                        <Button variant="outline" size="icon" className="size-8" aria-label="Next page" disabled={items.current_page >= items.last_page} onClick={() => onPage(items.current_page + 1)}>
                            <ChevronRight />
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function Choice({ value, allLabel, options, onChange, label }: { value: string; allLabel: string; options: string[]; onChange: (value: string) => void; label: string }) {
    return (
        <Select value={value || ALL} onValueChange={(next) => onChange(next === ALL ? '' : next)}>
            <SelectTrigger size="sm" className="w-44" aria-label={label}>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value={ALL}>{allLabel}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option} value={option}>
                        {option}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
