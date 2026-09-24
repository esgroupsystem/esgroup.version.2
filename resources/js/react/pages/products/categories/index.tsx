import { router, useForm } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Loader2, MoreHorizontal, Pencil, Plus, Search, Tags, Trash2 } from 'lucide-react';
import { useMemo, useState, type FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
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
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';

interface Category {
    id: number;
    name: string;
    products_count: number;
    update_url: string;
    destroy_url: string;
}

interface Props {
    categories: Category[];
    can: { create: boolean; update: boolean; delete: boolean };
    urls: { store: string };
}

const PER_PAGE = 10;

export default function CategoriesIndex({ categories, can, urls }: Props) {
    const [search, setSearch] = useState('');
    const [page, setPage] = useState(0);
    const [editing, setEditing] = useState<Category | 'new' | null>(null);
    const [deleting, setDeleting] = useState<Category | null>(null);

    const filtered = useMemo(() => {
        const needle = search.trim().toLowerCase();

        return needle ? categories.filter((category) => category.name.toLowerCase().includes(needle)) : categories;
    }, [categories, search]);
    const pages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
    const current = Math.min(page, pages - 1);
    const visible = filtered.slice(current * PER_PAGE, (current + 1) * PER_PAGE);

    return (
        <AppLayout title="Categories">
            <PageHeader
                title="Category Management"
                description="Manage item categories used across the system."
                actions={
                    can.create && (
                        <Button onClick={() => setEditing('new')}>
                            <Plus />
                            Add category
                        </Button>
                    )
                }
            />

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Category list</CardTitle>
                        <CardDescription>{categories.length} categor{categories.length === 1 ? 'y' : 'ies'}</CardDescription>
                    </div>
                    <div className="relative">
                        <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            aria-label="Search category"
                            placeholder="Search category..."
                            className="w-64 pl-8"
                            value={search}
                            onChange={(event) => {
                                setSearch(event.target.value);
                                setPage(0);
                            }}
                        />
                    </div>
                </CardHeader>
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Category name</TableHead>
                                <TableHead className="text-right">Products</TableHead>
                                <TableHead className="w-20 pr-6 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {visible.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={3} className="py-12">
                                        <div className="flex flex-col items-center gap-1 text-center text-muted-foreground">
                                            <Tags className="size-8" />
                                            No categories found.
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {visible.map((category) => (
                                <TableRow key={category.id}>
                                    <TableCell className="pl-6 font-medium">{category.name}</TableCell>
                                    <TableCell className="text-right">
                                        <Badge variant="secondary">{category.products_count}</Badge>
                                    </TableCell>
                                    <TableCell className="pr-6 text-right">
                                        {(can.update || can.delete) && (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="size-8" aria-label={`Actions for ${category.name}`}>
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    {can.update && (
                                                        <DropdownMenuItem onSelect={() => setEditing(category)}>
                                                            <Pencil />
                                                            Edit
                                                        </DropdownMenuItem>
                                                    )}
                                                    {can.delete && (
                                                        <DropdownMenuItem variant="destructive" onSelect={() => setDeleting(category)}>
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

            {pages > 1 && (
                <div className="flex items-center justify-end gap-2 text-sm">
                    <Button variant="outline" size="icon" className="size-8" aria-label="Previous page" disabled={current === 0} onClick={() => setPage(current - 1)}>
                        <ChevronLeft />
                    </Button>
                    <span className="text-muted-foreground">
                        Page {current + 1} of {pages}
                    </span>
                    <Button variant="outline" size="icon" className="size-8" aria-label="Next page" disabled={current >= pages - 1} onClick={() => setPage(current + 1)}>
                        <ChevronRight />
                    </Button>
                </div>
            )}

            {editing && <CategoryDialog category={editing === 'new' ? null : editing} storeUrl={urls.store} onClose={() => setEditing(null)} />}

            <AlertDialog open={!!deleting} onOpenChange={(open) => !open && setDeleting(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete {deleting?.name}?</AlertDialogTitle>
                        <AlertDialogDescription>
                            {deleting && deleting.products_count > 0
                                ? `This also permanently deletes the ${deleting.products_count} product(s) in this category and their stock records.`
                                : 'This category will be permanently deleted.'}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            className="bg-destructive text-white hover:bg-destructive/90"
                            onClick={() => deleting && router.delete(deleting.destroy_url, { preserveScroll: true })}
                        >
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}

function CategoryDialog({ category, storeUrl, onClose }: { category: Category | null; storeUrl: string; onClose: () => void }) {
    const form = useForm({ name: category?.name ?? '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(category ? category.update_url : storeUrl, { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{category ? 'Edit category' : 'Add category'}</DialogTitle>
                    </DialogHeader>
                    <FormField id="category-name" label="Category name" required error={form.errors.name}>
                        <Input id="category-name" required autoFocus value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Close
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Save
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
