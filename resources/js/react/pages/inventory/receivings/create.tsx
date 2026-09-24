import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save, ShieldCheck } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { linesAreValid, newLine, ProductLineItems, type LineItem } from '@/components/inventory/product-line-items';
import { PageHeader } from '@/components/page-header';
import type { SearchOption } from '@/components/search-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';

interface Props {
    locations: SearchOption[];
    today: string;
    urls: { index: string; store: string; search: string };
}

export default function ReceivingCreate({ locations, today, urls }: Props) {
    const [lines, setLines] = useState<LineItem[]>([newLine()]);
    const [preview, setPreview] = useState<string | null>(null);
    const form = useForm<{ location_id: string; delivered_by: string; delivery_date: string; remarks: string; proof_image: File | null }>({
        location_id: locations.length === 1 ? locations[0].value : '',
        delivered_by: '',
        delivery_date: today,
        remarks: '',
        proof_image: null,
    });
    const errors = form.errors as Record<string, string>;
    const valid = linesAreValid(lines, 1);

    useEffect(() => () => {
        if (preview) URL.revokeObjectURL(preview);
    }, [preview]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            product_id: lines.map((line) => line.product?.id),
            qty_delivered: lines.map((line) => line.qty),
        }));
        form.post(urls.store, { forceFormData: true });
    };

    return (
        <AppLayout title="New Receiving">
            <form onSubmit={submit} className="grid gap-4">
                <PageHeader
                    title="New Receiving"
                    description="Record delivered products into a garage, add stock, and attach proof of delivery."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={urls.index}>
                                <ArrowLeft />
                                Back to records
                            </Link>
                        </Button>
                    }
                />

                <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Receiving information</CardTitle>
                            <CardDescription>Garage, delivery details and remarks.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 md:grid-cols-3">
                            <FormField id="location_id" label="Garage / location" required error={errors.location_id}>
                                <Select value={form.data.location_id} onValueChange={(value) => form.setData('location_id', value)}>
                                    <SelectTrigger id="location_id" className="w-full" aria-invalid={!!errors.location_id}>
                                        <SelectValue placeholder="Select location" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {locations.map((location) => (
                                            <SelectItem key={location.value} value={location.value}>
                                                {location.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField id="delivered_by" label="Delivered by" required error={errors.delivered_by}>
                                <Input id="delivered_by" required placeholder="Supplier / driver / handler" value={form.data.delivered_by} onChange={(event) => form.setData('delivered_by', event.target.value)} />
                            </FormField>
                            <FormField id="delivery_date" label="Delivery date" required error={errors.delivery_date}>
                                <Input id="delivery_date" type="date" required value={form.data.delivery_date} onChange={(event) => form.setData('delivery_date', event.target.value)} />
                            </FormField>
                            <FormField id="remarks" label="Remarks" error={errors.remarks} className="md:col-span-3">
                                <Textarea id="remarks" rows={3} placeholder="Delivery notes, DR / invoice number, etc." value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} />
                            </FormField>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Proof of delivery</CardTitle>
                            <CardDescription>Optional photo of the DR / receipt (JPG or PNG, up to 2 MB).</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            <FormField id="proof_image" label="Proof image" error={errors.proof_image}>
                                <Input
                                    id="proof_image"
                                    type="file"
                                    accept="image/png,image/jpeg,image/jpg"
                                    onChange={(event) => {
                                        const file = event.target.files?.[0] ?? null;
                                        form.setData('proof_image', file);
                                        setPreview(file ? URL.createObjectURL(file) : null);
                                    }}
                                />
                            </FormField>
                            {preview && <img src={preview} alt="Proof preview" className="max-h-56 rounded-lg border object-contain" />}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Delivered products</CardTitle>
                        <CardDescription>Search products and encode the delivered quantity. Stock shown is the current total.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-2">
                        {(errors.product_id || errors.qty_delivered) && <p className="text-sm text-destructive">{errors.product_id ?? errors.qty_delivered}</p>}
                        <ProductLineItems
                            lines={lines}
                            onChange={setLines}
                            stockEffect={1}
                            stockLabel="Current stock"
                            qtyLabel="Qty delivered"
                            qtyKey="qty_delivered"
                            errors={errors}
                            searchUrl={(query, exclude) => `${urls.search}?search=${encodeURIComponent(query)}&exclude_ids=${exclude.join(',')}`}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className="flex items-center gap-2 text-sm text-muted-foreground">
                            <ShieldCheck className="size-4" />
                            Saving adds the delivered quantities to the selected garage stock.
                        </p>
                        <div className="flex gap-2">
                            <Button type="button" variant="outline" asChild>
                                <Link href={urls.index}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={form.processing || !valid || !form.data.location_id || !form.data.delivered_by}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                Save receiving
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
