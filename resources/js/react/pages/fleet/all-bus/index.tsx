import { Link, router } from '@inertiajs/react';
import { Bus, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataPagination } from '@/components/data-pagination';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { Paginated } from '@/types';

interface BusRow {
    id: number;
    garage: string;
    name: string;
    body_number: string;
    plate_number: string;
    edit_url: string;
    destroy_url: string;
}

interface Props {
    buses: Paginated<BusRow>;
    filters: { search: string };
    can: { create: boolean; edit: boolean; delete: boolean };
    urls: { index: string; create: string };
}

export default function AllBusIndex({ buses, filters, can, urls }: Props) {
    const [search, setSearch] = useState(filters.search);
    const first = useRef(true);

    useEffect(() => {
        if (first.current) {
            first.current = false;
            return;
        }
        const timer = window.setTimeout(() => router.get(urls.index, search ? { search } : {}, { preserveState: true, replace: true }), 400);

        return () => window.clearTimeout(timer);
    }, [search]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <AppLayout title="Bus List">
            <PageHeader
                title="All Bus"
                description="Manage bus details including garage, body number, and plate number."
                actions={
                    can.create && (
                        <Button asChild>
                            <Link href={urls.create}>
                                <Plus />
                                Add bus
                            </Link>
                        </Button>
                    )
                }
            />

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Bus records</CardTitle>
                        <CardDescription>
                            <Badge variant="secondary">Total records: {buses.total.toLocaleString()}</Badge>
                        </CardDescription>
                    </div>
                    <div className="relative">
                        <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            aria-label="Search bus records"
                            placeholder="Search garage, name, body number, plate number..."
                            className="w-80 pl-8"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                        />
                    </div>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Garage</TableHead>
                                <TableHead>Bus name</TableHead>
                                <TableHead>Body number</TableHead>
                                <TableHead>Plate number</TableHead>
                                <TableHead className="pr-6 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {buses.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-12">
                                        <div className="flex flex-col items-center gap-1 text-center text-muted-foreground">
                                            <Bus className="size-8" />
                                            No bus records found.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {buses.data.map((bus) => (
                                <TableRow key={bus.id}>
                                    <TableCell className="pl-6">{bus.garage}</TableCell>
                                    <TableCell>{bus.name}</TableCell>
                                    <TableCell className="font-medium">{bus.body_number}</TableCell>
                                    <TableCell className="font-mono text-sm">{bus.plate_number}</TableCell>
                                    <TableCell className="pr-6">
                                        <div className="flex justify-end gap-1">
                                            {can.edit && (
                                                <Button variant="ghost" size="icon" className="size-8" asChild>
                                                    <Link href={bus.edit_url} aria-label={`Edit ${bus.body_number}`}>
                                                        <Pencil />
                                                    </Link>
                                                </Button>
                                            )}
                                            {can.delete && (
                                                <ConfirmAction
                                                    title="Delete this bus?"
                                                    description={`${bus.body_number} (${bus.plate_number}) will be removed from the bus list.`}
                                                    confirmLabel="Delete"
                                                    destructive
                                                    onConfirm={() => router.delete(bus.destroy_url, { preserveScroll: true })}
                                                    trigger={
                                                        <IconButton label={`Delete ${bus.body_number}`}>
                                                            <Trash2 className="text-destructive" />
                                                        </IconButton>
                                                    }
                                                />
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={buses} noun="bus record" />
        </AppLayout>
    );
}
