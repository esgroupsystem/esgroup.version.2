import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save } from 'lucide-react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';

interface Option {
    value: string;
    label: string;
}

interface BusData {
    id: number;
    bus_no: string;
    plate_no: string;
    company: string;
    garage: string;
    chassis_number: string;
    engine_number: string;
    case_number: string;
    operational_status: string;
    sale_status: string;
    monitoring_remarks: string;
    updated_at: string | null;
}

interface Props {
    bus: BusData | null;
    options: { operational_statuses: Option[]; sale_statuses: Option[]; garages: string[]; companies: string[] };
    urls: { submit: string; back: string };
}

export default function FleetBusForm({ bus, options, urls }: Props) {
    // Older rows can hold a condition that is no longer an option; make the user pick instead of silently changing it.
    const legacyCondition = bus && !options.operational_statuses.some((option) => option.value === bus.operational_status) ? bus.operational_status : null;
    const form = useForm({
        bus_no: bus?.bus_no ?? '',
        plate_no: bus?.plate_no ?? '',
        company: bus?.company ?? '',
        garage: bus?.garage ?? '',
        chassis_number: bus?.chassis_number ?? '',
        engine_number: bus?.engine_number ?? '',
        case_number: bus?.case_number ?? '',
        operational_status: legacyCondition !== null ? '' : (bus?.operational_status ?? options.operational_statuses[0]?.value ?? 'active'),
        sale_status: bus?.sale_status ?? 'not_for_sale',
        monitoring_remarks: bus?.monitoring_remarks ?? '',
    });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (bus) form.put(urls.submit);
        else form.post(urls.submit);
    };

    const text = (name: keyof typeof form.data, label: string, props: { required?: boolean; list?: string; placeholder?: string; mono?: boolean } = {}) => (
        <FormField id={`bus-${name}`} label={label} required={props.required} error={errors[name]}>
            <Input
                id={`bus-${name}`}
                required={props.required}
                list={props.list}
                placeholder={props.placeholder}
                className={props.mono ? 'font-mono uppercase' : 'uppercase'}
                value={form.data[name]}
                onChange={(event) => form.setData(name, event.target.value)}
                aria-invalid={!!errors[name]}
            />
        </FormField>
    );

    return (
        <AppLayout title={bus ? `Update bus ${bus.bus_no}` : 'Add bus'}>
            <PageHeader
                title={bus ? `Update bus ${bus.bus_no}` : 'Add bus unit'}
                description={bus ? `Last updated ${bus.updated_at ?? '—'}. Text fields are saved in uppercase.` : 'Register a unit in the bus master list. Text fields are saved in uppercase.'}
                actions={
                    <Button variant="outline" asChild>
                        <Link href={urls.back}>
                            <ArrowLeft />
                            Back to fleet
                        </Link>
                    </Button>
                }
            />

            <form onSubmit={submit} className="grid gap-4 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Unit details</CardTitle>
                        <CardDescription>Identification and assignment of the unit.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 sm:grid-cols-2">
                        {text('bus_no', 'Bus no.', { required: true, placeholder: 'e.g. 1024' })}
                        {text('plate_no', 'Plate no.', { placeholder: 'e.g. ABC 1234' })}
                        {text('company', 'Company', { list: 'bus-company-options' })}
                        {text('garage', 'Garage', { list: 'bus-garage-options' })}
                        {text('chassis_number', 'Chassis number', { mono: true })}
                        {text('engine_number', 'Engine number', { mono: true })}
                        {text('case_number', 'Case number', { mono: true })}
                        <datalist id="bus-company-options">
                            {options.companies.map((company) => (
                                <option key={company} value={company} />
                            ))}
                        </datalist>
                        <datalist id="bus-garage-options">
                            {options.garages.map((garage) => (
                                <option key={garage} value={garage} />
                            ))}
                        </datalist>
                        <FormField id="bus-remarks" label="Monitoring remarks" error={errors.monitoring_remarks} className="sm:col-span-2">
                            <Textarea id="bus-remarks" rows={4} maxLength={1000} value={form.data.monitoring_remarks} onChange={(event) => form.setData('monitoring_remarks', event.target.value)} />
                        </FormField>
                    </CardContent>
                </Card>

                <Card className="content-start">
                    <CardHeader>
                        <CardTitle>Status</CardTitle>
                        <CardDescription>Condition and sale status drive the fleet summaries.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        <FormField
                            id="bus-condition"
                            label="Condition"
                            required
                            error={errors.operational_status}
                            hint={legacyCondition ? `Saved value "${legacyCondition}" is not a current option. Choose one.` : undefined}
                        >
                            <Select value={form.data.operational_status} onValueChange={(value) => form.setData('operational_status', value)}>
                                <SelectTrigger id="bus-condition" className="w-full" aria-invalid={!!errors.operational_status || !!legacyCondition}>
                                    <SelectValue placeholder="Select condition" />
                                </SelectTrigger>
                                <SelectContent>
                                    {options.operational_statuses.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                        <FormField id="bus-sale" label="Sale status" required error={errors.sale_status}>
                            <Select value={form.data.sale_status} onValueChange={(value) => form.setData('sale_status', value)}>
                                <SelectTrigger id="bus-sale" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {options.sale_statuses.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                    </CardContent>
                    <CardFooter className="justify-end gap-2">
                        <Button type="button" variant="outline" asChild>
                            <Link href={urls.back}>Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            {bus ? 'Update bus' : 'Save bus'}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </AppLayout>
    );
}
