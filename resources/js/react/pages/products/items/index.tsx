import { router, useForm } from '@inertiajs/react';
import { Boxes, Loader2, MoreHorizontal, Package, Pencil, Plus, Save, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent } from 'react';
import { DataPagination } from '@/components/data-pagination';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface Item {
    id: number;
    category_id: string;
    category: string | null;
    product_name: string;
    supplier_name: string | null;
    unit: string | null;
    part_number: string | null;
    details: string | null;
    stock_qty: number;
    update_url: string;
    destroy_url: string;
}

interface Props {
    items: Paginated<Item>;
    stock: Paginated<Item>;
    categories: SearchOption[];
    filters: { search: string; showStock: boolean };
    can: { create: boolean; update: boolean; delete: boolean };
    urls: { index: string; store: string };
}

export default function ItemsIndex({ items, stock, categories, filters, can, urls }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [editing, setEditing] = useState<Item | 'new' | null>(null);
    const [deleting, setDeleting] = useState<Item | null>(null);
    const [stockOpen, setStockOpen] = useState(filters.showStock);
    const first = useRef(true);

    // Live search across both the item list and the stock view.
    useEffect(() => {
        if (first.current) {
            first.current = false;
            return;
        }
        const timer = window.setTimeout(() => router.get(urls.index, search ? { search } : {}, { preserveState: true, replace: true }), 300);

        return () => window.clearTimeout(timer);
    }, [search]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <AppLayout title="Products">
            <PageHeader
                title="Items Management"
                description="Manage all items and assign them under categories."
                actions={
                    <>
                        <Button variant="outline" onClick={() => setStockOpen(true)}>
                            <Boxes />
                            View stock
                        </Button>
                        {can.create && (
                            <Button onClick={() => setEditing('new')}>
                                <Plus />
                                Add item
                            </Button>
                        )}
                    </>
                }
            />

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Items list</CardTitle>
                        <CardDescription>Search by item name, category, supplier, unit, part number, or details.</CardDescription>
                    </div>
                    <div className="relative">
                        <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            aria-label="Search items"
                            placeholder="Search item, category, supplier, part number..."
                            className="w-80 pl-8"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                        />
                    </div>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[760px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Item name</TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Supplier / shop</TableHead>
                                <TableHead className="text-right">Stock</TableHead>
                                <TableHead className="w-20 pr-6 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {items.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-12">
                                        <div className="flex flex-col items-center gap-1 text-center text-muted-foreground">
                                            <Package className="size-8" />
                                            No items found.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {items.data.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell className="max-w-80 pl-6">
                                        <div className="font-medium">
                                            {item.product_name}
                                            {item.unit && <span className="text-muted-foreground"> ({item.unit})</span>}
                                        </div>
                                        <div className="truncate text-xs text-muted-foreground">{item.details || 'N/A'}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="font-medium">{item.category ?? 'N/A'}</div>
                                        {item.part_number && <div className="text-xs text-muted-foreground">#{item.part_number}</div>}
                                    </TableCell>
                                    <TableCell>{item.supplier_name || 'N/A'}</TableCell>
                                    <TableCell className="text-right">
                                        <StockBadge qty={item.stock_qty} />
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        {(can.update || can.delete) && (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="size-8" aria-label={`Actions for ${item.product_name}`}>
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    {can.update && (
                                                        <DropdownMenuItem onSelect={() => setEditing(item)}>
                                                            <Pencil />
                                                            Edit
                                                        </DropdownMenuItem>
                                                    )}
                                                    {can.delete && (
                                                        <DropdownMenuItem variant="destructive" onSelect={() => setDeleting(item)}>
                                                            <Trash2 />
                                                            Delete
                                                        </DropdownMenuItem>
                                                    )}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={items} noun="item" />

            {editing && <ItemDialog item={editing === 'new' ? null : editing} categories={categories} storeUrl={urls.store} onClose={() => setEditing(null)} />}

            <Dialog open={stockOpen} onOpenChange={setStockOpen}>
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-4xl">
                    <DialogHeader>
                        <DialogTitle>Inventory dashboard</DialogTitle>
                        <DialogDescription>Total stock per item across all locations{filters.search ? ` · matching "${filters.search}"` : ''}.</DialogDescription>
                    </DialogHeader>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Category</TableHead>
                                <TableHead>Product</TableHead>
                                <TableHead className="text-center">Unit</TableHead>
                                <TableHead className="text-center">Status</TableHead>
                                <TableHead className="text-center">Qty</TableHead>
                                <TableHead>Indicator</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {stock.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-6 text-center text-muted-foreground">
                                        No stock items found.
                                    </TableCell>
                                </TableRow>
                            )}
                            {stock.data.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell>{item.category ?? '—'}</TableCell>
                                    <TableCell>
                                        <div className="font-medium">{item.product_name}</div>
                                        <div className="text-xs text-muted-foreground">{item.details || 'N/A'}</div>
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <Badge variant="secondary">{item.unit || '—'}</Badge>
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <StockBadge qty={item.stock_qty} label />
                                    </TableCell>
                                    <TableCell className="text-center font-semibold tabular-nums">{item.stock_qty}</TableCell>
                                    <TableCell className="w-32">
                                        <div className="h-2 overflow-hidden rounded-full bg-muted">
                                            <div
                                                className={cn('h-full rounded-full', item.stock_qty <= 0 ? 'bg-red-500' : item.stock_qty <= 5 ? 'bg-amber-500' : 'bg-emerald-500')}
                                                style={{ width: `${item.stock_qty <= 0 ? 0 : Math.min(100, item.stock_qty * 10)}%` }}
                                            />
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    {stock.last_page > 1 && (
                        <div className="flex items-center justify-end gap-2 text-sm">
                            <Button
                                variant="outline"
                                size="sm"
                                disabled={!stock.prev_page_url}
                                onClick={() => stock.prev_page_url && router.get(stock.prev_page_url, { stock: 1 }, { preserveState: true, preserveScroll: true })}
                            >
                                Previous
                            </Button>
                            <span className="text-muted-foreground">
                                Page {stock.current_page} of {stock.last_page}
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                disabled={!stock.next_page_url}
                                onClick={() => stock.next_page_url && router.get(stock.next_page_url, { stock: 1 }, { preserveState: true, preserveScroll: true })}
                            >
                                Next
                            </Button>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            <AlertDialog open={!!deleting} onOpenChange={(open) => !open && setDeleting(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete {deleting?.product_name}?</AlertDialogTitle>
                        <AlertDialogDescription>This item and its stock records will be permanently deleted. This action cannot be undone.</AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            className="bg-destructive text-white hover:bg-destructive/90"
                            onClick={() => deleting && router.delete(deleting.destroy_url, { preserveScroll: true })}
                        >
                            Yes, delete it
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}

function StockBadge({ qty, label }: { qty: number; label?: boolean }) {
    if (qty <= 0) {
        return <Badge variant="outline" className="border-red-300 text-red-700 dark:border-red-800 dark:text-red-400">{label ? 'Out of stock' : '0'}</Badge>;
    }
    if (qty <= 5) {
        return <Badge variant="outline" className="border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400">{label ? 'Low' : qty}</Badge>;
    }

    return <Badge variant="outline" className="border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400">{label ? 'Available' : qty}</Badge>;
}

function ItemDialog({ item, categories, storeUrl, onClose }: { item: Item | null; categories: SearchOption[]; storeUrl: string; onClose: () => void }) {
    const form = useForm({
        category_id: item?.category_id ?? '',
        product_name: item?.product_name ?? '',
        supplier_name: item?.supplier_name ?? '',
        unit: item?.unit ?? '',
        part_number: item?.part_number ?? '',
        details: item?.details ?? '',
    });
    const errors = form.errors as Record<string, string>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(item ? item.update_url : storeUrl, { preserveScroll: true, onSuccess: onClose });
    };

    const text = (key: 'product_name' | 'supplier_name' | 'unit' | 'part_number', label: string, required = false) => (
        <FormField id={`item-${key}`} label={label} required={required} error={errors[key]}>
            <Input id={`item-${key}`} required={required} value={form.data[key]} onChange={(event) => form.setData(key, event.target.value)} />
        </FormField>
    );

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="sm:max-w-xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{item ? 'Edit item' : 'Add item'}</DialogTitle>
                    </DialogHeader>
                    <FormField id="item-category" label="Category" required error={errors.category_id}>
                        <SearchSelect
                            id="item-category"
                            options={categories}
                            value={form.data.category_id}
                            onChange={(value) => form.setData('category_id', value)}
                            placeholder="Select category"
                            searchPlaceholder="Search category..."
                            invalid={!!errors.category_id}
                        />
                    </FormField>
                    {text('product_name', 'Item name', true)}
                    {text('supplier_name', 'Supplier / shop name')}
                    <div className="grid gap-4 sm:grid-cols-2">
                        {text('unit', 'Unit')}
                        {text('part_number', 'Part number')}
                    </div>
                    <FormField id="item-details" label="Details" error={errors.details}>
                        <Textarea id="item-details" rows={3} value={form.data.details} onChange={(event) => form.setData('details', event.target.value)} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing || !form.data.category_id}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            Save
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
