import { Link, router } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, CheckCircle2, PauseCircle, Pencil, Plus, Search, Tag, Trash2, Wrench, X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { DashboardCard, KpiCard } from '@/components/dashboard/charts';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataPagination } from '@/components/data-pagination';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { busConditionClass } from '@/lib/fleet-status';
import type { Paginated } from '@/types';

interface Option {
    value: string;
    label: string;
}

interface ForSaleRow {
    id: number;
    bus_no: string;
    plate_no: string | null;
    company: string | null;
    garage: string | null;
    status: string;
    status_label: string;
    storage_area: string | null;
    breakdown_start: string | null;
    breakdown_end: string | null;
    column_11: string | null;
    days: number;
    unit_location: string | null;
    progress: string | null;
    remarks: string | null;
    edit_url: string;
    destroy_url: string;
}

interface Filters {
    search: string;
    company: string;
    garage: string;
    status: string;
}

interface Props {
    filters: Filters;
    records: Paginated<ForSaleRow>;
    summary: { total: number; running_condition: number; mechanical_breakdown: number; accident_related: number; on_hold: number; breakdown_total: number };
    options: { companies: string[]; garages: string[]; statuses: Option[] };
    can: { create: boolean; edit: boolean; delete: boolean };
    urls: { index: string; create: string; fleet: string };
}

const ALL = '__all';
const n = (value: number) => new Intl.NumberFormat('en-US').format(value);

export default function ForSaleUnits({ filters, records, summary, options, can, urls }: Props) {
    const [draft, setDraft] = useState<Filters>(filters);
    useEffect(() => setDraft(filters), [filters]);
    const hasFilters = Object.values(filters).some(Boolean);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.index, Object.fromEntries(Object.entries(draft).filter(([, value]) => value !== '')), { preserveScroll: true });
    };

    return (
        <AppLayout title="For Sale Units">
            <PageHeader
                title="For Sale Units"
                description="Units for sale with their condition, storage, breakdown days and progress. Changes sync to bus monitoring."
                actions={
                    <>
                        <Button variant="outline" asChild>
                            <Link href={urls.fleet}>
                                <ArrowLeft />
                                Bus Analytics
                            </Link>
                        </Button>
                        {can.create && (
                            <Button asChild>
                                <Link href={urls.create}>
                                    <Plus />
                                    Add for-sale unit
                                </Link>
                            </Button>
                        )}
                    </>
                }
            />

            <div className="grid gap-4 sm:grid-cols-3 xl:grid-cols-6">
                <KpiCard label="Total for sale" value={n(summary.total)} icon={<Tag />} />
                <KpiCard label="Running condition" value={n(summary.running_condition)} icon={<CheckCircle2 />} tone="text-emerald-600 dark:text-emerald-400" />
                <KpiCard label="Mechanical" value={n(summary.mechanical_breakdown)} icon={<Wrench />} tone="text-amber-600 dark:text-amber-400" />
                <KpiCard label="Accident" value={n(summary.accident_related)} icon={<AlertTriangle />} tone="text-red-600 dark:text-red-400" />
                <KpiCard label="On hold" value={n(summary.on_hold)} hint="Plate registration" icon={<PauseCircle />} tone="text-sky-600 dark:text-sky-400" />
                <KpiCard label="Breakdown total" value={n(summary.breakdown_total)} tone="text-red-600 dark:text-red-400" />
            </div>

            <DashboardCard title="For sale records" description={`${n(records.total)} record(s)`} className="gap-0 pb-0" contentClassName="px-0 pt-4">
                <form onSubmit={submit} className="flex flex-wrap items-center gap-2 border-b px-6 pb-4">
                    <div className="relative">
                        <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input aria-label="Search for-sale units" placeholder="Bus no., plate, location, remarks..." className="w-64 pl-8" value={draft.search} onChange={(event) => setDraft({ ...draft, search: event.target.value })} />
                    </div>
                    <FilterSelect allLabel="All companies" value={draft.company} options={options.companies.map((value) => ({ value, label: value }))} onChange={(company) => setDraft({ ...draft, company })} />
                    <FilterSelect allLabel="All garages" value={draft.garage} options={options.garages.map((value) => ({ value, label: value }))} onChange={(garage) => setDraft({ ...draft, garage })} />
                    <FilterSelect allLabel="All statuses" value={draft.status} options={options.statuses} onChange={(status) => setDraft({ ...draft, status })} />
                    <Button type="submit">Apply</Button>
                    {hasFilters && (
                        <Button type="button" variant="ghost" onClick={() => router.get(urls.index)}>
                            <X />
                            Clear
                        </Button>
                    )}
                </form>

                <Table className="min-w-[1500px]">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="pl-6">Bus no.</TableHead>
                            <TableHead>Plate no.</TableHead>
                            <TableHead>Company</TableHead>
                            <TableHead>Garage</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Storage area</TableHead>
                            <TableHead>Breakdown start</TableHead>
                            <TableHead>Breakdown end</TableHead>
                            <TableHead>Column 11</TableHead>
                            <TableHead className="text-right">Days</TableHead>
                            <TableHead>Unit location</TableHead>
                            <TableHead>Progress</TableHead>
                            <TableHead>Remarks</TableHead>
                            {(can.edit || can.delete) && <TableHead className="pr-6 text-right">Actions</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {records.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={14} className="py-12 text-center text-muted-foreground">
                                    No for-sale units found.
                                </TableCell>
                            </TableRow>
                        )}
                        {records.data.map((record) => (
                            <TableRow key={record.id}>
                                <TableCell className="pl-6 font-semibold">{record.bus_no}</TableCell>
                                <TableCell>{record.plate_no ?? '—'}</TableCell>
                                <TableCell>{record.company ?? '—'}</TableCell>
                                <TableCell>{record.garage ?? '—'}</TableCell>
                                <TableCell>
                                    <Badge variant="outline" className={busConditionClass(record.status)}>
                                        {record.status_label}
                                    </Badge>
                                </TableCell>
                                <TableCell>{record.storage_area ?? '—'}</TableCell>
                                <TableCell className="whitespace-nowrap">{record.breakdown_start ?? '—'}</TableCell>
                                <TableCell className="whitespace-nowrap">{record.breakdown_end ?? '—'}</TableCell>
                                <TableCell>{record.column_11 ?? '—'}</TableCell>
                                <TableCell className="text-right font-medium tabular-nums">{n(record.days)}</TableCell>
                                <TableCell>{record.unit_location ?? '—'}</TableCell>
                                <TableCell>{record.progress ?? '—'}</TableCell>
                                <TableCell className="max-w-64 whitespace-normal text-muted-foreground">{record.remarks ?? '—'}</TableCell>
                                {(can.edit || can.delete) && (
                                    <TableCell className="pr-6">
                                        <div className="flex justify-end gap-1">
                                            {can.edit && (
                                                <IconButton label={`Edit ${record.bus_no}`} onClick={() => router.visit(record.edit_url)}>
                                                    <Pencil />
                                                </IconButton>
                                            )}
                                            {can.delete && (
                                                <ConfirmAction
                                                    title={`Delete ${record.bus_no}?`}
                                                    description="This for-sale unit will be removed and the bus returns to Not For Sale in bus monitoring."
                                                    confirmLabel="Delete"
                                                    destructive
                                                    onConfirm={() => router.delete(record.destroy_url, { preserveScroll: true })}
                                                    trigger={
                                                        <IconButton label={`Delete ${record.bus_no}`}>
                                                            <Trash2 className="text-destructive" />
                                                        </IconButton>
                                                    }
                                                />
                                            )}
                                        </div>
                                    </TableCell>
                                )}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <div className="border-t px-6 py-3">
                    <DataPagination paginator={records} noun="record" />
                </div>
            </DashboardCard>
        </AppLayout>
    );
}

function FilterSelect({ allLabel, value, options, onChange }: { allLabel: string; value: string; options: Option[]; onChange: (value: string) => void }) {
    return (
        <Select value={value || ALL} onValueChange={(next) => onChange(next === ALL ? '' : next)}>
            <SelectTrigger className="w-48" aria-label={allLabel}>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value={ALL}>{allLabel}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
