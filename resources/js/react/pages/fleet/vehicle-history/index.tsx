import { Link, router } from '@inertiajs/react';
import { Bus, Search, Warehouse, Wrench } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { DataPagination } from '@/components/data-pagination';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { busStatusClass } from '@/lib/bus-status';
import type { Paginated } from '@/types';

interface BusRow {
    id: number;
    plate_number: string | null;
    body_number: string | null;
    name: string | null;
    garage: string | null;
    status: string | null;
    show_url: string;
}

interface Props {
    buses: Paginated<BusRow>;
    filters: { search: string };
    urls: { index: string };
}

export default function VehicleHistoryIndex({ buses, filters, urls }: Props) {
    const [search, setSearch] = useState(filters.search);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.index, search ? { search } : {});
    };

    return (
        <AppLayout title="Vehicle History">
            <PageHeader title="Vehicle History" description="Browse buses and view their maintenance (parts-out) history." />

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Bus list</CardTitle>
                        <CardDescription>Click a bus to open its maintenance records.</CardDescription>
                    </div>
                    <form onSubmit={submit} className="flex gap-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input aria-label="Search buses" placeholder="Search plate, body no, name, garage..." className="w-72 pl-8" value={search} onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        {filters.search && (
                            <Button type="button" variant="outline" onClick={() => router.get(urls.index)}>
                                Clear
                            </Button>
                        )}
                        <Button type="submit">Search</Button>
                    </form>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Bus</TableHead>
                                <TableHead>Garage</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="pr-6 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {buses.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="py-12 text-center text-muted-foreground">
                                        No buses found
                                    </TableCell>
                                </TableRow>
                            )}
                            {buses.data.map((bus) => (
                                <TableRow key={bus.id} className="cursor-pointer" onClick={() => router.visit(bus.show_url)}>
                                    <TableCell className="pl-6">
                                        <div className="flex items-center gap-3">
                                            <div className="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                                <Bus className="size-4" />
                                            </div>
                                            <div>
                                                <div className="font-medium">{bus.plate_number ?? 'N/A'}</div>
                                                <div className="text-xs text-muted-foreground">
                                                    Body no: {bus.body_number ?? 'N/A'} {bus.name && `· ${bus.name}`}
                                                </div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span className="inline-flex items-center gap-1">
                                            <Warehouse className="size-4 text-muted-foreground" />
                                            {bus.garage ?? 'N/A'}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" className={busStatusClass(bus.status)}>
                                            {bus.status ?? 'N/A'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        <Button variant="outline" size="sm" asChild onClick={(event) => event.stopPropagation()}>
                                            <Link href={bus.show_url}>
                                                <Wrench />
                                                History
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={buses} noun="bus" />
        </AppLayout>
    );
}
