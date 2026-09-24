import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, Loader2, Save, TriangleAlert } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { FormField } from '@/components/form-field';
import { cleanMechanics, JobStatusBadge, RepairDetailsFields, type Option } from '@/components/maintenance/job-order-parts';
import { PageHeader } from '@/components/page-header';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';

interface Props {
    jobOrder: {
        job_order_no: string;
        bus_no: string;
        plate_no: string;
        requester: string;
        work: string;
        status: { value: string | null; label: string; description: string };
    };
    values: { status: string; mechanic_names: string[]; repair_types: string[]; remarks: string };
    statuses: Option[];
    repairTypes: Option[];
    urls: { show: string; update: string };
}

export default function EditJobOrderStatus({ jobOrder, values, statuses, repairTypes, urls }: Props) {
    const form = useForm(values);
    const errors = form.errors as Record<string, string>;
    const operational = form.data.status === 'operational';
    const missingCompletion = operational && (cleanMechanics(form.data.mechanic_names).length === 0 || form.data.repair_types.length === 0);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, mechanic_names: cleanMechanics(data.mechanic_names) }));
        form.patch(urls.update);
    };

    return (
        <AppLayout title="Update Maintenance Status">
            <PageHeader
                title="Update maintenance status"
                description={
                    <>
                        Change the repair state for <strong className="text-foreground">{jobOrder.job_order_no}</strong>.
                    </>
                }
                actions={
                    <Button variant="outline" asChild>
                        <Link href={urls.show}>
                            <ArrowLeft />
                            Back to details
                        </Link>
                    </Button>
                }
            />

            <form onSubmit={submit} className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <div className="grid min-w-0 content-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Select repair status</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Repair status">
                            {statuses.map((status) => {
                                const selected = form.data.status === status.value;

                                return (
                                    <button
                                        key={status.value}
                                        type="button"
                                        role="radio"
                                        aria-checked={selected}
                                        onClick={() => form.setData('status', status.value)}
                                        className={cn('grid gap-2 rounded-lg border p-4 text-left transition-colors hover:border-primary/60', selected && 'border-primary bg-accent/50 ring-2 ring-primary/20')}
                                    >
                                        <div className="flex items-center justify-between gap-2">
                                            <JobStatusBadge value={status.value} label={status.label} />
                                            {selected && <Check className="size-4 text-primary" />}
                                        </div>
                                        <div className="font-medium">{status.label}</div>
                                        <p className="text-xs text-muted-foreground">{status.description}</p>
                                    </button>
                                );
                            })}
                            {errors.status && <p className="text-xs text-destructive sm:col-span-2">{errors.status}</p>}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Repair completion details</CardTitle>
                            <CardDescription>Required when setting the status to Operational.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            {operational && (
                                <Alert>
                                    <TriangleAlert />
                                    <AlertDescription>Mechanic name and at least one repair type are required when setting the status to Operational.</AlertDescription>
                                </Alert>
                            )}
                            <RepairDetailsFields
                                mechanics={form.data.mechanic_names}
                                onMechanicsChange={(names) => form.setData('mechanic_names', names)}
                                repairTypes={form.data.repair_types}
                                onRepairTypesChange={(types) => form.setData('repair_types', types)}
                                options={repairTypes}
                                errors={errors}
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Status remarks</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <FormField id="remarks" label="Remarks" error={errors.remarks} hint="Optional. This note will appear in the job order update history.">
                                <Textarea id="remarks" rows={4} maxLength={1000} placeholder="Example: Unit is waiting for brake lining parts from supplier." value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} />
                            </FormField>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="outline" asChild>
                            <Link href={urls.show}>Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={form.processing || !form.data.status || missingCompletion}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            Save status
                        </Button>
                    </div>
                </div>

                <Card className="content-start">
                    <CardHeader>
                        <CardTitle>Job order summary</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 text-sm">
                        <Info label="Job order no." value={jobOrder.job_order_no} />
                        <Info label="Bus no." value={jobOrder.bus_no} />
                        <Info label="Plate no." value={jobOrder.plate_no} />
                        <Info label="Current status" value={<JobStatusBadge value={jobOrder.status.value} label={jobOrder.status.label} />} />
                        <Info label="Requester" value={jobOrder.requester} />
                        <Info label="Work description" value={<span className="font-normal whitespace-pre-line">{jobOrder.work}</span>} />
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}

function Info({ label, value }: { label: string; value: ReactNode }) {
    return (
        <div>
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="mt-0.5 font-medium">{value}</div>
        </div>
    );
}
