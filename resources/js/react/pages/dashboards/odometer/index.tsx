import { router, useForm } from '@inertiajs/react';
import { ArrowDownToLine, ArrowUpFromLine, Droplets, FileSpreadsheet, FileText, Fuel, Gauge, Loader2, Pencil, Plus, Route, SlidersHorizontal, Trash2, X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { DashboardCard, KpiCard, SeriesChart, SimpleBarChart } from '@/components/dashboard/charts';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataPagination } from '@/components/data-pagination';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { tones } from '@/lib/dashboard-tones';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface OdometerRow {
    id: number;
    garage: string | null;
    bus_name: string | null;
    body_number: string | null;
    plate_number: string | null;
    date: string;
    date_label: string;
    time: string;
    time_label: string;
    driver_name: string;
    driver_name_raw: string | null;
    date_bus_deployed: string | null;
    previous_odometer: number | null;
    new_odometer: number;
    total_km_run: number;
    diesel_consumption: number;
    km_per_liter: number;
    remaining_change_oil: number;
    update_url: string;
    destroy_url: string;
}

interface Movement {
    id: number;
    date: string;
    type: 'in' | 'out' | 'adjustment';
    reference_no: string | null;
    bus: string | null;
    liters: number;
    unit_cost: number | null;
    total_cost: number | null;
    remarks: string | null;
    encoder: string | null;
}

interface Filters {
    filter_type: 'month' | 'day' | 'range';
    month: string;
    date: string;
    date_from: string;
    date_to: string;
    bus_detail_id: string;
    last_change_oil: string;
}

interface Props {
    filters: Filters;
    periodLabel: string;
    selectedBus: string | null;
    buses: SearchOption[];
    summary: { current_stock: number; period_in: number; period_out: number; period_adjustment: number; total_km: number; total_liters: number; average_km_per_liter: number };
    records: Paginated<OdometerRow>;
    movements: Movement[];
    charts: { daily: { label: string; km: number; liters: number }[]; perBus: { label: string; value: number }[] };
    can: { create: boolean; edit: boolean; delete: boolean; diesel: boolean };
    urls: { index: string; export: string; manual: string; diesel: string };
}

const km = new Intl.NumberFormat('en-US');
const liters = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const ALL = '__all';

/** Same thresholds as the old report: none / <3 low / <5 monitor / otherwise good. */
function efficiency(value: number): { label: string; className: string } {
    if (value <= 0) return { label: 'No data', className: 'text-muted-foreground' };
    if (value < 3) return { label: 'Low', className: tones.RED };
    if (value < 5) return { label: 'Monitor', className: tones.AMBER };
    return { label: 'Good', className: tones.GREEN };
}

function stockStatus(value: number): { label: string; className: string } {
    if (value <= 0) return { label: 'No stock', className: tones.RED };
    if (value <= 100) return { label: 'Low stock', className: tones.AMBER };
    return { label: 'Good stock', className: tones.GREEN };
}

const MOVEMENT = {
    in: { label: 'Diesel IN', icon: ArrowDownToLine, className: tones.GREEN },
    out: { label: 'Diesel OUT', icon: ArrowUpFromLine, className: tones.RED },
    adjustment: { label: 'Adjustment', icon: SlidersHorizontal, className: tones.AMBER },
} as const;

export default function OdometerDashboard({ filters, periodLabel, selectedBus, buses, summary, records, movements, charts, can, urls }: Props) {
    const [draft, setDraft] = useState<Filters>(filters);
    const [dialog, setDialog] = useState<'manual' | 'diesel' | OdometerRow | null>(null);
    useEffect(() => setDraft(filters), [filters]);

    const params = (values: Filters) => {
        const query: Record<string, string> = { filter_type: values.filter_type };
        if (values.filter_type === 'month') query.month = values.month;
        if (values.filter_type === 'day') query.date = values.date;
        if (values.filter_type === 'range') {
            query.date_from = values.date_from;
            query.date_to = values.date_to;
        }
        if (values.bus_detail_id) query.bus_detail_id = values.bus_detail_id;
        if (values.last_change_oil) query.last_change_oil = values.last_change_oil;
        return query;
    };

    const apply = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.index, params(draft), { preserveScroll: true });
    };

    const exportUrl = (type: 'csv' | 'xls') => `${urls.export}?${new URLSearchParams({ ...params(filters), export_type: type }).toString()}`;
    const stock = stockStatus(summary.current_stock);
    const average = efficiency(summary.average_km_per_liter);
    const showOil = filters.last_change_oil !== '';

    return (
        <AppLayout title="Odometer Monitoring">
            <PageHeader
                title="Diesel Stock & Odometer Monitoring"
                description="Track diesel inventory, diesel usage, odometer entries, kilometers travelled, and fuel efficiency."
                actions={
                    <>
                        <Button variant="outline" asChild>
                            <a href={exportUrl('csv')}>
                                <FileText />
                                CSV
                            </a>
                        </Button>
                        <Button variant="outline" asChild>
                            <a href={exportUrl('xls')}>
                                <FileSpreadsheet />
                                Excel
                            </a>
                        </Button>
                        {can.create && (
                            <Button variant="secondary" onClick={() => setDialog('manual')}>
                                <Gauge />
                                Add odometer
                            </Button>
                        )}
                        {can.diesel && (
                            <Button onClick={() => setDialog('diesel')}>
                                <Plus />
                                Add diesel stock
                            </Button>
                        )}
                    </>
                }
            />

            <DashboardCard title="Filters" description={`Showing ${periodLabel}${selectedBus ? ` · ${selectedBus}` : ' · All bus units'}`}>
                <form onSubmit={apply} className="grid gap-3 md:grid-cols-2 xl:grid-cols-[10rem_repeat(2,minmax(0,11rem))_minmax(0,1fr)_10rem_auto] xl:items-end">
                    <FormField id="filter-type" label="Filter by">
                        <Select value={draft.filter_type} onValueChange={(value) => setDraft({ ...draft, filter_type: value as Filters['filter_type'] })}>
                            <SelectTrigger id="filter-type" className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="month">Month</SelectItem>
                                <SelectItem value="day">Day</SelectItem>
                                <SelectItem value="range">Date range</SelectItem>
                            </SelectContent>
                        </Select>
                    </FormField>
                    {draft.filter_type === 'month' && (
                        <FormField id="filter-month" label="Month" className="xl:col-span-2">
                            <Input id="filter-month" type="month" value={draft.month} onChange={(event) => setDraft({ ...draft, month: event.target.value })} />
                        </FormField>
                    )}
                    {draft.filter_type === 'day' && (
                        <FormField id="filter-date" label="Date" className="xl:col-span-2">
                            <Input id="filter-date" type="date" value={draft.date} onChange={(event) => setDraft({ ...draft, date: event.target.value })} />
                        </FormField>
                    )}
                    {draft.filter_type === 'range' && (
                        <>
                            <FormField id="filter-from" label="From">
                                <Input id="filter-from" type="date" value={draft.date_from} onChange={(event) => setDraft({ ...draft, date_from: event.target.value })} />
                            </FormField>
                            <FormField id="filter-to" label="To">
                                <Input id="filter-to" type="date" value={draft.date_to} onChange={(event) => setDraft({ ...draft, date_to: event.target.value })} />
                            </FormField>
                        </>
                    )}
                    <FormField id="filter-bus" label="Bus unit">
                        <SearchSelect
                            id="filter-bus"
                            ariaLabel="Bus unit"
                            options={[{ value: ALL, label: 'All bus units' }, ...buses]}
                            value={draft.bus_detail_id || ALL}
                            onChange={(value) => setDraft({ ...draft, bus_detail_id: value === ALL ? '' : value })}
                            searchPlaceholder="Search body no., plate, garage..."
                        />
                    </FormField>
                    <FormField id="filter-oil" label="Last change oil (km)">
                        <Input id="filter-oil" type="number" min={0} placeholder="Optional" value={draft.last_change_oil} onChange={(event) => setDraft({ ...draft, last_change_oil: event.target.value })} />
                    </FormField>
                    <div className="flex gap-2">
                        <Button type="submit">Apply</Button>
                        <Button type="button" variant="ghost" onClick={() => router.get(urls.index)} aria-label="Reset filters">
                            <X />
                        </Button>
                    </div>
                </form>
            </DashboardCard>

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <KpiCard label="Current diesel stock" value={`${liters.format(summary.current_stock)} L`} hint={<Badge variant="outline" className={stock.className}>{stock.label}</Badge>} icon={<Fuel />} />
                <KpiCard label="Diesel in" value={`${liters.format(summary.period_in)} L`} hint={`Liters added during ${periodLabel}`} icon={<ArrowDownToLine />} tone="text-emerald-600 dark:text-emerald-400" />
                <KpiCard label="Diesel out" value={`${liters.format(summary.period_out)} L`} hint={`Liters consumed during ${periodLabel}`} icon={<ArrowUpFromLine />} tone="text-red-600 dark:text-red-400" />
                <KpiCard label="Adjustment" value={`${liters.format(summary.period_adjustment)} L`} hint={`Manual correction during ${periodLabel}`} icon={<SlidersHorizontal />} tone="text-amber-600 dark:text-amber-400" />
                <KpiCard label="Period" value={<span className="text-lg">{periodLabel}</span>} hint="Selected reporting period" />
                <KpiCard label="Total KM run" value={km.format(summary.total_km)} hint="Kilometers travelled" icon={<Route />} />
                <KpiCard label="Diesel used" value={`${liters.format(summary.total_liters)} L`} hint="From odometer entries" icon={<Droplets />} />
                <KpiCard label="Average KM/L" value={liters.format(summary.average_km_per_liter)} hint={<Badge variant="outline" className={average.className}>{average.label}</Badge>} icon={<Gauge />} />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <DashboardCard title="Daily KM run and diesel" description={periodLabel} className="lg:col-span-2">
                    <SeriesChart
                        categories={charts.daily.map((day) => day.label)}
                        series={[
                            { key: 'km', label: 'KM run', values: charts.daily.map((day) => day.km), color: 'var(--chart-2)' },
                            { key: 'liters', label: 'Diesel (L)', values: charts.daily.map((day) => day.liters), color: 'var(--chart-1)' },
                        ]}
                    />
                </DashboardCard>
                <DashboardCard title="Top buses by KM" description="Highest kilometers in the period">
                    <SimpleBarChart data={charts.perBus} horizontal height={Math.max(220, charts.perBus.length * 30)} valueLabel="KM" />
                </DashboardCard>
            </div>

            <DashboardCard title="Odometer details" description={`${records.total} record(s) · ${periodLabel}`} className="gap-0 pb-0" contentClassName="px-0 pt-4">
                <Table className="min-w-[1100px]">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="pl-6">Date</TableHead>
                            <TableHead>Bus</TableHead>
                            <TableHead>Time</TableHead>
                            <TableHead>Driver</TableHead>
                            <TableHead className="text-right">Previous</TableHead>
                            <TableHead className="text-right">New</TableHead>
                            <TableHead className="text-right">KM run</TableHead>
                            <TableHead className="text-right">Diesel</TableHead>
                            <TableHead className="text-center">KM/L</TableHead>
                            {showOil && <TableHead className="text-right">Oil change in</TableHead>}
                            <TableHead className="pr-6 text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {records.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={showOil ? 11 : 10} className="py-12 text-center text-muted-foreground">
                                    No encoded odometer records for {periodLabel}.
                                </TableCell>
                            </TableRow>
                        )}
                        {records.data.map((row) => {
                            const rating = efficiency(row.km_per_liter);

                            return (
                                <TableRow key={row.id}>
                                    <TableCell className="pl-6 font-medium whitespace-nowrap">{row.date_label}</TableCell>
                                    <TableCell>
                                        <div className="font-medium">
                                            {row.body_number ?? 'N/A'} <span className="text-muted-foreground">· {row.bus_name ?? 'No bus name'}</span>
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {row.plate_number ?? '-'} · {row.garage ?? '-'}
                                        </div>
                                    </TableCell>
                                    <TableCell className="whitespace-nowrap">{row.time_label}</TableCell>
                                    <TableCell className="uppercase">{row.driver_name}</TableCell>
                                    <TableCell className="text-right text-muted-foreground tabular-nums">{row.previous_odometer !== null ? km.format(row.previous_odometer) : '-'}</TableCell>
                                    <TableCell className="text-right font-medium tabular-nums">{km.format(row.new_odometer)}</TableCell>
                                    <TableCell className="text-right tabular-nums">{km.format(row.total_km_run)}</TableCell>
                                    <TableCell className="text-right tabular-nums">{liters.format(row.diesel_consumption)}</TableCell>
                                    <TableCell className="text-center">
                                        <Badge variant="outline" className={cn('tabular-nums', rating.className)} title={rating.label}>
                                            {liters.format(row.km_per_liter)}
                                        </Badge>
                                    </TableCell>
                                    {showOil && <TableCell className="text-right tabular-nums">{km.format(row.remaining_change_oil)} km</TableCell>}
                                    <TableCell className="pr-6">
                                        <div className="flex justify-end gap-1">
                                            {can.edit && (
                                                <IconButton label={`Edit odometer ${row.id}`} onClick={() => setDialog(row)}>
                                                    <Pencil />
                                                </IconButton>
                                            )}
                                            {can.delete && (
                                                <ConfirmAction
                                                    title="Delete odometer record?"
                                                    description="The record and any diesel OUT created from it will be removed. KM run for the next reading will be recalculated."
                                                    confirmLabel="Delete"
                                                    destructive
                                                    onConfirm={() => router.delete(row.destroy_url, { preserveScroll: true })}
                                                    trigger={
                                                        <IconButton label={`Delete odometer ${row.id}`}>
                                                            <Trash2 className="text-destructive" />
                                                        </IconButton>
                                                    }
                                                />
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            );
                        })}
                    </TableBody>
                    {records.data.length > 0 && (
                        <TableFooter>
                            <TableRow>
                                <TableCell colSpan={6} className="pl-6 font-semibold">
                                    Period total
                                </TableCell>
                                <TableCell className="text-right font-semibold tabular-nums">{km.format(summary.total_km)}</TableCell>
                                <TableCell className="text-right font-semibold tabular-nums">{liters.format(summary.total_liters)}</TableCell>
                                <TableCell className="text-center font-semibold tabular-nums">{liters.format(summary.average_km_per_liter)}</TableCell>
                                <TableCell colSpan={showOil ? 2 : 1} />
                            </TableRow>
                        </TableFooter>
                    )}
                </Table>
                <div className="border-t px-6 py-3">
                    <DataPagination paginator={records} noun="record" />
                </div>
            </DashboardCard>

            <DashboardCard title="Diesel stock movement" description={`${movements.length} record(s) · ${periodLabel}`} className="gap-0 pb-0" contentClassName="px-0 pt-4">
                <Table className="min-w-[980px]">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="pl-6">Date</TableHead>
                            <TableHead>Movement</TableHead>
                            <TableHead>Reference</TableHead>
                            <TableHead>Bus / unit</TableHead>
                            <TableHead className="text-right">Liters</TableHead>
                            <TableHead className="text-right">Unit cost</TableHead>
                            <TableHead className="text-right">Total cost</TableHead>
                            <TableHead className="pr-6">Remarks</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {movements.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={8} className="py-12 text-center text-muted-foreground">
                                    No diesel inventory records for {periodLabel}.
                                </TableCell>
                            </TableRow>
                        )}
                        {movements.map((movement) => {
                            const meta = MOVEMENT[movement.type] ?? MOVEMENT.adjustment;
                            const Icon = meta.icon;

                            return (
                                <TableRow key={movement.id}>
                                    <TableCell className="pl-6 font-medium whitespace-nowrap">{movement.date}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" className={meta.className}>
                                            <Icon />
                                            {meta.label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="font-mono text-xs">{movement.reference_no ?? '-'}</TableCell>
                                    <TableCell>
                                        <div className="font-medium">{movement.bus ?? 'Stock only'}</div>
                                        <div className="text-xs text-muted-foreground">{movement.bus ? 'Bus consumption record' : 'No bus assigned'}</div>
                                    </TableCell>
                                    <TableCell className="text-right font-semibold tabular-nums">{liters.format(movement.liters)}</TableCell>
                                    <TableCell className="text-right tabular-nums">{movement.unit_cost ? liters.format(movement.unit_cost) : '-'}</TableCell>
                                    <TableCell className="text-right tabular-nums">{movement.total_cost ? liters.format(movement.total_cost) : '-'}</TableCell>
                                    <TableCell className="max-w-64 pr-6 whitespace-normal text-muted-foreground">{movement.remarks ?? '-'}</TableCell>
                                </TableRow>
                            );
                        })}
                    </TableBody>
                    {movements.length > 0 && (
                        <TableFooter>
                            <TableRow>
                                <TableCell colSpan={4} className="pl-6 font-semibold">
                                    Period total
                                </TableCell>
                                <TableCell className="text-right text-xs font-semibold whitespace-nowrap tabular-nums">
                                    IN: {liters.format(summary.period_in)}
                                    <br />
                                    OUT: {liters.format(summary.period_out)}
                                </TableCell>
                                <TableCell colSpan={3} />
                            </TableRow>
                        </TableFooter>
                    )}
                </Table>
            </DashboardCard>

            {dialog === 'manual' && <ManualOdometerDialog buses={buses} url={urls.manual} onClose={() => setDialog(null)} />}
            {dialog === 'diesel' && <DieselStockDialog buses={buses} url={urls.diesel} onClose={() => setDialog(null)} />}
            {dialog && typeof dialog === 'object' && <EditOdometerDialog row={dialog} onClose={() => setDialog(null)} />}
        </AppLayout>
    );
}

const today = () => new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' });
const nowTime = () => new Date().toLocaleTimeString('en-GB', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit' });

/** The manual-odometer endpoint answers some rejections (duplicate time, out-of-order reading) with a flash error, not a field error. */
const flashError = (page: { props: unknown }): string | null => (page.props as { flash?: { error?: string | null } }).flash?.error ?? null;

function ManualOdometerDialog({ buses, url, onClose }: { buses: SearchOption[]; url: string; onClose: () => void }) {
    const form = useForm({
        bus_detail_id: '',
        date_bus_deployed: today(),
        date: today(),
        time: nowTime(),
        driver_name: '',
        new_odometer: '',
        diesel_consumption: '',
        also_deduct_diesel_stock: true,
    });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, also_deduct_diesel_stock: data.also_deduct_diesel_stock ? 1 : 0 }));
        form.post(url, {
            preserveScroll: true,
            // The refusal itself shows as a toast; keep the dialog (and what was typed) open.
            onSuccess: (page) => {
                if (!flashError(page)) onClose();
            },
        });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Add manual odometer</DialogTitle>
                        <DialogDescription>The reading must sit between the bus's previous and next readings.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField id="manual-bus" label="Bus unit" required error={errors.bus_detail_id} className="sm:col-span-2">
                            <SearchSelect id="manual-bus" ariaLabel="Bus unit" options={buses} value={form.data.bus_detail_id} onChange={(value) => form.setData('bus_detail_id', value)} placeholder="Select bus" invalid={!!errors.bus_detail_id} />
                        </FormField>
                        <FormField id="manual-deployed" label="Date bus deployed" error={errors.date_bus_deployed}>
                            <Input id="manual-deployed" type="date" value={form.data.date_bus_deployed} onChange={(event) => form.setData('date_bus_deployed', event.target.value)} />
                        </FormField>
                        <FormField id="manual-date" label="Odometer date" required error={errors.date}>
                            <Input id="manual-date" type="date" required value={form.data.date} onChange={(event) => form.setData('date', event.target.value)} />
                        </FormField>
                        <FormField id="manual-time" label="Time" required error={errors.time}>
                            <Input id="manual-time" type="time" required value={form.data.time} onChange={(event) => form.setData('time', event.target.value)} />
                        </FormField>
                        <FormField id="manual-driver" label="Driver name" required error={errors.driver_name}>
                            <Input id="manual-driver" required value={form.data.driver_name} onChange={(event) => form.setData('driver_name', event.target.value)} />
                        </FormField>
                        <FormField id="manual-odometer" label="New odometer (km)" required error={errors.new_odometer}>
                            <Input id="manual-odometer" type="number" min={0} required value={form.data.new_odometer} onChange={(event) => form.setData('new_odometer', event.target.value)} />
                        </FormField>
                        <FormField id="manual-diesel" label="Diesel consumption (L)" error={errors.diesel_consumption}>
                            <Input id="manual-diesel" type="number" min={0} step="0.01" value={form.data.diesel_consumption} onChange={(event) => form.setData('diesel_consumption', event.target.value)} />
                        </FormField>
                        <Label className="flex items-start gap-2 rounded-md border p-3 font-normal sm:col-span-2">
                            <Checkbox checked={form.data.also_deduct_diesel_stock} onCheckedChange={(value) => form.setData('also_deduct_diesel_stock', value === true)} className="mt-0.5" />
                            <span>
                                <span className="font-medium">Also deduct from diesel stock</span>
                                <span className="block text-xs text-muted-foreground">Creates a Diesel OUT record for the liters above.</span>
                            </span>
                        </Label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Save odometer
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function DieselStockDialog({ buses, url, onClose }: { buses: SearchOption[]; url: string; onClose: () => void }) {
    const form = useForm({ date: today(), type: 'in', liters: '', unit_cost: '', reference_no: '', bus_detail_id: '', remarks: '' });
    const errors = form.errors as Record<string, string | undefined>;
    const total = Number(form.data.liters) * Number(form.data.unit_cost);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(url, { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Add diesel stock</DialogTitle>
                        <DialogDescription>Record a delivery (IN), a withdrawal (OUT), or a manual correction.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField id="diesel-date" label="Date" required error={errors.date}>
                            <Input id="diesel-date" type="date" required value={form.data.date} onChange={(event) => form.setData('date', event.target.value)} />
                        </FormField>
                        <FormField id="diesel-type" label="Movement type" required error={errors.type}>
                            <Select value={form.data.type} onValueChange={(value) => form.setData('type', value)}>
                                <SelectTrigger id="diesel-type" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="in">Diesel IN (delivery / purchase)</SelectItem>
                                    <SelectItem value="out">Diesel OUT (withdrawal)</SelectItem>
                                    <SelectItem value="adjustment">Adjustment (correction)</SelectItem>
                                </SelectContent>
                            </Select>
                        </FormField>
                        <FormField id="diesel-liters" label="Liters" required error={errors.liters}>
                            <Input id="diesel-liters" type="number" min={0.01} step="0.01" required value={form.data.liters} onChange={(event) => form.setData('liters', event.target.value)} />
                        </FormField>
                        <FormField id="diesel-cost" label="Unit cost" error={errors.unit_cost} hint={total > 0 ? `Total cost: ${liters.format(total)}` : undefined}>
                            <Input id="diesel-cost" type="number" min={0} step="0.01" value={form.data.unit_cost} onChange={(event) => form.setData('unit_cost', event.target.value)} />
                        </FormField>
                        <FormField id="diesel-reference" label="Reference no." error={errors.reference_no}>
                            <Input id="diesel-reference" value={form.data.reference_no} onChange={(event) => form.setData('reference_no', event.target.value)} placeholder="DR / invoice no." />
                        </FormField>
                        <FormField id="diesel-bus" label="Bus unit" error={errors.bus_detail_id} hint="Optional; leave empty for stock only.">
                            <SearchSelect
                                id="diesel-bus"
                                ariaLabel="Bus unit"
                                options={[{ value: ALL, label: 'Stock only (no bus)' }, ...buses]}
                                value={form.data.bus_detail_id || ALL}
                                onChange={(value) => form.setData('bus_detail_id', value === ALL ? '' : value)}
                            />
                        </FormField>
                        <FormField id="diesel-remarks" label="Remarks" error={errors.remarks} className="sm:col-span-2">
                            <Textarea id="diesel-remarks" rows={3} value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} placeholder="Optional notes" />
                        </FormField>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Save diesel stock
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function EditOdometerDialog({ row, onClose }: { row: OdometerRow; onClose: () => void }) {
    const form = useForm({
        date_bus_deployed: row.date_bus_deployed ?? '',
        date: row.date,
        time: row.time,
        driver_name: row.driver_name_raw ?? '',
        new_odometer: String(row.new_odometer),
        diesel_consumption: String(row.diesel_consumption),
    });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.patch(row.update_url, { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="sm:max-w-2xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Edit odometer</DialogTitle>
                        <DialogDescription>
                            {row.body_number ?? 'N/A'} · {row.bus_name ?? 'No bus name'}. A linked diesel OUT record is updated to match.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField id="edit-date" label="Date" required error={errors.date}>
                            <Input id="edit-date" type="date" required value={form.data.date} onChange={(event) => form.setData('date', event.target.value)} />
                        </FormField>
                        <FormField id="edit-time" label="Time" required error={errors.time}>
                            <Input id="edit-time" type="time" required value={form.data.time} onChange={(event) => form.setData('time', event.target.value)} />
                        </FormField>
                        <FormField id="edit-deployed" label="Date bus deployed" error={errors.date_bus_deployed}>
                            <Input id="edit-deployed" type="date" value={form.data.date_bus_deployed} onChange={(event) => form.setData('date_bus_deployed', event.target.value)} />
                        </FormField>
                        <FormField id="edit-driver" label="Driver name" required error={errors.driver_name}>
                            <Input id="edit-driver" required value={form.data.driver_name} onChange={(event) => form.setData('driver_name', event.target.value)} />
                        </FormField>
                        <FormField id="edit-odometer" label="New odometer (km)" required error={errors.new_odometer}>
                            <Input id="edit-odometer" type="number" min={0} required value={form.data.new_odometer} onChange={(event) => form.setData('new_odometer', event.target.value)} />
                        </FormField>
                        <FormField id="edit-diesel" label="Diesel consumption (L)" error={errors.diesel_consumption}>
                            <Input id="edit-diesel" type="number" min={0} step="0.01" value={form.data.diesel_consumption} onChange={(event) => form.setData('diesel_consumption', event.target.value)} />
                        </FormField>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Update odometer
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
