import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { ConfirmAction } from '@/components/confirm-action';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { SearchSelect } from '@/components/search-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';

interface Option {
    value: string;
    label: string;
}

interface BusOption extends Option {
    bus_no: string;
    plate_no: string;
    company: string;
    garage: string;
}

interface RecordData {
    id: number;
    bus_id: string;
    bus_no: string;
    plate_no: string;
    company: string;
    garage: string;
    status: string;
    storage_area: string;
    breakdown_start_date: string;
    breakdown_end_date: string;
    column_11: string;
    unit_location: string;
    progress: string;
    remarks: string;
    days: number;
    destroy_url: string;
}

interface Props {
    record: RecordData | null;
    buses: BusOption[];
    statuses: Option[];
    can: { delete: boolean };
    urls: { submit: string; index: string };
}

const MANUAL = '__manual';

export default function ForSaleForm({ record, buses, statuses, can, urls }: Props) {
    const form = useForm({
        bus_id: record?.bus_id ?? '',
        bus_no: record?.bus_no ?? '',
        plate_no: record?.plate_no ?? '',
        company: record?.company ?? '',
        garage: record?.garage ?? '',
        status: record?.status ?? statuses[0]?.value ?? 'active',
        storage_area: record?.storage_area ?? '',
        breakdown_start_date: record?.breakdown_start_date ?? '',
        breakdown_end_date: record?.breakdown_end_date ?? '',
        column_11: record?.column_11 ?? '',
        unit_location: record?.unit_location ?? '',
        progress: record?.progress ?? '',
        remarks: record?.remarks ?? '',
    });
    const errors = form.errors as Record<string, string | undefined>;

    // Same behaviour as the old form: picking a bus fills its details; typing a bus number unlinks it.
    const pickBus = (value: string) => {
        if (value === MANUAL) {
            form.setData('bus_id', '');
            return;
        }
        const bus = buses.find((option) => option.value === value);
        if (!bus) return;
        form.setData((current) => ({ ...current, bus_id: bus.value, bus_no: bus.bus_no, plate_no: bus.plate_no, company: bus.company, garage: bus.garage }));
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (record) form.put(urls.submit, { preserveScroll: true });
        else form.post(urls.submit);
    };

    const text = (name: 'bus_no' | 'plate_no' | 'company' | 'garage' | 'storage_area' | 'unit_location' | 'progress' | 'column_11', label: string, required = false) => (
        <FormField id={`fs-${name}`} label={label} required={required} error={errors[name]}>
            <Input
                id={`fs-${name}`}
                required={required}
                value={form.data[name]}
                aria-invalid={!!errors[name]}
                onChange={(event) =>
                    name === 'bus_no'
                        ? form.setData((current) => ({ ...current, bus_no: event.target.value, bus_id: '' }))
                        : form.setData(name, event.target.value)
                }
            />
        </FormField>
    );

    return (
        <AppLayout title={record ? `For sale unit ${record.bus_no}` : 'Add for-sale unit'}>
            <PageHeader
                title={record ? `Update for-sale unit ${record.bus_no}` : 'Add for-sale unit'}
                description="Saving syncs the unit's condition and sale status to bus monitoring."
                actions={
                    <>
                        <Button variant="outline" asChild>
                            <Link href={urls.index}>
                                <ArrowLeft />
                                Back to list
                            </Link>
                        </Button>
                        {record && can.delete && (
                            <ConfirmAction
                                title={`Delete ${record.bus_no}?`}
                                description="This for-sale unit will be removed and the bus returns to Not For Sale in bus monitoring."
                                confirmLabel="Delete"
                                destructive
                                onConfirm={() => router.delete(record.destroy_url)}
                                trigger={
                                    <Button variant="outline" className="text-destructive">
                                        <Trash2 />
                                        Delete
                                    </Button>
                                }
                            />
                        )}
                    </>
                }
            />

            <form onSubmit={submit} className="grid gap-4 lg:grid-cols-3">
                <div className="grid content-start gap-4 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Unit identification</CardTitle>
                            <CardDescription>Select the exact bus record. This prevents wrong syncing when bus numbers are duplicated.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <FormField id="fs-bus" label="Select existing bus" error={errors.bus_id} className="sm:col-span-2" hint="Or leave on manual entry and type a new bus number.">
                                <SearchSelect
                                    id="fs-bus"
                                    ariaLabel="Select existing bus"
                                    options={[{ value: MANUAL, label: 'Manual entry / new bus' }, ...buses]}
                                    value={form.data.bus_id || MANUAL}
                                    onChange={pickBus}
                                    searchPlaceholder="Search bus no., plate, company, garage..."
                                    invalid={!!errors.bus_id}
                                />
                            </FormField>
                            {text('bus_no', 'Bus number', true)}
                            {text('plate_no', 'Plate number')}
                            {text('company', 'Company')}
                            {text('garage', 'Garage')}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Breakdown and location</CardTitle>
                            <CardDescription>Days in breakdown count from the start date until the end date (or today).</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <FormField id="fs-start" label="Breakdown start" error={errors.breakdown_start_date}>
                                <Input id="fs-start" type="date" value={form.data.breakdown_start_date} onChange={(event) => form.setData('breakdown_start_date', event.target.value)} />
                            </FormField>
                            <FormField id="fs-end" label="Breakdown end" error={errors.breakdown_end_date}>
                                <Input id="fs-end" type="date" value={form.data.breakdown_end_date} onChange={(event) => form.setData('breakdown_end_date', event.target.value)} />
                            </FormField>
                            {text('storage_area', 'Storage area')}
                            {text('unit_location', 'Unit location')}
                            {text('progress', 'Progress')}
                            {text('column_11', 'Column 11')}
                            <FormField id="fs-remarks" label="Remarks" error={errors.remarks} className="sm:col-span-2">
                                <Textarea id="fs-remarks" rows={4} maxLength={5000} value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} />
                            </FormField>
                        </CardContent>
                    </Card>
                </div>

                <Card className="content-start">
                    <CardHeader>
                        <CardTitle>Status</CardTitle>
                        <CardDescription>Condition of the unit while it is for sale.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        <FormField id="fs-status" label="Status" required error={errors.status}>
                            <Select value={form.data.status} onValueChange={(value) => form.setData('status', value)}>
                                <SelectTrigger id="fs-status" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {statuses.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                        {record && (
                            <div className="rounded-lg border p-3">
                                <p className="text-xs text-muted-foreground">Days in breakdown</p>
                                <p className="text-2xl font-semibold tabular-nums">{record.days.toLocaleString()}</p>
                            </div>
                        )}
                        <Button type="submit" disabled={form.processing} className="w-full">
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            {record ? 'Update unit' : 'Save unit'}
                        </Button>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
