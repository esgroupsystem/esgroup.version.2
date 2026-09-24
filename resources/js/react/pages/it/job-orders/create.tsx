import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save } from 'lucide-react';
import type { FormEvent } from 'react';
import { FileDrop } from '@/components/file-drop';
import { FormField } from '@/components/form-field';
import { BusSeatMap } from '@/components/it/bus-seat-map';
import { useModal } from '@/components/modal/modal-context';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';

interface Props {
    buses: SearchOption[];
    issueTypes: string[];
    reporter: string;
    /** Old seat plan image; the animated seat map replaces it. */
    seatLayout?: string;
    urls: { index: string; store: string };
}

interface JobOrderForm {
    bus_detail_id: string;
    job_datestart: string;
    job_type: string;
    job_time_start: string;
    job_time_end: string;
    driver_name: string;
    conductor_name: string;
    job_sitNumber: string;
    direction: string;
    job_remarks: string;
    files: File[];
}

/** The endpoint expects the legacy flatpickr "d/m/y" date. */
const toLegacyDate = (iso: string) => {
    const [year, month, day] = iso.split('-');

    return iso ? `${day}/${month}/${year.slice(-2)}` : '';
};

export default definePage<Props>({
    title: () => 'Create Job Order',
    description: ({ reporter }) => `Reported by ${reporter}. New tickets start as Pending and move through IT approval.`,
    actions: (props) => <BackLink {...props} />,
    size: 'xl',
    Content: CreateJobOrder,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.index}>
                <ArrowLeft />
                Exit
            </Link>
        </Button>
    );
}

function CreateJobOrder({ buses, issueTypes, urls }: Props) {
    const modal = useModal();
    const form = useForm<JobOrderForm>({
        bus_detail_id: '',
        job_datestart: '',
        job_type: issueTypes[0] ?? '',
        job_time_start: '',
        job_time_end: '',
        driver_name: '',
        conductor_name: '',
        job_sitNumber: '',
        direction: 'South Bound',
        job_remarks: '',
        files: [],
    });
    const errors = form.errors as Record<string, string>;
    const fileError = errors.files ?? Object.entries(errors).find(([key]) => key.startsWith('files.'))?.[1];

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({ ...data, job_datestart: toLegacyDate(data.job_datestart) }));
        form.post(urls.store, modal.visit({ forceFormData: true }));
    };

    const set = (key: Exclude<keyof JobOrderForm, 'files'>) => (value: string) => form.setData(key, value);

    return (
        <form onSubmit={submit} className="grid gap-4">
            <Card>
                <CardHeader>
                    <CardTitle>Seat</CardTitle>
                    <CardDescription>Click the seats involved in the incident. Seat 1 is the driver. You can pick more than one.</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-3">
                    <BusSeatMap value={form.data.job_sitNumber} onChange={set('job_sitNumber')} />
                    <FormField id="job_sitNumber" label="Seat number(s)" error={errors.job_sitNumber} hint="Filled by the seat map. You can also type numbers separated by commas." className="max-w-sm">
                        <Input
                            id="job_sitNumber"
                            inputMode="numeric"
                            placeholder="e.g. 12, 13"
                            value={form.data.job_sitNumber}
                            aria-invalid={!!errors.job_sitNumber}
                            onChange={(event) => form.setData('job_sitNumber', event.target.value)}
                        />
                    </FormField>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Incident</CardTitle>
                </CardHeader>
                <CardContent className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <FormField id="bus_detail_id" label="Bus number" required error={errors.bus_detail_id} className="md:col-span-2">
                        <SearchSelect
                            id="bus_detail_id"
                            options={buses}
                            value={form.data.bus_detail_id}
                            onChange={set('bus_detail_id')}
                            placeholder="Select bus..."
                            searchPlaceholder="Search body no., plate, name, garage..."
                            empty="No bus found."
                            invalid={!!errors.bus_detail_id}
                        />
                    </FormField>
                    <FormField id="job_type" label="Issue type" required error={errors.job_type}>
                        <Select value={form.data.job_type} onValueChange={set('job_type')}>
                            <SelectTrigger id="job_type" className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {issueTypes.map((type) => (
                                    <SelectItem key={type} value={type}>
                                        {type}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </FormField>
                    <FormField id="direction" label="Direction" error={errors.direction}>
                        <Select value={form.data.direction} onValueChange={set('direction')}>
                            <SelectTrigger id="direction" className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="South Bound">South Bound</SelectItem>
                                <SelectItem value="North Bound">North Bound</SelectItem>
                            </SelectContent>
                        </Select>
                    </FormField>
                    <FormField id="job_datestart" label="Incident date" required error={errors.job_datestart}>
                        <Input id="job_datestart" type="date" required value={form.data.job_datestart} aria-invalid={!!errors.job_datestart} onChange={(event) => form.setData('job_datestart', event.target.value)} />
                    </FormField>
                    <FormField id="job_time_start" label="Time start" required error={errors.job_time_start}>
                        <Input id="job_time_start" type="time" required value={form.data.job_time_start} aria-invalid={!!errors.job_time_start} onChange={(event) => form.setData('job_time_start', event.target.value)} />
                    </FormField>
                    <FormField id="job_time_end" label="Time end" required error={errors.job_time_end}>
                        <Input id="job_time_end" type="time" required value={form.data.job_time_end} aria-invalid={!!errors.job_time_end} onChange={(event) => form.setData('job_time_end', event.target.value)} />
                    </FormField>
                    <div className="hidden xl:block" />
                    <FormField id="driver_name" label="Driver name" error={errors.driver_name} className="md:col-span-1 xl:col-span-2">
                        <Input id="driver_name" value={form.data.driver_name} onChange={(event) => form.setData('driver_name', event.target.value)} />
                    </FormField>
                    <FormField id="conductor_name" label="Conductor name" error={errors.conductor_name} className="md:col-span-1 xl:col-span-2">
                        <Input id="conductor_name" value={form.data.conductor_name} onChange={(event) => form.setData('conductor_name', event.target.value)} />
                    </FormField>
                    <FormField id="job_remarks" label="Description / remarks" error={errors.job_remarks} className="md:col-span-2 xl:col-span-4">
                        <Textarea id="job_remarks" rows={4} value={form.data.job_remarks} onChange={(event) => form.setData('job_remarks', event.target.value)} />
                    </FormField>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Photos and files</CardTitle>
                    <CardDescription>Images, videos, PDFs, Office files, text or zip.</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-1.5">
                    <FileDrop files={form.data.files} onChange={(files) => form.setData('files', files)} invalid={!!fileError} />
                    {fileError && <p className="text-xs text-destructive">{fileError}</p>}
                    {form.progress && (
                        <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                            <div className="h-full bg-primary transition-all" style={{ width: `${form.progress.percentage ?? 0}%` }} />
                        </div>
                    )}
                </CardContent>
            </Card>

            <div className="flex justify-end gap-2">
                {modal.inModal ? (
                    <Button type="button" variant="outline" onClick={modal.close}>
                        Cancel
                    </Button>
                ) : (
                    <Button type="button" variant="outline" asChild>
                        <Link href={urls.index}>Cancel</Link>
                    </Button>
                )}
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                    Save job order
                </Button>
            </div>
        </form>
    );
}
