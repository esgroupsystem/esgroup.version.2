import { Link } from '@inertiajs/react';
import { ArrowLeft, Undo2 } from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { inventoryStatusClass, RollbackDialog } from '@/components/inventory/rollback-dialog';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';

interface Item {
    id: number;
    product_id: number;
    name: string;
    supplier: string;
    unit: string;
    part_number: string;
    qty_used: number;
    stock_before: number;
    stock_after: number;
    remarks: string;
}

interface Props {
    record: {
        id: number;
        number: string;
        status: { key: string; label: string };
        date: string;
        mechanic: string;
        vehicle: { plate_number: string; detail: string } | null;
        location: string;
        creator: string;
        requested_by: string;
        job_order_no: string;
        odometer: string;
        purpose: string;
        remarks: string;
        items: Item[];
        total_qty: number;
    };
    can: { rollback: boolean };
    urls: { index: string; rollback: string };
}

export default function PartsOutShow({ record, can, urls }: Props) {
    const [rollback, setRollback] = useState(false);

    return (
        <AppLayout title={`Parts Out ${record.number}`}>
            <PageHeader
                title={record.number}
                description="Issued / installed parts: vehicle usage, mechanic details, items used, stock before and stock after."
                actions={
                    <>
                        {can.rollback && (
                            <Button variant="destructive" onClick={() => setRollback(true)}>
                                <Undo2 />
                                Rollback transaction
                            </Button>
                        )}
                        <Button variant="outline" asChild>
                            <Link href={urls.index}>
                                <ArrowLeft />
                                Back to records
                            </Link>
                        </Button>
                    </>
                }
            />

            <Card>
                <CardHeader>
                    <CardTitle>Transaction information</CardTitle>
                    <CardDescription>Main parts out reference and maintenance details.</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Info label="Parts out no." value={record.number} />
                    <Info label="Date issued" value={record.date} />
                    <Info label="Mechanic" value={record.mechanic} />
                    <Info
                        label="Status"
                        value={
                            <Badge variant="outline" className={inventoryStatusClass(record.status.key)}>
                                {record.status.label}
                            </Badge>
                        }
                    />
                    <Info
                        label="Vehicle"
                        value={
                            record.vehicle ? (
                                <>
                                    <div>{record.vehicle.plate_number}</div>
                                    <div className="text-xs font-normal text-muted-foreground">{record.vehicle.detail}</div>
                                </>
                            ) : (
                                <span className="font-normal text-muted-foreground">No vehicle selected</span>
                            )
                        }
                    />
                    <Info label="Source garage" value={record.location} />
                    <Info label="Encoded by" value={record.creator} />
                    <Info label="Requested by" value={record.requested_by} />
                    <Info label="Job order no." value={record.job_order_no} />
                    <Info label="Odometer" value={record.odometer} />
                    <Info label="Purpose / work details" value={record.purpose} wide />
                    <Info label="Remarks" value={record.remarks} wide />
                </CardContent>
            </Card>

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-row items-center justify-between border-b py-4">
                    <div className="grid gap-1.5">
                        <CardTitle>Items used</CardTitle>
                        <CardDescription>Stock before and after are recorded for audit tracking.</CardDescription>
                    </div>
                    <Badge variant="secondary">
                        {record.items.length} item{record.items.length === 1 ? '' : 's'}
                    </Badge>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[900px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Product</TableHead>
                                <TableHead>Supplier</TableHead>
                                <TableHead>Unit</TableHead>
                                <TableHead>Part no.</TableHead>
                                <TableHead className="text-center">Qty used</TableHead>
                                <TableHead className="text-center">Stock before</TableHead>
                                <TableHead className="text-center">Stock after</TableHead>
                                <TableHead className="pr-6">Remarks</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {record.items.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={8} className="py-10 text-center text-muted-foreground">
                                        This parts out record has no encoded items.
                                    </TableCell>
                                </TableRow>
                            )}
                            {record.items.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell className="pl-6">
                                        <div className="font-medium">{item.name}</div>
                                        <div className="text-xs text-muted-foreground">Product ID: {item.product_id}</div>
                                    </TableCell>
                                    <TableCell>{item.supplier}</TableCell>
                                    <TableCell>{item.unit}</TableCell>
                                    <TableCell>{item.part_number}</TableCell>
                                    <TableCell className="text-center font-semibold tabular-nums">{item.qty_used.toLocaleString()}</TableCell>
                                    <TableCell className="text-center tabular-nums">{item.stock_before.toLocaleString()}</TableCell>
                                    <TableCell className="text-center tabular-nums">{item.stock_after.toLocaleString()}</TableCell>
                                    <TableCell className="pr-6">{item.remarks}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                        <TableFooter>
                            <TableRow>
                                <TableCell colSpan={4} className="pl-6 text-right font-medium">
                                    Total used quantity
                                </TableCell>
                                <TableCell className="text-center font-semibold tabular-nums">{record.total_qty.toLocaleString()}</TableCell>
                                <TableCell colSpan={3} />
                            </TableRow>
                        </TableFooter>
                    </Table>
                </CardContent>
            </Card>

            <RollbackDialog
                open={rollback}
                onOpenChange={setRollback}
                title="Confirm parts out rollback"
                description={`This will return all used quantities to ${record.location}.`}
                effect={`${record.number}: ${record.items.length} item(s), total quantity ${record.total_qty.toLocaleString()}. All item quantities will be added back to stock and the status will become Rolled Back.`}
                url={urls.rollback}
                defaultReason="Rollback from Parts Out details"
            />
        </AppLayout>
    );
}

function Info({ label, value, wide }: { label: string; value: ReactNode; wide?: boolean }) {
    return (
        <div className={wide ? 'sm:col-span-2' : undefined}>
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="mt-0.5 font-medium whitespace-pre-line">{value}</div>
        </div>
    );
}
