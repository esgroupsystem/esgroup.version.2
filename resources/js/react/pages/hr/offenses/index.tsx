import { useForm } from '@inertiajs/react';
import { Loader2, Plus, Save } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import type { Paginated } from '@/types';

interface Offense {
    id: number;
    section: string;
    offense_description: string;
    offense_type: string;
    offense_gravity: string;
}

interface Props {
    offenses: Paginated<Offense>;
    filters: { search: string; type: string; gravity: string };
    types: string[];
    gravities: string[];
    can: { create: boolean };
    urls: { index: string; store: string };
}

const GRAVITY_TONE: Record<string, string> = {
    CAPITAL: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    GRAVE: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    SEVERE: 'border-orange-300 text-orange-700 dark:border-orange-800 dark:text-orange-400',
    SERIOUS: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
};

const toOptions = (values: string[]) => values.map((value) => ({ value, label: value }));

export default definePage<Props>({
    title: () => 'HR Offense Management',
    description: () => 'Company offense policies used when recording violations on an employee profile.',
    actions: (props) => <Actions {...props} />,
    size: 'lg',
    Content: OffensesIndex,
});

function Actions({ can, types, gravities, urls }: Props) {
    const [open, setOpen] = useState(false);
    if (!can.create) return null;

    return (
        <>
            <Button onClick={() => setOpen(true)}>
                <Plus />
                New offense
            </Button>
            <CreateOffense open={open} onOpenChange={setOpen} types={types} gravities={gravities} url={urls.store} />
        </>
    );
}

function OffensesIndex({ offenses, filters, types, gravities, urls }: Props) {
    const columns: DataTableColumn<Offense>[] = [
        { key: 'section', header: 'Section', className: 'font-medium whitespace-nowrap', cell: (offense) => offense.section },
        {
            key: 'description',
            header: 'Description',
            cell: (offense) => <div className="min-w-64 whitespace-normal">{offense.offense_description}</div>,
        },
        {
            key: 'type',
            header: 'Type',
            cell: (offense) => <Badge variant="secondary">{offense.offense_type}</Badge>,
            filter: { type: 'select', param: 'type', options: toOptions(types), placeholder: 'All types' },
        },
        {
            key: 'gravity',
            header: 'Gravity',
            cell: (offense) => (
                <Badge variant="outline" className={GRAVITY_TONE[offense.offense_gravity]}>
                    {offense.offense_gravity}
                </Badge>
            ),
            filter: { type: 'select', param: 'gravity', options: toOptions(gravities), placeholder: 'All gravity' },
        },
    ];

    return (
        <DataTable
            title="Offense list"
            noun="offense"
            paginator={offenses}
            url={urls.index}
            filters={filters}
            columns={columns}
            rowKey={(offense) => offense.id}
            searchPlaceholder="Search section or description..."
            emptyText="No offenses found."
            minWidth={560}
        />
    );
}

function CreateOffense({ open, onOpenChange, types, gravities, url }: { open: boolean; onOpenChange: (open: boolean) => void; types: string[]; gravities: string[]; url: string }) {
    const modal = useModal();
    const form = useForm({ section: '', offense_type: '', offense_gravity: '', offense_description: '' });

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

    const choice = (key: 'offense_type' | 'offense_gravity', options: string[], placeholder: string) => (
        <Select value={form.data[key]} onValueChange={(value) => form.setData(key, value)}>
            <SelectTrigger id={key} className="w-full" aria-invalid={!!form.errors[key]}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                {options.map((option) => (
                    <SelectItem key={option} value={option}>
                        {option}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <Dialog open={open} onOpenChange={(next) => !form.processing && onOpenChange(next)}>
            <DialogContent className="sm:max-w-xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>New offense</DialogTitle>
                        <DialogDescription>Fill out the details below then save.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 sm:grid-cols-3">
                        <FormField id="section" label="Section" required error={form.errors.section}>
                            <Input id="section" required value={form.data.section} onChange={(event) => form.setData('section', event.target.value)} />
                        </FormField>
                        <FormField id="offense_type" label="Offense type" required error={form.errors.offense_type}>
                            {choice('offense_type', types, 'Select type')}
                        </FormField>
                        <FormField id="offense_gravity" label="Offense gravity" required error={form.errors.offense_gravity}>
                            {choice('offense_gravity', gravities, 'Select gravity')}
                        </FormField>
                        <FormField id="offense_description" label="Offense description" required error={form.errors.offense_description} className="sm:col-span-3">
                            <Textarea id="offense_description" rows={4} required value={form.data.offense_description} onChange={(event) => form.setData('offense_description', event.target.value)} />
                        </FormField>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            Save offense
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
