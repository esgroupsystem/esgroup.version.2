import { Link, router } from '@inertiajs/react';
import { AlertTriangle, Building2, Bus, CheckCircle2, FolderOpen, PauseCircle, Pencil, Plus, Search, Tag, Wrench, X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { DashboardCard, DonutChart, KpiCard, SeriesChart } from '@/components/dashboard/charts';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { busConditionClass, forSaleStatusClass } from '@/lib/fleet-status';
import { tones } from '@/lib/dashboard-tones';

interface Option {
    value: string;
    label: string;
}

interface SummaryRow {
    name: string;
    active: number;
    mechanical_breakdown: number;
    accident_related: number;
    on_hold: number;
    for_sale: number;
    not_for_sale: number;
    total_units: number;
}

interface ForSaleSummaryRow {
    name: string;
    mechanical_breakdown: number;
    accident_related: number;
    on_hold: number;
    breakdown_total: number;
    running_condition: number;
    total_for_sale: number;
}

interface BusRow {
    id: number;
    bus_no: string;
    plate_no: string | null;
    company: string | null;
    garage: string | null;
    operational_status: string;
    operational_status_label: string;
    for_sale: boolean;
    sale_status_label: string;
    chassis_number: string | null;
    engine_number: string | null;
    case_number: string | null;
    remarks: string | null;
    edit_url: string;
}

interface ForSaleRow {
    id: number;
    bus_no: string;
    plate_no: string | null;
    company: string | null;
    garage: string | null;
    status: string | null;
    status_label: string;
    storage_area: string | null;
    breakdown_start: string | null;
    breakdown_end: string | null;
    days: number;
    unit_location: string | null;
    progress: string | null;
    remarks: string | null;
    edit_url: string;
}

type Folder =
    | { type: 'garage'; key: string; label: string; count: number; groups: { name: string; rows: BusRow[] }[] }
    | { type: 'for_sale'; key: string; label: string; count: number; groups: { name: string; rows: ForSaleRow[] }[] };

interface Filters {
    search: string;
    garage: string;
    company: string;
    operational_status: string;
    sale_status: string;
}

interface Props {
    filters: Filters;
    options: { garages: string[]; companies: string[]; operational_statuses: Option[]; sale_statuses: Option[] };
    totals: { total_units: number; active: number; mechanical_breakdown: number; accident_related: number; on_hold: number; for_sale: number; not_for_sale: number };
    filteredCount: number;
    garageSummary: SummaryRow[];
    companySummary: SummaryRow[];
    forSaleSummary: {
        rows: ForSaleSummaryRow[];
        mechanical_breakdown_total: number;
        accident_related_total: number;
        on_hold_total: number;
        breakdown_total: number;
        running_condition_total: number;
        total_for_sale: number;
    };
    folders: Folder[];
    folderTotals: { units: number; for_sale: number };
    can: { create: boolean; edit: boolean; for_sale_create: boolean; for_sale_edit: boolean };
    urls: { index: string; create: string; forSaleIndex: string; forSaleCreate: string };
}

const ALL = '__all';
const number = new Intl.NumberFormat('en-US');
const n = (value: number) => number.format(value);

const STATUS_COLORS = { active: '#10b981', mechanical: '#f59e0b', accident: '#ef4444', onHold: '#0ea5e9', forSale: '#a855f7' };

export default function FleetDashboard({ filters, options, totals, filteredCount, garageSummary, companySummary, forSaleSummary, folders, folderTotals, can, urls }: Props) {
    const [draft, setDraft] = useState<Filters>(filters);
    useEffect(() => setDraft(filters), [filters]);
    const hasFilters = Object.values(filters).some(Boolean);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const query = Object.fromEntries(Object.entries(draft).filter(([, value]) => value !== ''));
        router.get(urls.index, query, { preserveScroll: true });
    };

    return (
        <AppLayout title="Bus Analytics">
            <PageHeader
                title="Fleet Monitoring"
                description="Unit condition per garage and company, for-sale monitoring, and the full bus master list."
                actions={
                    <>
                        {can.for_sale_create && (
                            <Button variant="outline" asChild>
                                <Link href={urls.forSaleCreate}>
                                    <Tag />
                                    Add for-sale unit
                                </Link>
                            </Button>
                        )}
                        <Button variant="outline" asChild>
                            <Link href={urls.forSaleIndex}>
                                <FolderOpen />
                                For-sale units
                            </Link>
                        </Button>
                        {can.create && (
                            <Button asChild>
                                <Link href={urls.create}>
                                    <Plus />
                                    Add bus
                                </Link>
                            </Button>
                        )}
                    </>
                }
            />

            <div className="grid gap-4 sm:grid-cols-3 xl:grid-cols-6">
                <KpiCard label="Total units" value={n(totals.total_units)} icon={<Bus />} />
                <KpiCard label="Active" value={n(totals.active)} hint="Running, not for sale" icon={<CheckCircle2 />} tone="text-emerald-600 dark:text-emerald-400" />
                <KpiCard label="Mechanical" value={n(totals.mechanical_breakdown)} hint="Open maintenance job order" icon={<Wrench />} tone="text-amber-600 dark:text-amber-400" />
                <KpiCard label="Accident" value={n(totals.accident_related)} hint="Accident related breakdown" icon={<AlertTriangle />} tone="text-red-600 dark:text-red-400" />
                <KpiCard label="On hold" value={n(totals.on_hold)} hint="Plate registration" icon={<PauseCircle />} tone="text-sky-600 dark:text-sky-400" />
                <KpiCard label="For sale" value={n(totals.for_sale)} icon={<Tag />} tone="text-purple-600 dark:text-purple-400" />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <DashboardCard title="Fleet condition" description="All units by current condition">
                    <DonutChart
                        centerLabel="Units"
                        data={[
                            { label: 'Active', value: totals.active, color: STATUS_COLORS.active },
                            { label: 'Mechanical', value: totals.mechanical_breakdown, color: STATUS_COLORS.mechanical },
                            { label: 'Accident', value: totals.accident_related, color: STATUS_COLORS.accident },
                            { label: 'On hold', value: totals.on_hold, color: STATUS_COLORS.onHold },
                            { label: 'For sale', value: totals.for_sale, color: STATUS_COLORS.forSale },
                        ]}
                    />
                </DashboardCard>
                <DashboardCard title="Condition by company" description="Units per company, stacked by condition" className="lg:col-span-2">
                    <SeriesChart
                        stacked
                        height={300}
                        categories={companySummary.map((row) => row.name)}
                        series={[
                            { key: 'active', label: 'Active', values: companySummary.map((row) => row.active), color: STATUS_COLORS.active },
                            { key: 'mechanical', label: 'Mechanical', values: companySummary.map((row) => row.mechanical_breakdown), color: STATUS_COLORS.mechanical },
                            { key: 'accident', label: 'Accident', values: companySummary.map((row) => row.accident_related), color: STATUS_COLORS.accident },
                            { key: 'onHold', label: 'On hold', values: companySummary.map((row) => row.on_hold), color: STATUS_COLORS.onHold },
                            { key: 'forSale', label: 'For sale', values: companySummary.map((row) => row.for_sale), color: STATUS_COLORS.forSale },
                        ]}
                    />
                </DashboardCard>
            </div>

            <div className="grid gap-4 xl:grid-cols-2">
                <SummaryTable title="Garage summary" description="Units not for sale, by garage" icon={<Building2 className="size-4" />} rows={garageSummary} />
                <SummaryTable title="Company summary" description="Units not for sale, by company" icon={<Bus className="size-4" />} rows={companySummary} />
            </div>

            <DashboardCard
                title="For sale summary"
                description="From the for-sale unit records, by company"
                className="gap-0 pb-0"
                contentClassName="overflow-x-auto px-0 pt-4"
                action={
                    <Button variant="outline" size="sm" asChild>
                        <Link href={urls.forSaleIndex}>Manage</Link>
                    </Button>
                }
            >
                <Table className="min-w-[760px]">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="pl-6">Company</TableHead>
                            <TableHead className="text-right">Mechanical</TableHead>
                            <TableHead className="text-right">Accident</TableHead>
                            <TableHead className="text-right">On hold (plate reg.)</TableHead>
                            <TableHead className="text-right">Breakdown total</TableHead>
                            <TableHead className="text-right">Running condition</TableHead>
                            <TableHead className="pr-6 text-right">Total for sale</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {forSaleSummary.rows.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={7} className="py-10 text-center text-muted-foreground">
                                    No for-sale records yet.
                                </TableCell>
                            </TableRow>
                        )}
                        {forSaleSummary.rows.map((row) => (
                            <TableRow key={row.name}>
                                <TableCell className="pl-6 font-medium">{row.name}</TableCell>
                                <TableCell className="text-right tabular-nums">{n(row.mechanical_breakdown)}</TableCell>
                                <TableCell className="text-right tabular-nums">{n(row.accident_related)}</TableCell>
                                <TableCell className="text-right tabular-nums">{n(row.on_hold)}</TableCell>
                                <TableCell className="text-right font-medium text-red-600 tabular-nums dark:text-red-400">{n(row.breakdown_total)}</TableCell>
                                <TableCell className="text-right font-medium text-emerald-600 tabular-nums dark:text-emerald-400">{n(row.running_condition)}</TableCell>
                                <TableCell className="pr-6 text-right font-semibold tabular-nums">{n(row.total_for_sale)}</TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                    {forSaleSummary.rows.length > 0 && (
                        <TableFooter>
                            <TableRow>
                                <TableCell className="pl-6 font-semibold">Total</TableCell>
                                <TableCell className="text-right tabular-nums">{n(forSaleSummary.mechanical_breakdown_total)}</TableCell>
                                <TableCell className="text-right tabular-nums">{n(forSaleSummary.accident_related_total)}</TableCell>
                                <TableCell className="text-right tabular-nums">{n(forSaleSummary.on_hold_total)}</TableCell>
                                <TableCell className="text-right tabular-nums">{n(forSaleSummary.breakdown_total)}</TableCell>
                                <TableCell className="text-right tabular-nums">{n(forSaleSummary.running_condition_total)}</TableCell>
                                <TableCell className="pr-6 text-right font-semibold tabular-nums">{n(forSaleSummary.total_for_sale)}</TableCell>
                            </TableRow>
                        </TableFooter>
                    )}
                </Table>
            </DashboardCard>

            <DashboardCard
                title="Fleet folder monitoring list"
                description={`Bus master: ${n(folderTotals.units)} · For sale: ${n(folderTotals.for_sale)}${hasFilters ? ` · ${n(filteredCount)} unit(s) match the filters` : ''}`}
                className="gap-0 pb-0"
                contentClassName="px-0 pt-4"
            >
                <form onSubmit={submit} className="flex flex-wrap items-center gap-2 border-b px-6 pb-4">
                    <div className="relative">
                        <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input aria-label="Search buses" placeholder="Bus no., plate, chassis, engine..." className="w-64 pl-8" value={draft.search} onChange={(event) => setDraft({ ...draft, search: event.target.value })} />
                    </div>
                    <FilterSelect label="Garage" allLabel="All garages" value={draft.garage} options={options.garages.map((garage) => ({ value: garage, label: garage }))} onChange={(garage) => setDraft({ ...draft, garage })} />
                    <FilterSelect label="Company" allLabel="All companies" value={draft.company} options={options.companies.map((company) => ({ value: company, label: company }))} onChange={(company) => setDraft({ ...draft, company })} />
                    <FilterSelect label="Condition" allLabel="All conditions" value={draft.operational_status} options={options.operational_statuses} onChange={(operational_status) => setDraft({ ...draft, operational_status })} />
                    <FilterSelect label="Sale status" allLabel="All sale statuses" value={draft.sale_status} options={options.sale_statuses} onChange={(sale_status) => setDraft({ ...draft, sale_status })} />
                    <Button type="submit">Apply</Button>
                    {hasFilters && (
                        <Button type="button" variant="ghost" onClick={() => router.get(urls.index)}>
                            <X />
                            Clear
                        </Button>
                    )}
                </form>

                {folders.length === 0 ? (
                    <p className="py-12 text-center text-sm text-muted-foreground">No folders found.</p>
                ) : (
                    <Tabs defaultValue={folders[0].key} className="gap-0">
                        <div className="overflow-x-auto px-6 pt-4">
                            <TabsList>
                                {folders.map((folder) => (
                                    <TabsTrigger key={folder.key} value={folder.key}>
                                        {folder.type === 'for_sale' ? <Tag /> : <FolderOpen />}
                                        {folder.label}
                                        <Badge variant="secondary" className="tabular-nums">
                                            {n(folder.count)}
                                        </Badge>
                                    </TabsTrigger>
                                ))}
                            </TabsList>
                        </div>
                        {folders.map((folder) => (
                            <TabsContent key={folder.key} value={folder.key} className="mt-2">
                                {folder.type === 'garage' ? <BusFolder folder={folder} canEdit={can.edit} /> : <ForSaleFolder folder={folder} canEdit={can.for_sale_edit} />}
                            </TabsContent>
                        ))}
                    </Tabs>
                )}
            </DashboardCard>
        </AppLayout>
    );
}

function SummaryTable({ title, description, icon, rows }: { title: string; description: string; icon: React.ReactNode; rows: SummaryRow[] }) {
    const sum = (key: keyof SummaryRow) => rows.reduce((total, row) => total + Number(row[key]), 0);

    return (
        <DashboardCard
            title={
                <span className="flex items-center gap-2">
                    {icon}
                    {title}
                </span>
            }
            description={description}
            className="gap-0 pb-0"
            contentClassName="overflow-x-auto px-0 pt-4"
        >
            <Table className="min-w-[560px]">
                <TableHeader>
                    <TableRow>
                        <TableHead className="pl-6">Name</TableHead>
                        <TableHead className="text-right">Active</TableHead>
                        <TableHead className="text-right">Mechanical</TableHead>
                        <TableHead className="text-right">Accident</TableHead>
                        <TableHead className="text-right">On hold</TableHead>
                        <TableHead className="pr-6 text-right">Not for sale</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {rows.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={6} className="py-10 text-center text-muted-foreground">
                                No data found.
                            </TableCell>
                        </TableRow>
                    )}
                    {rows.map((row) => (
                        <TableRow key={row.name}>
                            <TableCell className="pl-6 font-medium">{row.name}</TableCell>
                            <TableCell className="text-right text-emerald-600 tabular-nums dark:text-emerald-400">{n(row.active)}</TableCell>
                            <TableCell className="text-right tabular-nums">{n(row.mechanical_breakdown)}</TableCell>
                            <TableCell className="text-right tabular-nums">{n(row.accident_related)}</TableCell>
                            <TableCell className="text-right tabular-nums">{n(row.on_hold)}</TableCell>
                            <TableCell className="pr-6 text-right font-semibold tabular-nums">{n(row.not_for_sale)}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
                {rows.length > 0 && (
                    <TableFooter>
                        <TableRow>
                            <TableCell className="pl-6 font-semibold">Total</TableCell>
                            <TableCell className="text-right tabular-nums">{n(sum('active'))}</TableCell>
                            <TableCell className="text-right tabular-nums">{n(sum('mechanical_breakdown'))}</TableCell>
                            <TableCell className="text-right tabular-nums">{n(sum('accident_related'))}</TableCell>
                            <TableCell className="text-right tabular-nums">{n(sum('on_hold'))}</TableCell>
                            <TableCell className="pr-6 text-right font-semibold tabular-nums">{n(sum('not_for_sale'))}</TableCell>
                        </TableRow>
                    </TableFooter>
                )}
            </Table>
        </DashboardCard>
    );
}

function CompanyRow({ name, count, span }: { name: string; count: number; span: number }) {
    return (
        <TableRow className="bg-muted/40 hover:bg-muted/40">
            <TableCell colSpan={span} className="pl-6">
                <div className="flex items-center justify-between gap-2 text-sm font-semibold">
                    <span className="flex items-center gap-2">
                        <Building2 className="size-4 text-muted-foreground" />
                        {name}
                    </span>
                    <span className="text-xs font-normal text-muted-foreground">{n(count)} unit(s)</span>
                </div>
            </TableCell>
        </TableRow>
    );
}

function BusFolder({ folder, canEdit }: { folder: Extract<Folder, { type: 'garage' }>; canEdit: boolean }) {
    const span = canEdit ? 11 : 10;

    return (
        <Table className="min-w-[1200px]">
            <TableHeader>
                <TableRow>
                    <TableHead className="pl-6">Bus no.</TableHead>
                    <TableHead>Plate no.</TableHead>
                    <TableHead>Company</TableHead>
                    <TableHead>Garage</TableHead>
                    <TableHead>Condition</TableHead>
                    <TableHead>Sale status</TableHead>
                    <TableHead>Chassis no.</TableHead>
                    <TableHead>Engine no.</TableHead>
                    <TableHead>Case no.</TableHead>
                    <TableHead>Remarks</TableHead>
                    {canEdit && <TableHead className="pr-6 text-right">Action</TableHead>}
                </TableRow>
            </TableHeader>
            <TableBody>
                {folder.groups.length === 0 && (
                    <TableRow>
                        <TableCell colSpan={span} className="py-10 text-center text-muted-foreground">
                            No bus records found for this folder.
                        </TableCell>
                    </TableRow>
                )}
                {folder.groups.map((group) => [
                    <CompanyRow key={`${group.name}-head`} name={group.name} count={group.rows.length} span={span} />,
                    ...group.rows.map((bus) => (
                        <TableRow key={bus.id}>
                            <TableCell className="pl-6 font-semibold">{bus.bus_no}</TableCell>
                            <TableCell>{bus.plate_no ?? '—'}</TableCell>
                            <TableCell>{bus.company ?? '—'}</TableCell>
                            <TableCell>{bus.garage ?? '—'}</TableCell>
                            <TableCell>
                                <Badge variant="outline" className={busConditionClass(bus.operational_status)}>
                                    {bus.operational_status_label}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                {bus.for_sale ? (
                                    <Badge variant="outline" className={tones.RED}>
                                        For Sale
                                    </Badge>
                                ) : (
                                    <Badge variant="outline" className="text-muted-foreground">
                                        {bus.sale_status_label}
                                    </Badge>
                                )}
                            </TableCell>
                            <TableCell className="font-mono text-xs">{bus.chassis_number ?? '—'}</TableCell>
                            <TableCell className="font-mono text-xs">{bus.engine_number ?? '—'}</TableCell>
                            <TableCell className="font-mono text-xs">{bus.case_number ?? '—'}</TableCell>
                            <TableCell className="max-w-64 whitespace-normal text-muted-foreground">{bus.remarks ?? '—'}</TableCell>
                            {canEdit && (
                                <TableCell className="pr-6 text-right">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={bus.edit_url}>
                                            <Pencil />
                                            Update
                                        </Link>
                                    </Button>
                                </TableCell>
                            )}
                        </TableRow>
                    )),
                ])}
            </TableBody>
        </Table>
    );
}

function ForSaleFolder({ folder, canEdit }: { folder: Extract<Folder, { type: 'for_sale' }>; canEdit: boolean }) {
    const span = canEdit ? 13 : 12;

    return (
        <Table className="min-w-[1400px]">
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
                    <TableHead className="text-right">Days</TableHead>
                    <TableHead>Unit location</TableHead>
                    <TableHead>Progress</TableHead>
                    <TableHead>Remarks</TableHead>
                    {canEdit && <TableHead className="pr-6 text-right">Action</TableHead>}
                </TableRow>
            </TableHeader>
            <TableBody>
                {folder.groups.length === 0 && (
                    <TableRow>
                        <TableCell colSpan={span} className="py-10 text-center text-muted-foreground">
                            No for-sale records found.
                        </TableCell>
                    </TableRow>
                )}
                {folder.groups.map((group) => [
                    <CompanyRow key={`${group.name}-head`} name={group.name} count={group.rows.length} span={span} />,
                    ...group.rows.map((record) => (
                        <TableRow key={record.id}>
                            <TableCell className="pl-6 font-semibold">{record.bus_no}</TableCell>
                            <TableCell>{record.plate_no ?? '—'}</TableCell>
                            <TableCell>{record.company ?? '—'}</TableCell>
                            <TableCell>{record.garage ?? '—'}</TableCell>
                            <TableCell>
                                <Badge variant="outline" className={forSaleStatusClass(record.status)}>
                                    {record.status_label}
                                </Badge>
                            </TableCell>
                            <TableCell>{record.storage_area ?? '—'}</TableCell>
                            <TableCell className="whitespace-nowrap">{record.breakdown_start ?? '—'}</TableCell>
                            <TableCell className="whitespace-nowrap">{record.breakdown_end ?? '—'}</TableCell>
                            <TableCell className="text-right tabular-nums">{n(record.days)}</TableCell>
                            <TableCell>{record.unit_location ?? '—'}</TableCell>
                            <TableCell>{record.progress ?? '—'}</TableCell>
                            <TableCell className="max-w-64 whitespace-normal text-muted-foreground">{record.remarks ?? '—'}</TableCell>
                            {canEdit && (
                                <TableCell className="pr-6 text-right">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={record.edit_url}>
                                            <Pencil />
                                            Update
                                        </Link>
                                    </Button>
                                </TableCell>
                            )}
                        </TableRow>
                    )),
                ])}
            </TableBody>
        </Table>
    );
}

function FilterSelect({ label, allLabel, value, options, onChange }: { label: string; allLabel: string; value: string; options: Option[]; onChange: (value: string) => void }) {
    return (
        <Select value={value || ALL} onValueChange={(next) => onChange(next === ALL ? '' : next)}>
            <SelectTrigger className="w-44" aria-label={label}>
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
