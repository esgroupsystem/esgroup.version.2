import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, Loader2, Receipt, Undo2 } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';

interface Item {
    id: number;
    product_id: number;
    name: string;
    details: string;
    delivered: number;
    rolled_back: number;
    remaining: number;
    stock: number;
    rollback_limit: number;
    rollback_url: string;
}

interface Props {
    record: {
        id: number;
        number: string;
        location: string;
        delivered_by: string;
        delivery_date: string;
        receiver: string;
        created: string;
        remarks: string;
        proof_url: string | null;
        items: Item[];
        total_delivered: number;
    };
    can: { rollback: boolean };
    urls: { index: string };
}

export default function ReceivingShow({ record, can, urls }: Props) {
    const [rollback, setRollback] = useState<Item | null>(null);

    return (
        <AppLayout title={`Receiving ${record.number}`}>
            <PageHeader
                title={record.number}
                description="Delivered products: receiving information, delivered items, proof of delivery, and rollback status."
                actions={
                    <Button variant="outline" asChild>
                        <Link href={urls.index}>
                            <ArrowLeft />
                            Back to records
                        </Link>
                    </Button>
                }
            />

            <Card>
                <CardHeader>
                    <CardTitle>Receiving information</CardTitle>
                    <CardDescription>Main delivery reference and encoder details.</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Info label="Receiving no." value={record.number} />
                    <Info label="Garage / location" value={record.location} />
                    <Info label="Delivered by" value={record.delivered_by} />
                    <Info label="Delivery date" value={record.delivery_date} />
                    <Info label="Received by" value={record.receiver} />
                    <Info label="Date created" value={record.created} />
                    <Info label="Remarks" value={record.remarks} wide />
                </CardContent>
            </Card>

            {record.proof_url && (
                <Collapsible className="rounded-xl border bg-card">
                    <CollapsibleTrigger className="flex w-full items-center gap-2 px-6 py-4 text-left font-medium">
                        <Receipt className="size-4 text-primary" />
                        Proof of delivery
                        <span className="ml-auto text-xs text-muted-foreground">Click to show / hide</span>
                    </CollapsibleTrigger>
                    <CollapsibleContent className="border-t px-6 py-4">
                        <a href={record.proof_url} target="_blank" rel="noopener">
                            <img src={record.proof_url} alt="Proof of delivery" className="max-h-96 rounded-lg border shadow-sm" />
                        </a>
                        <p className="mt-2 text-xs text-muted-foreground">Open the image to verify the encoded delivered products.</p>
                    </CollapsibleContent>
                </Collapsible>
            )}

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-row items-center justify-between border-b py-4">
                    <div className="grid gap-1.5">
                        <CardTitle>Delivered product list</CardTitle>
                        <CardDescription>Each row shows delivered, rolled back, remaining balance, and rollback availability.</CardDescription>
                    </div>
                    <Badge variant="secondary">
                        {record.items.length} item{record.items.length === 1 ? '' : 's'}
                    </Badge>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[900px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-12 pl-6">#</TableHead>
                                <TableHead>Product</TableHead>
                                <TableHead>Details</TableHead>
                                <TableHead className="text-center">Delivered</TableHead>
                                {can.rollback && (
                                    <>
                                        <TableHead className="text-center">Rolled back</TableHead>
                                        <TableHead className="text-center">Balance</TableHead>
                                        <TableHead className="text-center">Current stock</TableHead>
                                        <TableHead className="pr-6 text-center">Rollback</TableHead>
                                    </>
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {record.items.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={can.rollback ? 8 : 4} className="py-10 text-center text-muted-foreground">
                                        This receiving record has no encoded products.
                                    </TableCell>
                                </TableRow>
                            )}
                            {record.items.map((item, index) => (
                                <TableRow key={item.id}>
                                    <TableCell className="pl-6 text-muted-foreground">{index + 1}</TableCell>
                                    <TableCell>
                                        <div className="font-medium">{item.name}</div>
                                        <div className="text-xs text-muted-foreground">Product ID: {item.product_id}</div>
                                    </TableCell>
                                    <TableCell className="max-w-64 whitespace-normal text-muted-foreground">{item.details}</TableCell>
                                    <TableCell className="text-center font-semibold tabular-nums">{item.delivered.toLocaleString()}</TableCell>
                                    {can.rollback && (
                                        <>
                                            <TableCell className="text-center tabular-nums">{item.rolled_back.toLocaleString()}</TableCell>
                                            <TableCell className="text-center">
                                                {item.remaining > 0 ? (
                                                    <span className="font-semibold tabular-nums">{item.remaining.toLocaleString()}</span>
                                                ) : (
                                                    <Badge variant="outline" className="border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400">
                                                        Fully rolled back
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-center tabular-nums">{item.stock.toLocaleString()}</TableCell>
                                            <TableCell className="pr-6 text-center">
                                                {item.remaining <= 0 ? (
                                                    <Button variant="outline" size="sm" disabled>
                                                        <Check />
                                                        Done
                                                    </Button>
                                                ) : item.stock <= 0 ? (
                                                    <Badge variant="outline" className="text-muted-foreground">
                                                        No stock
                                                    </Badge>
                                                ) : (
                                                    <Button variant="outline" size="sm" onClick={() => setRollback(item)}>
                                                        <Undo2 />
                                                        Rollback
                                                    </Button>
                                                )}
                                            </TableCell>
                                        </>
                                    )}
                                </TableRow>
                            ))}
                        </TableBody>
                        <TableFooter>
                            <TableRow>
                                <TableCell colSpan={3} className="pl-6 text-right font-medium">
                                    Total delivered quantity
                                </TableCell>
                                <TableCell className="text-center font-semibold tabular-nums">{record.total_delivered.toLocaleString()}</TableCell>
                                {can.rollback && <TableCell colSpan={4} />}
                            </TableRow>
                        </TableFooter>
                    </Table>
                </CardContent>
            </Card>

            {rollback && <ItemRollbackDialog item={rollback} location={record.location} onClose={() => setRollback(null)} />}
        </AppLayout>
    );
}

function ItemRollbackDialog({ item, location, onClose }: { item: Item; location: string; onClose: () => void }) {
    const form = useForm({ rollback_qty: '1' });
    const qty = Number(form.data.rollback_qty);
    const valid = Number.isInteger(qty) && qty >= 1 && qty <= item.rollback_limit;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(item.rollback_url, { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Confirm product rollback</DialogTitle>
                        <DialogDescription>This will deduct stock from {location}.</DialogDescription>
                    </DialogHeader>
                    <div className="rounded-lg border p-3">
                        <div className="text-xs text-muted-foreground uppercase">Product to rollback</div>
                        <div className="font-semibold">{item.name}</div>
                        <div className="text-xs text-muted-foreground">{item.details}</div>
                        <div className="mt-3 grid grid-cols-4 gap-2 text-center text-sm">
                            <Figure label="Delivered" value={item.delivered} />
                            <Figure label="Rolled back" value={item.rolled_back} />
                            <Figure label="Remaining" value={item.remaining} />
                            <Figure label="Location stock" value={item.stock} />
                        </div>
                    </div>
                    <Alert>
                        <AlertDescription>
                            Maximum allowed rollback for this product is <strong>{item.rollback_limit.toLocaleString()}</strong>.
                        </AlertDescription>
                    </Alert>
                    <FormField id="rollback-qty" label="Quantity to rollback" required error={form.errors.rollback_qty} hint="Enter only the quantity that should be deducted from stock.">
                        <Input id="rollback-qty" type="number" min={1} max={item.rollback_limit} required value={form.data.rollback_qty} onChange={(event) => form.setData('rollback_qty', event.target.value)} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>
                            Cancel
                        </Button>
                        <Button type="submit" variant="destructive" disabled={form.processing || !valid}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Undo2 />}
                            Confirm rollback
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function Figure({ label, value }: { label: string; value: number }) {
    return (
        <div>
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="font-semibold tabular-nums">{value.toLocaleString()}</div>
        </div>
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
