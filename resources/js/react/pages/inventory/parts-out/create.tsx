import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save, ShieldCheck } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { linesAreValid, newLine, ProductLineItems, type LineItem } from '@/components/inventory/product-line-items';
import { PageHeader } from '@/components/page-header';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';

interface Props {
    vehicles: SearchOption[];
    locations: SearchOption[];
    today: string;
    urls: { index: string; store: string; search: string };
}

export default function PartsOutCreate({ vehicles, locations, today, urls }: Props) {
    const [lines, setLines] = useState<LineItem[]>([newLine()]);
    const form = useForm({
        vehicle_id: '',
        location_id: locations.length === 1 ? locations[0].value : '',
        mechanic_name: '',
        issued_date: today,
        requested_by: '',
        job_order_no: '',
        odometer: '',
        purpose: '',
        remarks: '',
    });
    const errors = form.errors as Record<string, string>;
    const valid = linesAreValid(lines, -1);

    const changeLocation = (location_id: string) => {
        form.setData('location_id', location_id);
        // Stock differs per garage, so picked products must be chosen again.
        setLines([newLine()]);
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            product_id: lines.map((line) => line.product?.id),
            qty_used: lines.map((line) => line.qty),
            item_remarks: lines.map((line) => line.remarks),
        }));
        form.post(urls.store);
    };

    const text = (key: 'mechanic_name' | 'requested_by' | 'job_order_no' | 'odometer', label: string, placeholder: string, required = false) => (
        <FormField id={key} label={label} required={required} error={errors[key]}>
            <Input id={key} required={required} placeholder={placeholder} value={form.data[key]} aria-invalid={!!errors[key]} onChange={(event) => form.setData(key, event.target.value)} />
        </FormField>
    );

    return (
        <AppLayout title="New Parts Out">
            <form onSubmit={submit} className="grid gap-4">
                <PageHeader
                    title="New Parts Out"
                    description="Issue vehicle parts, deduct stock from garage inventory, and record mechanic and job order details."
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
                        <CardTitle>Transaction information</CardTitle>
                        <CardDescription>Vehicle, source garage, mechanic and job order details.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <FormField id="vehicle_id" label="Vehicle" error={errors.vehicle_id} className="xl:col-span-2">
                            <SearchSelect
                                id="vehicle_id"
                                options={vehicles}
                                value={form.data.vehicle_id}
                                onChange={(value) => form.setData('vehicle_id', value)}
                                placeholder="Select vehicle"
                                searchPlaceholder="Search plate, body no., name..."
                            />
                        </FormField>
                        <FormField id="location_id" label="Source garage / location" required error={errors.location_id} className="xl:col-span-2">
                            <Select value={form.data.location_id} onValueChange={changeLocation}>
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
                        {text('mechanic_name', 'Mechanic', 'Mechanic name', true)}
                        <FormField id="issued_date" label="Date issued" required error={errors.issued_date}>
                            <Input id="issued_date" type="date" required value={form.data.issued_date} onChange={(event) => form.setData('issued_date', event.target.value)} />
                        </FormField>
                        {text('requested_by', 'Requested by', 'Requester name')}
                        {text('job_order_no', 'Job order no.', 'JO number')}
                        {text('odometer', 'Odometer', 'Odometer')}
                        <FormField id="purpose" label="Purpose / work details" error={errors.purpose} className="md:col-span-2 xl:col-span-3">
                            <Textarea id="purpose" rows={3} placeholder="Describe the work done" value={form.data.purpose} onChange={(event) => form.setData('purpose', event.target.value)} />
                        </FormField>
                        <FormField id="remarks" label="Remarks" error={errors.remarks} className="md:col-span-2 xl:col-span-4">
                            <Textarea id="remarks" rows={2} value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} />
                        </FormField>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Parts / items used</CardTitle>
                        <CardDescription>Search available products from the selected garage and encode quantity used.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-2">
                        {(errors.product_id || errors.qty_used) && <p className="text-sm text-destructive">{errors.product_id ?? errors.qty_used}</p>}
                        <ProductLineItems
                            lines={lines}
                            onChange={setLines}
                            stockEffect={-1}
                            withRemarks
                            qtyLabel="Qty used"
                            qtyKey="qty_used"
                            errors={errors}
                            disabledReason={form.data.location_id ? undefined : 'Select source garage / location first.'}
                            searchUrl={(query, exclude) =>
                                form.data.location_id
                                    ? `${urls.search}?search=${encodeURIComponent(query)}&location_id=${form.data.location_id}&exclude_ids=${exclude.join(',')}`
                                    : null
                            }
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className="flex items-center gap-2 text-sm text-muted-foreground">
                            <ShieldCheck className="size-4" />
                            Saving this record will deduct stock from the selected garage. Review stock after values before submitting.
                        </p>
                        <div className="flex gap-2">
                            <Button type="button" variant="outline" asChild>
                                <Link href={urls.index}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={form.processing || !valid || !form.data.location_id || !form.data.mechanic_name}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                Save parts out
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
