import { Link, router } from '@inertiajs/react';
import { AlertTriangle, ArrowLeftRight, Boxes, Package, PackageX, Search, Warehouse, X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { DashboardCard, DonutChart, KpiCard, SimpleBarChart } from '@/components/dashboard/charts';
import { DataPagination } from '@/components/data-pagination';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { stockLevelClass, tones } from '@/lib/dashboard-tones';
import type { Paginated } from '@/types';

interface StockRow {
    id: number;
    category: string;
    name: string;
    details: string | null;
    part_number: string | null;
    unit: string | null;
    qty: number;
    main_qty: number;
    balintawak_qty: number;
    suggestion: string | null;
}

type TabKey = 'main' | 'balintawak' | 'transfer';

interface Props {
    filters: { search: string; location: string; tab: TabKey };
    locationNames: { main: string; balintawak: string };
    totals: { items: number; stock: number; main: number; balintawak: number; low: number; out: number };
    main: Paginated<StockRow>;
    balintawak: Paginated<StockRow>;
    transfer: Paginated<StockRow>;
    urls: { index: string; items: string };
}

const ALL = '__all';
const number = new Intl.NumberFormat('en-US');

const levelOf = (qty: number) => (qty <= 0 ? 'out' : qty <= 5 ? 'low' : 'ok');
const LEVEL_LABEL = { out: 'Out of stock', low: 'Low stock', ok: 'Available' } as const;

export default function MaintenanceStockDashboard({ filters, locationNames, totals, main, balintawak, transfer, urls }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [location, setLocation] = useState(filters.location);
    useEffect(() => {
        setSearch(filters.search);
        setLocation(filters.location);
    }, [filters.search, filters.location]);

    const query = (next: Partial<Props['filters']>) => {
        const merged = { ...filters, ...next };
        const params: Record<string, string> = {};
        if (merged.search) params.search = merged.search;
        if (merged.location) params.location = merged.location;
        if (merged.tab !== 'main') params.tab = merged.tab;
        return params;
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.index, query({ search: search.trim(), location }), { preserveScroll: true });
    };

    const pages: Record<TabKey, Paginated<StockRow>> = { main, balintawak, transfer };
    const current = pages[filters.tab];
    const available = Math.max(totals.items - totals.low - totals.out, 0);

    return (
        <AppLayout title="Maintenance Stock">
            <PageHeader
                title="Maintenance Stock Dashboard"
                description={`Stock on hand per location, low and out-of-stock items, and transfer suggestions between ${locationNames.main} and ${locationNames.balintawak}.`}
                actions={
                    <Button variant="outline" asChild>
                        <Link href={urls.items}>
                            <Package />
                            Manage items
                        </Link>
                    </Button>
                }
            />

            <div className="grid gap-4 sm:grid-cols-3 xl:grid-cols-6">
                <KpiCard label="Total items" value={number.format(totals.items)} icon={<Boxes />} />
                <KpiCard label="Total stock" value={number.format(totals.stock)} icon={<Warehouse />} />
                <KpiCard label={locationNames.main} value={number.format(totals.main)} tone="text-primary" />
                <KpiCard label={locationNames.balintawak} value={number.format(totals.balintawak)} tone="text-sky-600 dark:text-sky-400" />
                <KpiCard label="Low stock" value={number.format(totals.low)} hint="1 to 5 on hand" icon={<AlertTriangle />} tone="text-amber-600 dark:text-amber-400" />
                <KpiCard label="Out of stock" value={number.format(totals.out)} hint="Nothing on hand" icon={<PackageX />} tone="text-red-600 dark:text-red-400" />
            </div>

            <div className="grid gap-4 lg:grid-cols-2">
                <DashboardCard title="Stock health" description="Items by total quantity across all locations">
                    <DonutChart
                        centerLabel="Items"
                        data={[
                            { label: 'Available (6+)', value: available, color: '#10b981' },
                            { label: 'Low stock (1-5)', value: totals.low, color: '#f59e0b' },
                            { label: 'Out of stock', value: totals.out, color: '#ef4444' },
                        ]}
                    />
                </DashboardCard>
                <DashboardCard title="Stock by location" description="Units on hand">
                    <SimpleBarChart
                        valueLabel="Units"
                        height={260}
                        data={[
                            { label: locationNames.main, value: totals.main, color: 'var(--chart-1)' },
                            { label: locationNames.balintawak, value: totals.balintawak, color: 'var(--chart-2)' },
                        ]}
                    />
                </DashboardCard>
            </div>

            <DashboardCard title="Stock records" description="Search a product or part number, or narrow the view." className="gap-0 pb-0" contentClassName="px-0 pt-4">
                <form onSubmit={submit} className="flex flex-wrap items-center gap-2 px-6 pb-4">
                    <div className="relative">
                        <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input aria-label="Search product or part number" placeholder="Search by product name or part number" className="w-80 pl-8" value={search} onChange={(event) => setSearch(event.target.value)} />
                    </div>
                    <Select value={location || ALL} onValueChange={(value) => setLocation(value === ALL ? '' : value)}>
                        <SelectTrigger className="w-48" aria-label="Filter view">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>All</SelectItem>
                            <SelectItem value="main">{locationNames.main} only</SelectItem>
                            <SelectItem value="balintawak">{locationNames.balintawak} only</SelectItem>
                            <SelectItem value="needs_transfer">Needs transfer</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button type="submit">Apply</Button>
                    {(filters.search || filters.location) && (
                        <Button type="button" variant="ghost" onClick={() => router.get(urls.index, filters.tab !== 'main' ? { tab: filters.tab } : {})}>
                            <X />
                            Clear
                        </Button>
                    )}
                </form>

                <div className="border-t px-6 pt-4">
                    <Tabs value={filters.tab} onValueChange={(tab) => router.get(urls.index, query({ tab: tab as TabKey }), { preserveScroll: true, preserveState: true })}>
                        <TabsList>
                            <TabsTrigger value="main">
                                {locationNames.main} <Badge variant="secondary">{main.total}</Badge>
                            </TabsTrigger>
                            <TabsTrigger value="balintawak">
                                {locationNames.balintawak} <Badge variant="secondary">{balintawak.total}</Badge>
                            </TabsTrigger>
                            <TabsTrigger value="transfer">
                                <ArrowLeftRight />
                                Needs transfer <Badge variant="secondary">{transfer.total}</Badge>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                </div>

                <Table className="mt-2 min-w-[760px]">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="pl-6">Category</TableHead>
                            <TableHead>Product</TableHead>
                            {filters.tab === 'transfer' ? (
                                <>
                                    <TableHead className="text-center">{locationNames.main} qty</TableHead>
                                    <TableHead className="text-center">{locationNames.balintawak} qty</TableHead>
                                    <TableHead className="pr-6">Suggestion</TableHead>
                                </>
                            ) : (
                                <>
                                    <TableHead className="text-center">Unit</TableHead>
                                    <TableHead className="text-center">Available qty</TableHead>
                                    <TableHead className="pr-6 text-center">Status</TableHead>
                                </>
                            )}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {current.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={5} className="py-12 text-center text-muted-foreground">
                                    {filters.tab === 'transfer' ? 'No transfer suggestions found.' : `No ${locationNames[filters.tab]} stock found.`}
                                </TableCell>
                            </TableRow>
                        )}
                        {current.data.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="pl-6">{row.category}</TableCell>
                                <TableCell className="max-w-96 whitespace-normal">
                                    <div className="font-medium">{row.name}</div>
                                    {filters.tab !== 'transfer' && <div className="text-xs text-muted-foreground">{row.details || 'No details available'}</div>}
                                    {row.part_number && <div className="font-mono text-xs text-muted-foreground">Part no: {row.part_number}</div>}
                                </TableCell>
                                {filters.tab === 'transfer' ? (
                                    <>
                                        <TableCell className="text-center tabular-nums">{number.format(row.main_qty)}</TableCell>
                                        <TableCell className="text-center tabular-nums">{number.format(row.balintawak_qty)}</TableCell>
                                        <TableCell className="pr-6">
                                            <Badge variant="outline" className={tones.AMBER}>
                                                {row.suggestion}
                                            </Badge>
                                        </TableCell>
                                    </>
                                ) : (
                                    <>
                                        <TableCell className="text-center">{row.unit || '—'}</TableCell>
                                        <TableCell className="text-center">
                                            <Badge variant="outline" className={`min-w-10 justify-center tabular-nums ${stockLevelClass(levelOf(row.qty))}`}>
                                                {number.format(row.qty)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="pr-6 text-center">
                                            <Badge variant="outline" className={stockLevelClass(levelOf(row.qty))}>
                                                {LEVEL_LABEL[levelOf(row.qty)]}
                                            </Badge>
                                        </TableCell>
                                    </>
                                )}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <div className="border-t px-6 py-3">
                    <DataPagination paginator={current} noun="record" />
                </div>
            </DashboardCard>
        </AppLayout>
    );
}
