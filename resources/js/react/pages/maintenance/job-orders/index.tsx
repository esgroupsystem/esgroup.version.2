import { Link, router } from '@inertiajs/react';
import { CheckCircle2, ClipboardList, Eye, FileSpreadsheet, FileText, Filter, Pencil, Plus, RotateCcw, Search, Timer, UserCog } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { DataPagination } from '@/components/data-pagination';
import { FormField } from '@/components/form-field';
import { JobStatusBadge, RepairTypeBadge, type Option } from '@/components/maintenance/job-order-parts';
import { PageHeader } from '@/components/page-header';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface Row {
    id: number;
    job_order_no: string;
    creator: string;
    bus_no: string;
    plate_no: string;
    company: string;
    garage: string;
    requester: string;
    status: { value: string | null; label: string; description: string };
    work: string;
    repair_types: { value: string; label: string }[];
    mechanics: string | null;
    odometer: string | null;
    odometer_note: string;
    odometer_lower: boolean;
    downtime: string;
    downtime_running: boolean;
    created_date: string;
    created_time: string;
    show_url: string;
    edit_status_url: string;
}

interface Filters {
    search: string;
    status: string;
    bus_id: string;
    date_filter: string;
    filter_date: string;
    filter_month: string;
    filter_year: string;
}

interface Props {
    jobOrders: Paginated<Row>;
    buses: SearchOption[];
    statusCards: (Option & { count: number })[];
    statuses: Option[];
    filters: Filters;
    can: { create: boolean; updateStatus: boolean };
    urls: { index: string; create: string; export: string };
}

const ALL = 'all';

export default function JobOrdersIndex({ jobOrders, buses, statusCards, statuses, filters, can, urls }: Props) {
    const [values, setValues] = useState<Filters>(filters);
    const query = (next: Partial<Filters> = {}) => Object.fromEntries(Object.entries({ ...values, ...next }).filter(([, value]) => value !== ''));
    const apply = (event?: FormEvent) => {
        event?.preventDefault();
        router.get(urls.index, query());
    };
    const exportUrl = (type: string) => `${urls.export}?${new URLSearchParams({ ...query(), export_type: type }).toString()}`;

    return (
        <AppLayout title="Maintenance Job Orders">
            <PageHeader
                title="Maintenance Job Orders"
                description="Centralized maintenance tracking for bus repairs, odometer checks, and repair status."
                actions={
                    can.create && (
                        <Button asChild>
                            <Link href={urls.create}>
                                <Plus />
                                Create job order
                            </Link>
                        </Button>
                    )
                }
            />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {statusCards.map((card) => (
                    <button
                        key={card.value}
                        type="button"
                        onClick={() => router.get(urls.index, query({ status: filters.status === card.value ? '' : card.value }))}
                        className={cn('rounded-xl border bg-card p-4 text-left shadow-xs transition-colors hover:border-primary/60', filters.status === card.value && 'border-primary ring-2 ring-primary/20')}
                    >
                        <div className="flex items-center justify-between gap-2">
                            <JobStatusBadge value={card.value} label={card.label} />
                            <span className="text-2xl font-semibold tabular-nums">{card.count.toLocaleString()}</span>
                        </div>
                        <p className="mt-2 text-xs text-muted-foreground">{card.description}</p>
                    </button>
                ))}
            </div>

            <Card>
                <CardContent>
                    <form onSubmit={apply} className="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_14rem_12rem_10rem_11rem_auto] xl:items-end">
                        <FormField id="jo-search" label="Search job order">
                            <div className="relative">
                                <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="jo-search" className="pl-8" placeholder="JO no., bus no., plate no., requester, work" value={values.search} onChange={(event) => setValues({ ...values, search: event.target.value })} />
                            </div>
                        </FormField>
                        <FormField id="jo-bus" label="Bus">
                            <SearchSelect id="jo-bus" options={buses} value={values.bus_id} onChange={(bus_id) => setValues({ ...values, bus_id })} placeholder="All buses" searchPlaceholder="Search bus..." />
                        </FormField>
                        <FormField id="jo-status" label="Maintenance status">
                            <Select value={values.status || ALL} onValueChange={(status) => setValues({ ...values, status: status === ALL ? '' : status })}>
                                <SelectTrigger id="jo-status" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>All statuses</SelectItem>
                                    {statuses.map((status) => (
                                        <SelectItem key={status.value} value={status.value}>
                                            {status.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                        <FormField id="jo-date-filter" label="Date filter">
                            <Select value={values.date_filter || ALL} onValueChange={(date_filter) => setValues({ ...values, date_filter: date_filter === ALL ? '' : date_filter })}>
                                <SelectTrigger id="jo-date-filter" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>All dates</SelectItem>
                                    <SelectItem value="day">By day</SelectItem>
                                    <SelectItem value="month">By month</SelectItem>
                                    <SelectItem value="year">By year</SelectItem>
                                </SelectContent>
                            </Select>
                        </FormField>
                        <div>
                            {values.date_filter === 'day' && (
                                <FormField id="jo-day" label="Day">
                                    <Input id="jo-day" type="date" value={values.filter_date} onChange={(event) => setValues({ ...values, filter_date: event.target.value })} />
                                </FormField>
                            )}
                            {values.date_filter === 'month' && (
                                <FormField id="jo-month" label="Month">
                                    <Input id="jo-month" type="month" value={values.filter_month} onChange={(event) => setValues({ ...values, filter_month: event.target.value })} />
                                </FormField>
                            )}
                            {values.date_filter === 'year' && (
                                <FormField id="jo-year" label="Year">
                                    <Input id="jo-year" type="number" min={2000} max={2100} placeholder="2026" value={values.filter_year} onChange={(event) => setValues({ ...values, filter_year: event.target.value })} />
                                </FormField>
                            )}
                        </div>
                        <div className="flex gap-2">
                            <Button type="submit">
                                <Filter />
                                Filter
                            </Button>
                            <Button type="button" variant="outline" aria-label="Reset filters" onClick={() => router.get(urls.index)}>
                                <RotateCcw />
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Maintenance records</CardTitle>
                        <CardDescription>{jobOrders.total.toLocaleString()} records</CardDescription>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" asChild>
                            <a href={exportUrl('csv')}>
                                <FileText />
                                CSV
                            </a>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <a href={exportUrl('xls')}>
                                <FileSpreadsheet />
                                Excel
                            </a>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[1300px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Job order</TableHead>
                                <TableHead>Vehicle</TableHead>
                                <TableHead>Requester</TableHead>
                                <TableHead>Work required</TableHead>
                                <TableHead>Odometer</TableHead>
                                <TableHead>Downtime</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead className="pr-6 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {jobOrders.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={9} className="py-12">
                                        <div className="flex flex-col items-center gap-2 text-center text-muted-foreground">
                                            <ClipboardList className="size-8" />
                                            <div className="font-semibold text-foreground">No maintenance job orders found</div>
                                            Create a maintenance record to start tracking bus repair activity.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {jobOrders.data.map((row) => (
                                <TableRow key={row.id} className="align-top">
                                    <TableCell className="pl-6">
                                        <Link href={row.show_url} className="font-semibold text-primary hover:underline">
                                            {row.job_order_no}
                                        </Link>
                                        <div className="text-xs text-muted-foreground">Created by {row.creator}</div>
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        <div className="font-medium">{row.bus_no}</div>
                                        <div className="text-xs text-muted-foreground">Plate: {row.plate_no}</div>
                                        <div className="text-xs text-muted-foreground">
                                            {row.company} · {row.garage}
                                        </div>
                                    </TableCell>
                                    <TableCell>{row.requester}</TableCell>
                                    <TableCell className="max-w-72 whitespace-normal">
                                        <p className="line-clamp-2 text-sm" title={row.work}>
                                            {row.work}
                                        </p>
                                        {row.repair_types.length > 0 && (
                                            <div className="mt-1 flex flex-wrap gap-1">
                                                {row.repair_types.map((type) => (
                                                    <RepairTypeBadge key={type.value} value={type.value} label={type.label} />
                                                ))}
                                            </div>
                                        )}
                                        {row.mechanics && (
                                            <div className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                                <UserCog className="size-3" />
                                                {row.mechanics}
                                            </div>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        {row.odometer ?? <span className="text-muted-foreground">No reading</span>}
                                        {row.odometer && <div className={cn('text-xs', row.odometer_lower ? 'text-destructive' : 'text-muted-foreground')}>{row.odometer_note}</div>}
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        <div className="font-medium">{row.downtime}</div>
                                        <div className={cn('mt-1 flex items-center gap-1 text-xs', row.downtime_running ? 'text-amber-600' : 'text-emerald-600')}>
                                            {row.downtime_running ? <Timer className="size-3" /> : <CheckCircle2 className="size-3" />}
                                            {row.downtime_running ? 'Counting' : 'Stopped'}
                                        </div>
                                    </TableCell>
                                    <TableCell className="max-w-48 whitespace-normal">
                                        <JobStatusBadge value={row.status.value} label={row.status.label} />
                                        <div className="mt-1 text-xs text-muted-foreground">{row.status.description}</div>
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        <div className="font-medium">{row.created_date}</div>
                                        <div className="text-xs text-muted-foreground">{row.created_time}</div>
                                    </TableCell>
                                    <TableCell className="pr-6">
                                        <div className="flex justify-end gap-1">
                                            <Button variant="ghost" size="icon" className="size-8" asChild>
                                                <Link href={row.show_url} aria-label={`View ${row.job_order_no}`}>
                                                    <Eye />
                                                </Link>
                                            </Button>
                                            {can.updateStatus && (
                                                <Button variant="ghost" size="icon" className="size-8" asChild>
                                                    <Link href={row.edit_status_url} aria-label={`Edit status of ${row.job_order_no}`}>
                                                        <Pencil />
                                                    </Link>
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={jobOrders} noun="record" />
        </AppLayout>
    );
}
