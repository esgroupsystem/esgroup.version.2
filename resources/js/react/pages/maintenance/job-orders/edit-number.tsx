import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save } from 'lucide-react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { JobStatusBadge } from '@/components/maintenance/job-order-parts';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';

interface Props {
    jobOrder: { job_order_no: string; bus_no: string; plate_no: string; requester: string; status: { value: string | null; label: string } };
    urls: { show: string; update: string };
}

export default function EditJobOrderNumber({ jobOrder, urls }: Props) {
    const form = useForm({ job_order_no: jobOrder.job_order_no, remarks: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.patch(urls.update);
    };

    return (
        <AppLayout title="Edit Job Order Number">
            <PageHeader
                title="Edit job order number"
                description="Correct the JO-NO. The change is recorded in the job order update history."
                actions={
                    <Button variant="outline" asChild>
                        <Link href={urls.show}>
                            <ArrowLeft />
                            Back to details
                        </Link>
                    </Button>
                }
            />
            <div className="grid gap-4 lg:grid-cols-[minmax(0,32rem)_18rem]">
                <Card>
                    <CardContent>
                        <form onSubmit={submit} className="grid gap-4">
                            <FormField id="job_order_no" label="JO-NO" required error={form.errors.job_order_no} hint="Letters, numbers, dash, and slash only.">
                                <Input id="job_order_no" required maxLength={50} value={form.data.job_order_no} onChange={(event) => form.setData('job_order_no', event.target.value)} />
                            </FormField>
                            <FormField id="remarks" label="Remarks" error={form.errors.remarks} hint="Optional reason for the change.">
                                <Textarea id="remarks" rows={3} maxLength={1000} value={form.data.remarks} onChange={(event) => form.setData('remarks', event.target.value)} />
                            </FormField>
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" asChild>
                                    <Link href={urls.show}>Cancel</Link>
                                </Button>
                                <Button type="submit" disabled={form.processing || !form.data.job_order_no.trim()}>
                                    {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                    Save JO-NO
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
                <Card className="content-start">
                    <CardHeader>
                        <CardTitle>Current record</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2 text-sm">
                        <div className="flex justify-between gap-2">
                            <span className="text-muted-foreground">Current JO-NO</span>
                            <span className="font-medium">{jobOrder.job_order_no}</span>
                        </div>
                        <div className="flex justify-between gap-2">
                            <span className="text-muted-foreground">Bus</span>
                            <span className="font-medium">
                                {jobOrder.bus_no} · {jobOrder.plate_no}
                            </span>
                        </div>
                        <div className="flex justify-between gap-2">
                            <span className="text-muted-foreground">Requester</span>
                            <span className="font-medium">{jobOrder.requester}</span>
                        </div>
                        <div className="flex justify-between gap-2">
                            <span className="text-muted-foreground">Status</span>
                            <JobStatusBadge value={jobOrder.status.value} label={jobOrder.status.label} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
