import { Link, router } from '@inertiajs/react';
import { ArrowDown, ArrowLeftRight, Eye, Plus, Search, Undo2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { DataPagination } from '@/components/data-pagination';
import { RollbackDialog } from '@/components/inventory/rollback-dialog';
import { PageHeader } from '@/components/page-header';
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
    creator: string;
    rolled_back: boolean;
    rolled_back_at: string | null;
    from: string;
    to: string;
    requested_by: string;
    received_by: string;
    items_count: number;
    remarks: string | null;
    created_date: string | null;
    created_time: string | null;
    show_url: string;
    rollback_url: string;
}

interface Props {
    records: Paginated<Row>;
    filters: { search: string };
    can: { create: boolean; rollback: boolean };
    urls: { index: string; create: string };
}

export default function StockTransfersIndex({ records, filters, can, urls }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [rollback, setRollback] = useState<Row | null>(null);
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
        <AppLayout title="Stock Transfer">
            <PageHeader
                title="Stock Transfers"
                description="Move stock between garages and roll back a transfer when needed."
                actions={
                    can.create && (
                        <Button asChild>
                            <Link href={urls.create}>
                                <Plus />
                                New transfer
                            </Link>
                        </Button>
                    )
                }
            />

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Transfer records</CardTitle>
                        <CardDescription>{records.total.toLocaleString()} transfer(s)</CardDescription>
                    </div>
                    <div className="flex gap-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                aria-label="Search stock transfers"
                                placeholder="Search transfer no., requester, receiver, remarks..."
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
                                <TableHead className="pl-6">Transfer no.</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Route</TableHead>
                                <TableHead>Requested / received by</TableHead>
                                <TableHead className="text-center">Items</TableHead>
                                <TableHead>Remarks</TableHead>
                                <TableHead>Date created</TableHead>
                                <TableHead className="pr-6 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {records.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={8} className="py-12">
                                        <div className="flex flex-col items-center gap-1 text-center text-muted-foreground">
                                            <ArrowLeftRight className="size-8" />
                                            No stock transfers found. Try changing your search keyword or create a new transfer record.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {records.data.map((row) => (
                                <TableRow key={row.id} className="align-top">
                                    <TableCell className="pl-6">
                                        <Link href={row.show_url} className="font-semibold text-primary hover:underline">
                                            {row.number}
                                        </Link>
                                        <div className="text-xs text-muted-foreground">Created by: {row.creator}</div>
                                    </TableCell>
                                    <TableCell>
                                        {row.rolled_back ? (
                                            <>
                                                <Badge variant="outline" className="border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400">
                                                    Rolled back
                                                </Badge>
                                                <div className="mt-1 text-xs text-muted-foreground">{row.rolled_back_at}</div>
                                            </>
                                        ) : (
                                            <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">
                                                Completed
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <div className="font-medium">{row.from}</div>
                                        <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                            <ArrowDown className="size-3" />
                                            to
                                        </div>
                                        <div className="font-medium">{row.to}</div>
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        <div className="text-xs text-muted-foreground">Requested by</div>
                                        <div>{row.requested_by}</div>
                                        <div className="mt-1 text-xs text-muted-foreground">Received by</div>
                                        <div>{row.received_by}</div>
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <Badge variant="secondary">{row.items_count} item(s)</Badge>
                                    </TableCell>
                                    <TableCell className="max-w-52 whitespace-normal text-muted-foreground">{row.remarks || 'No remarks provided.'}</TableCell>
                                    <TableCell>
                                        <div>{row.created_date}</div>
                                        <div className="text-xs text-muted-foreground">{row.created_time}</div>
                                    </TableCell>
                                    <TableCell className="pr-6">
                                        <div className="flex justify-end gap-1">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={row.show_url}>
                                                    <Eye />
                                                    View
                                                </Link>
                                            </Button>
                                            {can.rollback && !row.rolled_back && (
                                                <Button variant="outline" size="sm" onClick={() => setRollback(row)}>
                                                    <Undo2 className="text-destructive" />
                                                    Rollback
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

            <DataPagination paginator={records} noun="transfer" />

            {rollback && (
                <RollbackDialog
                    open
                    onOpenChange={(open) => !open && setRollback(null)}
                    title={`Rollback ${rollback.number}?`}
                    description={`Quantities will be returned to ${rollback.from} and deducted from ${rollback.to}.`}
                    effect="The transfer and all its items will be marked Rolled Back."
                    url={rollback.rollback_url}
                    method="post"
                    defaultReason="Rollback from stock transfer list"
                />
            )}
        </AppLayout>
    );
}
