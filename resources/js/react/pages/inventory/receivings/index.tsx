import { Link, router } from '@inertiajs/react';
import { ChartPie, Eye, Plus, Search, Truck, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { DataPagination } from '@/components/data-pagination';
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
    location: string;
    delivered_by: string;
    delivery_date: string | null;
    delivery_day: string | null;
    items_count: number;
    remarks: string | null;
    receiver: string;
    created_date: string | null;
    created_time: string | null;
    show_url: string;
}

interface Props {
    records: Paginated<Row>;
    filters: { search: string };
    can: { create: boolean };
    urls: { index: string; create: string; dashboard: string };
}

export default function ReceivingsIndex({ records, filters, can, urls }: Props) {
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
        <AppLayout title="Receiving Area">
            <PageHeader
                title="Receiving Records"
                description="Record delivered products per garage, attach proof of delivery, and add stock."
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
                                    New receiving
                                </Link>
                            </Button>
                        )}
                    </>
                }
            />

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard label="Total records" value={records.total.toLocaleString()} />
                <StatCard label="Items on this page" value={records.data.reduce((sum, row) => sum + row.items_count, 0).toLocaleString()} />
                <StatCard label="Garage based" value="Yes" />
                <StatCard label="Delivery proof" value="Image" />
            </div>

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Receiving records</CardTitle>
                        <CardDescription>Results update automatically after typing.</CardDescription>
                    </div>
                    <div className="flex gap-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                aria-label="Search receiving records"
                                placeholder="Search receiving no., delivered by, remarks, garage..."
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
                    <Table className="min-w-[1100px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Receiving</TableHead>
                                <TableHead>Garage</TableHead>
                                <TableHead>Delivered by</TableHead>
                                <TableHead>Delivery date</TableHead>
                                <TableHead className="text-center">Items</TableHead>
                                <TableHead>Remarks</TableHead>
                                <TableHead>Received by</TableHead>
                                <TableHead>Date created</TableHead>
                                <TableHead className="pr-6 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {records.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={9} className="py-12">
                                        <div className="flex flex-col items-center gap-2 text-center text-muted-foreground">
                                            <Truck className="size-8" />
                                            No receiving records found.
                                            {can.create && (
                                                <Button size="sm" asChild>
                                                    <Link href={urls.create}>
                                                        <Plus />
                                                        Create receiving
                                                    </Link>
                                                </Button>
                                            )}
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
                                    <TableCell>{row.location}</TableCell>
                                    <TableCell>{row.delivered_by}</TableCell>
                                    <TableCell>
                                        {row.delivery_date ? (
                                            <>
                                                <div>{row.delivery_date}</div>
                                                <div className="text-xs text-muted-foreground">{row.delivery_day}</div>
                                            </>
                                        ) : (
                                            <Badge variant="outline">No date</Badge>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <Badge variant="secondary">
                                            {row.items_count} item{row.items_count === 1 ? '' : 's'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="max-w-52 truncate text-muted-foreground" title={row.remarks ?? undefined}>
                                        {row.remarks || 'No remarks provided.'}
                                    </TableCell>
                                    <TableCell>{row.receiver}</TableCell>
                                    <TableCell>
                                        <div>{row.created_date ?? 'N/A'}</div>
                                        <div className="text-xs text-muted-foreground">{row.created_time}</div>
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={row.show_url}>
                                                <Eye />
                                                Details
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={records} noun="receiving record" />
        </AppLayout>
    );
}
