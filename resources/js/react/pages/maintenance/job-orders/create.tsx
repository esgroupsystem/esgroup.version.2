import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save, TriangleAlert } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { FormField } from '@/components/form-field';
import { cleanMechanics, JobStatusBadge, RepairDetailsFields, RepairTypeBadge, type Option } from '@/components/maintenance/job-order-parts';
import { PageHeader } from '@/components/page-header';
import { SearchSelect } from '@/components/search-select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';

interface BusOption {
    value: string;
    label: string;
    hint: string;
    bus_no: string;
    plate_no: string;
    garage: string;
    status: string | null;
    last_odometer: number | null;
}

interface Props {
    buses: BusOption[];
    repairTypes: Option[];
    urls: { index: string; store: string };
}

export default function JobOrderCreate({ buses, repairTypes, urls }: Props) {
    const form = useForm({
        job_order_no: '',
        bus_id: '',
        full_name: '',
        description_of_work: '',
        odometer_reading: '',
        mechanic_names: [''],
        repair_types: [] as string[],
    });
    const errors = form.errors as Record<string, string>;
    const bus = buses.find((option) => option.value === form.data.bus_id);
    const odometer = form.data.odometer_reading === '' ? null : Number(form.data.odometer_reading);
    const lower = bus?.last_odometer != null && odometer !== null && odometer < bus.last_odometer;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, mechanic_names: cleanMechanics(data.mechanic_names) }));
        form.post(urls.store);
    };

    return (
        <AppLayout title="Create Maintenance Job Order">
            <form onSubmit={submit} className="grid gap-4">
                <PageHeader
                    title="Create Maintenance Job Order"
                    description="Record a bus repair request. New job orders start as Standby."
                    actions={
                        <Button variant="outline" asChild>
                            <Link href={urls.index}>
                                <ArrowLeft />
                                Back
                            </Link>
                        </Button>
                    }
                />

                <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
                    <div className="grid min-w-0 content-start gap-4">
                        <Section n={1} title="Job order number" description="Leave blank to auto-generate, or encode your own JO-NO.">
                            <FormField id="job_order_no" label="JO-NO" error={errors.job_order_no} hint="Letters, numbers, dash, and slash only.">
                                <Input id="job_order_no" maxLength={50} placeholder="Example: JO-2026-0001" value={form.data.job_order_no} onChange={(event) => form.setData('job_order_no', event.target.value)} />
                            </FormField>
                        </Section>

                        <Section n={2} title="Bus assignment" description="Select the unit under repair.">
                            <FormField id="bus_id" label="Bus" required error={errors.bus_id}>
                                <SearchSelect
                                    id="bus_id"
                                    options={buses}
                                    value={form.data.bus_id}
                                    onChange={(value) => form.setData('bus_id', value)}
                                    placeholder="Select bus"
                                    searchPlaceholder="Search bus no., plate, company, garage..."
                                    invalid={!!errors.bus_id}
                                />
                            </FormField>
                            {bus && (
                                <div className="grid gap-3 rounded-lg border bg-muted/40 p-3 text-sm sm:grid-cols-4">
                                    <Mini label="Bus no." value={bus.bus_no} />
                                    <Mini label="Plate no." value={bus.plate_no} />
                                    <Mini label="Garage" value={bus.garage} />
                                    <Mini label="Bus status" value={bus.status ?? '—'} />
                                </div>
                            )}
                        </Section>

                        <Section n={3} title="Work request" description="Who requested the job and what needs to be done.">
                            <FormField id="full_name" label="Full name" error={errors.full_name}>
                                <Input id="full_name" placeholder="Requester / staff name" value={form.data.full_name} onChange={(event) => form.setData('full_name', event.target.value)} />
                            </FormField>
                            <FormField id="description_of_work" label="Description of work" required error={errors.description_of_work} hint="At least 5 characters.">
                                <Textarea
                                    id="description_of_work"
                                    rows={6}
                                    required
                                    value={form.data.description_of_work}
                                    aria-invalid={!!errors.description_of_work}
                                    onChange={(event) => form.setData('description_of_work', event.target.value)}
                                />
                            </FormField>
                        </Section>

                        <Section n={4} title="Repair assignment" description="Optional now; required later when setting the job to Operational.">
                            <RepairDetailsFields
                                mechanics={form.data.mechanic_names}
                                onMechanicsChange={(names) => form.setData('mechanic_names', names)}
                                repairTypes={form.data.repair_types}
                                onRepairTypesChange={(types) => form.setData('repair_types', types)}
                                options={repairTypes}
                                errors={errors}
                            />
                        </Section>

                        <Section n={5} title="Odometer reading" description="Optional, but useful for maintenance interval tracking.">
                            <FormField
                                id="odometer_reading"
                                label="Current odometer (km)"
                                error={errors.odometer_reading}
                                hint={bus ? (bus.last_odometer != null ? `Last recorded reading: ${bus.last_odometer.toLocaleString()} km` : 'No previous odometer reading for this bus.') : 'Select a bus to preview odometer comparison.'}
                            >
                                <Input id="odometer_reading" type="number" min={0} max={9999999} placeholder="Optional current odometer reading" value={form.data.odometer_reading} onChange={(event) => form.setData('odometer_reading', event.target.value)} />
                            </FormField>
                            {lower && (
                                <Alert variant="destructive">
                                    <TriangleAlert />
                                    <AlertDescription>Warning: current reading is lower than the last recorded reading ({bus?.last_odometer?.toLocaleString()} km).</AlertDescription>
                                </Alert>
                            )}
                        </Section>

                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="outline" asChild>
                                <Link href={urls.index}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={form.processing || !form.data.bus_id || form.data.description_of_work.trim().length < 5}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                Save job order
                            </Button>
                        </div>
                    </div>

                    <Card className="content-start xl:sticky xl:top-20">
                        <CardHeader>
                            <CardTitle>Live preview</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3 text-sm">
                            <Row label="Initial status" value={<JobStatusBadge value="standby" label="Standby" />} />
                            <Row label="Job order no." value={form.data.job_order_no || 'Auto-generated'} />
                            <Row label="Bus no." value={bus?.bus_no ?? 'Not selected'} />
                            <Row label="Plate no." value={bus?.plate_no ?? '—'} />
                            <Row label="Requester" value={form.data.full_name || 'Not specified'} />
                            <Row label="Mechanic(s)" value={cleanMechanics(form.data.mechanic_names).join(', ') || 'Not assigned'} />
                            <Row
                                label="Repair type(s)"
                                value={
                                    form.data.repair_types.length ? (
                                        <span className="flex flex-wrap justify-end gap-1">
                                            {form.data.repair_types.map((value) => (
                                                <RepairTypeBadge key={value} value={value} label={repairTypes.find((type) => type.value === value)?.label ?? value} />
                                            ))}
                                        </span>
                                    ) : (
                                        'Not selected'
                                    )
                                }
                            />
                            <Row label="Odometer" value={odometer !== null ? `${odometer.toLocaleString()} km` : 'Not encoded'} />
                            <div className="border-t pt-3">
                                <div className="text-xs text-muted-foreground">Description of work</div>
                                <p className="mt-1 whitespace-pre-line">{form.data.description_of_work || <span className="text-muted-foreground">No work description encoded yet.</span>}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </AppLayout>
    );
}

function Section({ n, title, description, children }: { n: number; title: string; description: string; children: ReactNode }) {
    return (
        <Card>
            <CardHeader className="flex flex-row items-start gap-3">
                <span className="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">{n}</span>
                <div className="grid gap-1">
                    <CardTitle>{title}</CardTitle>
                    <CardDescription>{description}</CardDescription>
                </div>
            </CardHeader>
            <CardContent className="grid gap-4">{children}</CardContent>
        </Card>
    );
}

function Mini({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="font-medium">{value}</div>
        </div>
    );
}

function Row({ label, value }: { label: string; value: ReactNode }) {
    return (
        <div className="flex items-start justify-between gap-3">
            <span className="text-muted-foreground">{label}</span>
            <span className="text-right font-medium">{value}</span>
        </div>
    );
}
