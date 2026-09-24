import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Loader2, Save } from 'lucide-react';
import { useState, type FormEvent } from 'react';
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

export default function StockTransferCreate({ locations, today, urls }: Props) {
    const [lines, setLines] = useState<LineItem[]>([newLine()]);
    const form = useForm({ from_location_id: '', to_location_id: '', transfer_date: today, requested_by: '', received_by: '', remarks: '' });
    const errors = form.errors as Record<string, string>;
    const sameLocation = !!form.data.from_location_id && form.data.from_location_id === form.data.to_location_id;
    const valid = linesAreValid(lines, -1) && !!form.data.from_location_id && !!form.data.to_location_id && !sameLocation;

    const location = (key: 'from_location_id' | 'to_location_id', label: string) => (
        <FormField id={key} label={label} required error={errors[key] ?? (key === 'to_location_id' && sameLocation ? 'Destination must be different from the source.' : undefined)}>
            <Select
                value={form.data[key]}
                onValueChange={(value) => {
                    form.setData(key, value);
                    // Available stock depends on the source garage.
                    if (key === 'from_location_id') setLines([newLine()]);
                }}
            >
                <SelectTrigger id={key} className="w-full" aria-invalid={!!errors[key] || (key === 'to_location_id' && sameLocation)}>
                    <SelectValue placeholder="Select location" />
                </SelectTrigger>
                <SelectContent>
                    {locations.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </FormField>
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, product_id: lines.map((line) => line.product?.id), qty: lines.map((line) => line.qty) }));
        form.post(urls.store);
    };

    return (
        <AppLayout title="New Stock Transfer">
            <form onSubmit={submit} className="grid gap-4">
                <PageHeader
                    title="New Stock Transfer"
                    description="Move available stock from one garage to another."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={urls.index}>
                                <ArrowLeft />
                                Back
                            </Link>
                        </Button>
                    }
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Transfer information</CardTitle>
                        <CardDescription>Source and destination garage, date and personnel.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-[1fr_auto_1fr] md:items-start">
                        {location('from_location_id', 'From location')}
                        <ArrowRight className="mx-auto mt-8 hidden size-5 text-muted-foreground md:block" />
                        {location('to_location_id', 'To location')}
                        <div className="grid gap-4 sm:grid-cols-3 md:col-span-3">
                            <FormField id="transfer_date" label="Transfer date" required error={errors.transfer_date}>
                                <Input id="transfer_date" type="date" required value={form.data.transfer_date} onChange={(event) => form.setData('transfer_date', event.target.value)} />
                            </FormField>
                            <FormField id="requested_by" label="Requested by" error={errors.requested_by}>
                                <Input id="requested_by" value={form.data.requested_by} onChange={(event) => form.setData('requested_by', event.target.value)} />
                            </FormField>
                            <FormField id="received_by" label="Received by" error={errors.received_by}>
                                <Input id="received_by" value={form.data.received_by} onChange={(event) => form.setData('received_by', event.target.value)} />
                            </FormField>
                        </div>
                        <FormField id="remarks" label="Remarks" error={errors.remarks} className="md:col-span-3">
                            <Textarea id="remarks" rows={2} value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} />
                        </FormField>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Items to transfer</CardTitle>
                        <CardDescription>Only products with stock in the source location are listed.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-2">
                        {(errors.product_id || errors.qty) && <p className="text-sm text-destructive">{errors.product_id ?? errors.qty}</p>}
                        <ProductLineItems
                            lines={lines}
                            onChange={setLines}
                            stockEffect={-1}
                            stockLabel="Source stock"
                            qtyLabel="Qty"
                            qtyKey="qty"
                            errors={errors}
                            disabledReason={form.data.from_location_id ? undefined : 'Select the source location first.'}
                            searchUrl={(query, exclude) =>
                                form.data.from_location_id
                                    ? `${urls.search}?q=${encodeURIComponent(query)}&from_location_id=${form.data.from_location_id}${exclude.map((id) => `&exclude_ids[]=${id}`).join('')}`
                                    : null
                            }
                        />
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" asChild>
                        <Link href={urls.index}>Cancel</Link>
                    </Button>
                    <Button type="submit" disabled={form.processing || !valid}>
                        {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                        Save transfer
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
