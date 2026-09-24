import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, PackageOpen, Plus, Undo2 } from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { RollbackDialog } from '@/components/inventory/rollback-dialog';
import { PageHeader, StatCard } from '@/components/page-header';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';

interface Props {
    record: {
        id: number;
        number: string;
        date: string;
        from: string;
        to: string;
        requested_by: string;
        received_by: string;
        creator: string;
        remarks: string | null;
        rolled_back: boolean;
        rollback: { by: string; at: string | null; reason: string | null } | null;
        items: { id: number; name: string; category: string | null; part_number: string; unit: string; qty: number; rolled_back: boolean }[];
    };
    can: { create: boolean; rollback: boolean };
    urls: { index: string; create: string; rollback: string };
}

export default function StockTransferShow({ record, can, urls }: Props) {
    const [rollback, setRollback] = useState(false);

    return (
        <AppLayout title={`Stock Transfer ${record.number}`}>
            <PageHeader
                title="Stock Transfer Details"
                description="Full transfer information, item movement, and source to destination details."
                actions={
                    <>
                        <Button variant="outline" asChild>
                            <Link href={urls.index}>
                                <ArrowLeft />
                                Back
                            </Link>
                        </Button>
                        {can.create && (
                            <Button variant="outline" asChild>
                                <Link href={urls.create}>
                                    <Plus />
                                    New transfer
                                </Link>
                            </Button>
                        )}
                        {can.rollback && (
                            <Button variant="destructive" onClick={() => setRollback(true)}>
                                <Undo2 />
                                Rollback transfer
                            </Button>
                        )}
                    </>
                }
            />

            {record.rollback && (
                <Alert>
                    <Undo2 />
                    <AlertTitle>This stock transfer has been rolled back.</AlertTitle>
                    <AlertDescription>
                        <p>
                            Rolled back by <strong>{record.rollback.by}</strong> · {record.rollback.at}
                        </p>
                        {record.rollback.reason && <p>Reason: {record.rollback.reason}</p>}
                    </AlertDescription>
                </Alert>
            )}

            <div className="grid gap-4 sm:grid-cols-3">
                <StatCard label="Transfer number" value={<span className="text-primary">{record.number}</span>} />
                <StatCard label="Transfer date" value={<span className="text-lg">{record.date}</span>} />
                <StatCard label="Total items" value={record.items.length.toLocaleString()} />
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Transfer route</CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <RouteBox label="From location" value={record.from} />
                    <ArrowRight className="size-6 rotate-90 text-muted-foreground sm:rotate-0" />
                    <RouteBox label="To location" value={record.to} />
                </CardContent>
            </Card>

            <div className="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Personnel information</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 sm:grid-cols-3">
                        <Info label="Requested by" value={record.requested_by} />
                        <Info label="Received by" value={record.received_by} />
                        <Info label="Created by" value={record.creator} />
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Remarks / notes</CardTitle>
                    </CardHeader>
                    <CardContent className="text-sm whitespace-pre-line">{record.remarks || <span className="text-muted-foreground">No remarks provided for this transfer.</span>}</CardContent>
                </Card>
            </div>

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-row items-center justify-between border-b py-4">
                    <div className="grid gap-1.5">
                        <CardTitle>Transferred items</CardTitle>
                        <CardDescription>List of all products included in this stock transfer.</CardDescription>
                    </div>
                    <Badge variant="secondary">{record.items.length} item(s)</Badge>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-12 pl-6">#</TableHead>
                                <TableHead>Product</TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Part number</TableHead>
                                <TableHead>Unit</TableHead>
                                <TableHead className="text-center">Transferred qty</TableHead>
                                <TableHead className="pr-6 text-center">Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {record.items.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-10">
                                        <div className="flex flex-col items-center gap-1 text-center text-muted-foreground">
                                            <PackageOpen className="size-8" />
                                            There are no products recorded in this transfer yet.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {record.items.map((item, index) => (
                                <TableRow key={item.id}>
                                    <TableCell className="pl-6 text-muted-foreground">{index + 1}</TableCell>
                                    <TableCell className="font-medium">{item.name}</TableCell>
                                    <TableCell>{item.category ?? <span className="text-muted-foreground">—</span>}</TableCell>
                                    <TableCell>{item.part_number}</TableCell>
                                    <TableCell>{item.unit}</TableCell>
                                    <TableCell className="text-center">
                                        <Badge variant="secondary" className="tabular-nums">
                                            {item.qty.toLocaleString()}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="pr-6 text-center">
                                        <Badge
                                            variant="outline"
                                            className={
                                                item.rolled_back
                                                    ? 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400'
                                                    : 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400'
                                            }
                                        >
                                            {item.rolled_back ? 'Rolled back' : 'Completed'}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <RollbackDialog
                open={rollback}
                onOpenChange={setRollback}
                title="Rollback stock transfer"
                description={
                    <>
                        Quantities will be returned to <strong>{record.from}</strong> and deducted from <strong>{record.to}</strong>.
                    </>
                }
                effect="This will reverse the stock transfer. The transfer and its items will be marked Rolled Back."
                url={urls.rollback}
                method="post"
                defaultReason=""
            />
        </AppLayout>
    );
}

function RouteBox({ label, value }: { label: string; value: string }) {
    return (
        <div className="min-w-48 rounded-lg border px-6 py-4 text-center">
            <div className="text-xs text-muted-foreground uppercase">{label}</div>
            <div className="mt-1 text-lg font-semibold">{value}</div>
        </div>
    );
}

function Info({ label, value }: { label: string; value: ReactNode }) {
    return (
        <div>
            <div className="text-xs text-muted-foreground uppercase">{label}</div>
            <div className="mt-0.5 font-medium">{value}</div>
        </div>
    );
}
