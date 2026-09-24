import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { useModal } from '@/components/modal/modal-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';

interface Values {
    item_name: string;
    category: string;
    unit: string;
    brand: string;
    model: string;
    part_number: string;
    location: string;
    stock_qty: string;
    minimum_stock: string;
    is_active: boolean;
    description: string;
}

interface Props {
    item: { id: number; item_name: string } | null;
    values: Values;
    categories: string[];
    urls: { index: string; submit: string };
}

type TextKey = Exclude<keyof Values, 'is_active'>;

export default definePage<Props>({
    title: ({ item }) => (item ? 'Edit inventory item' : 'Add inventory item'),
    description: ({ item }) => (item ? item.item_name : 'Add an IT part, accessory, device, or supply.'),
    actions: (props) => <BackLink {...props} />,
    size: 'lg',
    Content: ItInventoryForm,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.index}>
                <ArrowLeft />
                Back to inventory
            </Link>
        </Button>
    );
}

function ItInventoryForm({ item, values, categories, urls }: Props) {
    const form = useForm<Values>(values);
    const errors = form.errors as Partial<Record<keyof Values, string>>;
    const modal = useModal();

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = modal.visit({ preserveScroll: true });
        if (item) {
            form.put(urls.submit, options);
        } else {
            form.post(urls.submit, options);
        }
    };

    const text = (key: TextKey, label: string, props: { required?: boolean; type?: string; placeholder?: string; min?: number; list?: string } = {}) => (
        <Field id={key} label={label} required={props.required} error={errors[key]}>
            <Input id={key} value={form.data[key]} aria-invalid={!!errors[key]} onChange={(event) => form.setData(key, event.target.value)} {...props} />
        </Field>
    );

    return (
        <>
            <form onSubmit={submit} className="grid gap-4 md:grid-cols-[minmax(0,1fr)_16rem]">
                <Card>
                    <CardHeader>
                        <CardTitle>Item details</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <div className="md:col-span-2">{text('item_name', 'Item name', { required: true })}</div>
                        {text('category', 'Category', { placeholder: 'CCTV / Network / Printer', list: 'inventory-categories' })}
                        <datalist id="inventory-categories">
                            {categories.map((category) => (
                                <option key={category} value={category} />
                            ))}
                        </datalist>
                        {text('unit', 'Unit', { required: true })}
                        {text('brand', 'Brand')}
                        {text('model', 'Model')}
                        {text('part_number', 'Part number')}
                        {text('location', 'Location', { placeholder: 'IT Room' })}
                        <div className="md:col-span-2">
                            <Field id="description" label="Description" error={errors.description}>
                                <Textarea id="description" rows={3} value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} />
                            </Field>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid content-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Stock</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            {text('stock_qty', 'Stock qty', { required: true, type: 'number', min: 0 })}
                            {text('minimum_stock', 'Minimum stock', { type: 'number', min: 0 })}
                            <label htmlFor="is_active" className="flex cursor-pointer items-center justify-between gap-3 rounded-lg border p-3">
                                <span className="text-sm font-medium">Active item</span>
                                <Switch id="is_active" checked={form.data.is_active} onCheckedChange={(checked) => form.setData('is_active', checked)} />
                            </label>
                        </CardContent>
                    </Card>
                    <Button type="submit" disabled={form.processing}>
                        {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                        {item ? 'Save changes' : 'Save item'}
                    </Button>
                </div>
            </form>
        </>
    );
}

function Field({ id, label, required, error, className, children }: { id: string; label: string; required?: boolean; error?: string; className?: string; children: ReactNode }) {
    return (
        <div className={cn('grid content-start gap-1.5', className)}>
            <Label htmlFor={id}>
                {label}
                {required && <span className="text-destructive">*</span>}
            </Label>
            {children}
            {error && <p className="text-xs text-destructive">{error}</p>}
        </div>
    );
}
