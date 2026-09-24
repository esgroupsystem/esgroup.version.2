import { Link, router } from '@inertiajs/react';
import { Bus, ChartPie, Eye, Inbox, Plus, Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { DataPagination } from '@/components/data-pagination';
import { inventoryStatusClass } from '@/components/inventory/rollback-dialog';
import { PageHeader, StatCard } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { Paginated } from '@/types';

interface Row {
    id: number;
    number: string;
    vehicle: { plate_number: string; detail: string } | null;
    location: string;
    mechanic: string;
    date: string | null;
    day: string | null;
    job_order_no: string | null;
    items_count: number;
    status: { key: string; label: string };
    creator: string;
    show_url: string;
}

interface Props {
    records: Paginated<Row>;
    filters: { search: string };
    can: { create: boolean };
    urls: { index: string; create: string; dashboard: string };
}

export default function PartsOutIndex({ records, filters, can, urls }: Props) {
    const [search, setSearch] = useState(filters.search);
    const first = useRef(true);

    useEffect(() => {
        if (first.current) {
            first.current = false;
            return;
        }
        const timer = window.setTimeout(() => router.get(urls.index, search.trim() ? { search: search.trim() } : {}, { preserveState: true, replace: true }), 350);

        return () => window.clearTimeout(timer);
    }, [search]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <AppLayout title="Parts Issuance">
            <PageHeader
                title="Parts Out Records"
                description="Monitor issued parts, vehicle usage, mechanics, job orders, stock deduction, and rollback status."
                actions={
                    <>
                        <Button variant="outline" asChild>
                            <a href={urls.dashboard}>
                                <ChartPie />
                                Stock dashboard
                            </a>
                        </Button>
                        {can.create && (
                            <Button asChild>
                                <Link href={urls.create}>
                                    <Plus />
                                    New parts out
                                </Link>
                            </Button>
                        )}
                    </>
                }
            />

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard label="Total records" value={records.total.toLocaleString()} />
                <StatCard label="Items on this page" value={records.data.reduce((sum, row) => sum + row.items_count, 0).toLocaleString()} />
                <StatCard label="Vehicle based" value="Yes" />
                <StatCard label="Rollback" value="Controlled" />
            </div>

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Parts out records</CardTitle>
                        <CardDescription>Results update automatically after typing.</CardDescription>
                    </div>
                    <div className="flex gap-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                aria-label="Search parts out records"
                                placeholder="Search parts out no., mechanic, requester, JO no., vehicle..."
                                className="w-96 pl-8"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                            />
                        </div>
                        {search && (
                            <Button variant="outline" onClick={() => setSearch('')}>
                                <X />
                                Clear
                            </Button>
                        )}
                    </div>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[1200px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Parts out</TableHead>
                                <TableHead>Vehicle</TableHead>
                                <TableHead>Garage</TableHead>
                                <TableHead>Mechanic</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>JO no.</TableHead>
                                <TableHead className="text-center">Items</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Encoded by</TableHead>
                                <TableHead className="pr-6 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {records.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={10} className="py-12">
                                        <div className="flex flex-col items-center gap-1 text-center text-muted-foreground">
                                            <Inbox className="size-8" />
                                            No parts out records found.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {records.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="pl-6">
                                        <Link href={row.show_url} className="font-semibold hover:underline">
                                            {row.number}
                                        </Link>
                                    </TableCell>
                                    <TableCell>
                                        {row.vehicle ? (
                                            <div className="flex items-center gap-2">
                                                <Bus className="size-4 text-muted-foreground" />
                                                <div>
                                                    <div className="font-medium">{row.vehicle.plate_number}</div>
                                                    <div className="text-xs text-muted-foreground">{row.vehicle.detail}</div>
                                                </div>
                                            </div>
                                        ) : (
                                            <span className="text-muted-foreground italic">No vehicle selected</span>
                                        )}
                                    </TableCell>
                                    <TableCell>{row.location}</TableCell>
                                    <TableCell>{row.mechanic}</TableCell>
                                    <TableCell>
                                        {row.date ? (
                                            <>
                                                <div>{row.date}</div>
                                                <div className="text-xs text-muted-foreground">{row.day}</div>
                                            </>
                                        ) : (
                                            '—'
                                        )}
                                    </TableCell>
                                    <TableCell>{row.job_order_no || <span className="text-muted-foreground">No JO</span>}</TableCell>
                                    <TableCell className="text-center">
                                        <Badge variant="secondary">
                                            {row.items_count} item{row.items_count === 1 ? '' : 's'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" className={inventoryStatusClass(row.status.key)}>
                                            {row.status.label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{row.creator}</TableCell>
                                    <TableCell className="pr-6 text-right">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={row.show_url}>
                                                <Eye />
                                                View
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={records} noun="record" />
        </AppLayout>
    );
}
