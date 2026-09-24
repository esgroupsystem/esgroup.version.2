import { router, useForm } from '@inertiajs/react';
import { Briefcase, Building2, Loader2, Plus, Trash2, X } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { StatCard } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { definePage } from '@/lib/define-page';

interface Position {
    id: number;
    title: string;
    destroy_url: string;
}

interface Department {
    id: number;
    name: string;
    positions: Position[];
    destroy_url: string;
}

interface Props {
    departments: Department[];
    can: { create: boolean; delete: boolean };
    urls: { store: string; storePosition: string };
}

export default definePage<Props>({
    title: () => 'Departments & Positions',
    description: () => 'Departments and the job titles under each one. Search finds a department by its name or by any of its positions.',
    actions: (props) => <Actions {...props} />,
    size: 'lg',
    Content: DepartmentsIndex,
});

function Actions({ can, urls }: Props) {
    const [adding, setAdding] = useState(false);
    if (!can.create) return null;

    return (
        <>
            <Button onClick={() => setAdding(true)}>
                <Plus />
                Add department
            </Button>
            <AddDepartment open={adding} onOpenChange={setAdding} url={urls.store} />
        </>
    );
}

function DepartmentsIndex({ departments, can, urls }: Props) {
    const modal = useModal();
    const positionCount = departments.reduce((sum, department) => sum + department.positions.length, 0);
    const empty = departments.filter((department) => department.positions.length === 0).length;

    const columns: DataTableColumn<Department>[] = [
        {
            key: 'name',
            header: 'Department',
            className: 'align-top',
            // Search covers the positions too.
            value: (department) => `${department.name} ${department.positions.map((position) => position.title).join(' ')}`,
            cell: (department) => (
                <div className="flex items-center gap-3">
                    <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted">
                        <Building2 className="size-4 text-muted-foreground" />
                    </div>
                    <div>
                        <div className="font-medium">{department.name}</div>
                        <div className="text-xs text-muted-foreground">
                            {department.positions.length} position{department.positions.length === 1 ? '' : 's'}
                        </div>
                    </div>
                </div>
            ),
        },
        {
            key: 'positions',
            header: 'Positions',
            className: 'align-top whitespace-normal',
            cell: (department) => (
                <div className="grid gap-2">
                    <div className="flex flex-wrap items-center gap-1.5">
                        {department.positions.length === 0 && <span className="text-sm text-amber-700 dark:text-amber-400">No positions yet</span>}
                        {department.positions.map((position) => (
                            <Badge key={position.id} variant="secondary" className="gap-1 pr-1">
                                {position.title}
                                {can.delete && (
                                    <ConfirmAction
                                        title="Remove position?"
                                        description={`"${position.title}" will be removed from ${department.name}.`}
                                        confirmLabel="Remove"
                                        destructive
                                        onConfirm={() => router.delete(position.destroy_url, modal.visit({ preserveScroll: true, preserveState: true }))}
                                        trigger={
                                            <button type="button" className="rounded-full p-0.5 hover:bg-destructive/15 hover:text-destructive" aria-label={`Remove ${position.title}`}>
                                                <X className="size-3" />
                                            </button>
                                        }
                                    />
                                )}
                            </Badge>
                        ))}
                    </div>
                    {can.create && <AddPosition departmentId={department.id} url={urls.storePosition} />}
                </div>
            ),
        },
    ];

    return (
        <>
            <div className="grid grid-cols-3 gap-3">
                <StatCard label="Departments" value={departments.length.toLocaleString()} />
                <StatCard label="Positions" value={positionCount.toLocaleString()} />
                <StatCard label="Without positions" value={empty.toLocaleString()} />
            </div>

            <DataTable
                title="Departments"
                noun="department"
                rows={departments}
                columns={columns}
                rowKey={(department) => department.id}
                searchPlaceholder="Search department or position..."
                emptyText="No departments found."
                minWidth={560}
                rowActions={
                    can.delete
                        ? (department) => (
                              <ConfirmAction
                                  title="Delete this department?"
                                  description={`${department.name} and its positions will be deleted.`}
                                  confirmLabel="Delete"
                                  destructive
                                  onConfirm={() => router.delete(department.destroy_url, modal.visit({ preserveScroll: true, preserveState: true }))}
                                  trigger={
                                      <IconButton label={`Delete ${department.name}`}>
                                          <Trash2 className="text-destructive" />
                                      </IconButton>
                                  }
                              />
                          )
                        : undefined
                }
            />
        </>
    );
}

function AddPosition({ departmentId, url }: { departmentId: number; url: string }) {
    const modal = useModal();
    const form = useForm({ department_id: departmentId, title: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(url, modal.visit({ preserveScroll: true, preserveState: true, onSuccess: () => form.reset('title') }));
    };

    return (
        <form onSubmit={submit} className="flex max-w-sm items-start gap-2">
            <div className="grid flex-1 gap-1">
                <div className="relative">
                    <Briefcase className="pointer-events-none absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        aria-label="New position title"
                        placeholder="Add position..."
                        className="h-8 pl-8"
                        value={form.data.title}
                        aria-invalid={!!form.errors.title}
                        onChange={(event) => form.setData('title', event.target.value)}
                    />
                </div>
                {form.errors.title && <p className="text-xs text-destructive">{form.errors.title}</p>}
            </div>
            <Button type="submit" size="sm" variant="outline" disabled={!form.data.title.trim() || form.processing}>
                {form.processing ? <Loader2 className="animate-spin" /> : <Plus />}
                Add
            </Button>
        </form>
    );
}

function AddDepartment({ open, onOpenChange, url }: { open: boolean; onOpenChange: (open: boolean) => void; url: string }) {
    const modal = useModal();
    const form = useForm({ name: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(
            url,
            modal.visit({
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            }),
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Add department</DialogTitle>
                        <DialogDescription>Positions are added from the department's row after saving.</DialogDescription>
                    </DialogHeader>
                    <FormField id="department-name" label="Department name" required error={form.errors.name}>
                        <Input id="department-name" required value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            Cancel
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
