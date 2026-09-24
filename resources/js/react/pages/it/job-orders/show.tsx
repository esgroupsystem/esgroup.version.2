import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Check, CheckCircle2, CloudUpload, Download, FileText, Flag, Loader2, Pencil, Plus, Printer, Save, X } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { FileDrop } from '@/components/file-drop';
import { BusSeatMap } from '@/components/it/bus-seat-map';
import { useModal } from '@/components/modal/modal-context';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';
import { ticketStatusClass } from '@/lib/it-status';
import { cn } from '@/lib/utils';

interface Job {
    id: number;
    number: string;
    status: string;
    job_type: string | null;
    creator: string;
    created_at: string | null;
    direction: string | null;
    date_label: string;
    time_start_label: string;
    time_end_label: string;
    seat: number | string | null;
    assigned_to: string | null;
    remarks: string | null;
    driver_name: string | null;
    conductor_name: string | null;
    bus: { name: string | null; body_number: string | null; plate_number: string | null; garage: string | null } | null;
}

interface Values {
    job_type: string;
    job_datestart: string;
    job_time_start: string;
    job_time_end: string;
    direction: string;
    job_sitNumber: string;
    job_remarks: string;
    driver_name: string;
    conductor_name: string;
}

interface JobFile {
    id: number;
    name: string;
    extension: string;
    url: string;
}

interface Note {
    id: number;
    reason: string;
    details: string | null;
    user: string;
    date: string | null;
    time: string | null;
}

interface Log {
    id: number;
    user: string;
    action: string;
    date: string | null;
    time: string | null;
    meta: { key: string; old: string | null; new: string | null; value: string | null }[];
}

interface Props {
    job: Job;
    values: Values;
    files: JobFile[];
    notes: Note[];
    logs: Log[];
    issueTypes: string[];
    noteReasons: string[];
    can: { update: boolean };
    urls: { index: string; print: string; update: string; accept: string; done: string; addNote: string; addFiles: string };
}

const IMAGE = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
const VIDEO = ['mp4', 'webm', 'ogg'];
const LOGS_PER_PAGE = 5;

export default definePage<Props>({
    title: ({ job }) => `Job Order #${job.number}`,
    description: ({ job }) => (
        <span className="flex flex-wrap items-center gap-x-2 gap-y-1">
            <Badge variant="outline" className={ticketStatusClass(job.status)}>
                {job.status || 'Unknown'}
            </Badge>
            <span>
                Filed by <strong className="text-foreground">{job.creator}</strong> · {job.created_at ?? 'N/A'}
            </span>
        </span>
    ),
    actions: (props) => <HeaderActions {...props} />,
    size: 'xl',
    Content: JobOrderShow,
});

function HeaderActions({ can, urls, noteReasons }: Props) {
    const modal = useModal();
    const [noteOpen, setNoteOpen] = useState(false);
    const size = modal.inModal ? 'sm' : 'default';

    return (
        <>
            {can.update && (
                <Button variant="outline" size={size} onClick={() => setNoteOpen(true)}>
                    <Plus />
                    Add note
                </Button>
            )}
            <Button variant="outline" size={size} asChild>
                <a href={urls.print} target="_blank" rel="noopener">
                    <Printer />
                    Print
                </a>
            </Button>
            {!modal.inModal && (
                <Button variant="outline" asChild>
                    <Link href={urls.index}>
                        <ArrowLeft />
                        Back
                    </Link>
                </Button>
            )}
            <NoteDialog open={noteOpen} onOpenChange={setNoteOpen} reasons={noteReasons} url={urls.addNote} />
        </>
    );
}

function JobOrderShow({ job, values, files, notes, logs, issueTypes, noteReasons, can, urls }: Props) {
    const modal = useModal();
    const [noteOpen, setNoteOpen] = useState(false);
    const [uploadOpen, setUploadOpen] = useState(false);
    const [busy, setBusy] = useState(false);

    const workflow = (url: string) => router.post(url, {}, modal.visit({ preserveScroll: true, onStart: () => setBusy(true), onFinish: () => setBusy(false) }));
    const accepted = ['In Progress', 'Completed'].includes(job.status);
    const completed = job.status === 'Completed';

    return (
        <>
            <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <DetailsCard job={job} values={values} issueTypes={issueTypes} canUpdate={can.update} updateUrl={urls.update} />

                <div className="grid content-start gap-4">
                    {/* Workflow: where the ticket is and the one next action. */}
                    <Card className="gap-4">
                        <CardHeader>
                            <CardTitle>Workflow</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <ol className="grid gap-3">
                                <Step done title="Created" text={job.created_at ?? 'N/A'} />
                                <Step done={accepted} title="Accepted" text={accepted ? `By ${job.assigned_to || 'IT'}` : 'Waiting for acceptance'} />
                                <Step done={completed} title="Completed" text={completed ? 'Task has been completed' : 'Waiting for completion'} />
                            </ol>
                            {can.update && job.status === 'Pending' && (
                                <Button className="w-full" disabled={busy} onClick={() => workflow(urls.accept)}>
                                    {busy ? <Loader2 className="animate-spin" /> : <CheckCircle2 />}
                                    Accept this task
                                </Button>
                            )}
                            {can.update && job.status === 'In Progress' && (
                                <Button className="w-full" disabled={busy} onClick={() => workflow(urls.done)}>
                                    {busy ? <Loader2 className="animate-spin" /> : <Flag />}
                                    Mark as completed
                                </Button>
                            )}
                        </CardContent>
                    </Card>

                    <Card className="gap-4">
                        <CardHeader className="flex flex-row items-center justify-between gap-2">
                            <CardTitle>Notes</CardTitle>
                            {can.update && (
                                <Button variant="ghost" size="sm" className="h-7" onClick={() => setNoteOpen(true)}>
                                    <Plus />
                                    Add
                                </Button>
                            )}
                        </CardHeader>
                        <CardContent>
                            {notes.length === 0 ? (
                                <p className="text-sm text-muted-foreground">No notes yet.</p>
                            ) : (
                                <ul className="grid gap-3">
                                    {notes.map((note) => (
                                        <li key={note.id} className="border-b pb-3 last:border-b-0 last:pb-0">
                                            <div className="flex items-start justify-between gap-2 text-sm">
                                                <span className="font-medium">{note.reason}</span>
                                                <span className="text-xs whitespace-nowrap text-muted-foreground">{note.date}</span>
                                            </div>
                                            {note.details && <p className="mt-1 text-sm break-words text-muted-foreground">{note.details}</p>}
                                            <div className="mt-1 text-xs text-muted-foreground">
                                                {note.user} · {note.time}
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <Card className="gap-4">
                <CardHeader className="flex flex-row items-start justify-between gap-2">
                    <div className="grid gap-1">
                        <CardTitle>Files</CardTitle>
                        <CardDescription>
                            {files.length} file{files.length === 1 ? '' : 's'} · images, videos and documents
                        </CardDescription>
                    </div>
                    {can.update && (
                        <Button size="sm" variant="outline" onClick={() => setUploadOpen(true)}>
                            <CloudUpload />
                            Upload
                        </Button>
                    )}
                </CardHeader>
                <CardContent>
                    {files.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No files attached.</p>
                    ) : (
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            {files.map((file) => (
                                <div key={file.id} className="overflow-hidden rounded-lg border">
                                    <div className="flex aspect-video items-center justify-center bg-muted">
                                        {IMAGE.includes(file.extension) ? (
                                            <a href={file.url} target="_blank" rel="noopener" className="size-full">
                                                <img src={file.url} alt={file.name} loading="lazy" className="size-full object-cover" />
                                            </a>
                                        ) : VIDEO.includes(file.extension) ? (
                                            <video controls preload="metadata" className="size-full">
                                                <source src={file.url} type={`video/${file.extension}`} />
                                            </video>
                                        ) : (
                                            <FileText className="size-10 text-muted-foreground" />
                                        )}
                                    </div>
                                    <div className="flex items-center gap-2 p-2.5">
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate text-sm font-medium" title={file.name}>
                                                {file.name}
                                            </div>
                                            <div className="text-xs text-muted-foreground uppercase">{file.extension || 'file'}</div>
                                            {file.extension === 'avi' && <div className="text-xs text-amber-600">Browser preview is unavailable.</div>}
                                        </div>
                                        <Button variant="ghost" size="icon" className="size-8" asChild>
                                            <a href={file.url} download={file.name} aria-label={`Download ${file.name}`}>
                                                <Download />
                                            </a>
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>

            <ActivityLog logs={logs} />

            <NoteDialog open={noteOpen} onOpenChange={setNoteOpen} reasons={noteReasons} url={urls.addNote} />
            <UploadDialog open={uploadOpen} onOpenChange={setUploadOpen} url={urls.addFiles} />
        </>
    );
}

function DetailsCard({ job, values, issueTypes, canUpdate, updateUrl }: { job: Job; values: Values; issueTypes: string[]; canUpdate: boolean; updateUrl: string }) {
    const modal = useModal();
    const [editing, setEditing] = useState(false);
    const form = useForm<Values>(values);
    const errors = form.errors as Partial<Record<keyof Values, string>>;

    const cancel = () => {
        form.setData(values);
        form.clearErrors();
        setEditing(false);
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(updateUrl, modal.visit({ preserveScroll: true, onSuccess: () => setEditing(false) }));
    };

    const input = (key: keyof Values, props: Record<string, unknown> = {}) => (
        <Input id={key} value={form.data[key]} aria-invalid={!!errors[key]} onChange={(event) => form.setData(key, event.target.value)} {...props} />
    );

    const choice = (key: 'job_type' | 'direction', options: string[]) => (
        <Select value={form.data[key]} onValueChange={(value) => form.setData(key, value)}>
            <SelectTrigger id={key} className="w-full">
                <SelectValue placeholder="Select..." />
            </SelectTrigger>
            <SelectContent>
                {options.map((option) => (
                    <SelectItem key={option} value={option}>
                        {option}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <form onSubmit={submit}>
            <Card className="gap-4">
                <CardHeader className="flex flex-row items-start justify-between gap-2">
                    <CardTitle>Incident</CardTitle>
                    {canUpdate &&
                        (editing ? (
                            <div className="flex gap-2">
                                <Button type="submit" size="sm" disabled={!form.isDirty || form.processing}>
                                    {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                    Save
                                </Button>
                                <Button type="button" variant="outline" size="sm" onClick={cancel}>
                                    <X />
                                    Cancel
                                </Button>
                            </div>
                        ) : (
                            <Button type="button" variant="outline" size="sm" onClick={() => setEditing(true)}>
                                <Pencil />
                                Update details
                            </Button>
                        ))}
                </CardHeader>
                <CardContent className="grid gap-5">
                    <BusSeatMap value={form.data.job_sitNumber} onChange={(value) => form.setData('job_sitNumber', value)} readOnly={!editing} />
                    {errors.job_sitNumber && <p className="-mt-3 text-xs text-destructive">{errors.job_sitNumber}</p>}

                    <div className="grid gap-x-8 md:grid-cols-2">
                        <dl className="divide-y">
                            <Row label="Bus" value={job.bus ? [job.bus.name, job.bus.body_number].filter(Boolean).join(' · ') : 'No bus linked'} hint={job.bus ? [job.bus.plate_number, job.bus.garage].filter(Boolean).join(' · ') : undefined} />
                            <Row label="Issue type" htmlFor="job_type" error={errors.job_type} value={editing ? choice('job_type', issueTypes) : job.job_type || 'N/A'} />
                            <Row label="Direction" htmlFor="direction" error={errors.direction} value={editing ? choice('direction', ['South Bound', 'North Bound']) : job.direction || 'N/A'} />
                            {/* The seat map above already shows the seats; the text box is only for typing them. */}
                            {editing && <Row label="Seat(s)" htmlFor="job_sitNumber" value={input('job_sitNumber', { placeholder: 'e.g. 12, 13', inputMode: 'numeric' })} />}
                        </dl>
                        <dl className="divide-y">
                            <Row label="Date" htmlFor="job_datestart" error={errors.job_datestart} value={editing ? input('job_datestart', { type: 'date' }) : job.date_label} />
                            <Row
                                label="Time"
                                htmlFor="job_time_start"
                                error={errors.job_time_start ?? errors.job_time_end}
                                value={
                                    editing ? (
                                        <div className="grid grid-cols-2 gap-2">
                                            {input('job_time_start', { type: 'time', 'aria-label': 'Start time' })}
                                            {input('job_time_end', { type: 'time', 'aria-label': 'End time' })}
                                        </div>
                                    ) : (
                                        `${job.time_start_label} – ${job.time_end_label}`
                                    )
                                }
                            />
                            <Row label="Driver" htmlFor="driver_name" error={errors.driver_name} value={editing ? input('driver_name') : job.driver_name || 'N/A'} />
                            <Row label="Conductor" htmlFor="conductor_name" error={errors.conductor_name} value={editing ? input('conductor_name') : job.conductor_name || 'N/A'} />
                        </dl>
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="job_remarks" className="text-muted-foreground">
                            Remarks
                        </Label>
                        {editing ? (
                            <Textarea id="job_remarks" rows={3} value={form.data.job_remarks} onChange={(event) => form.setData('job_remarks', event.target.value)} />
                        ) : (
                            <p className="rounded-lg bg-muted/50 p-3 text-sm whitespace-pre-line">{job.remarks || 'No remarks provided.'}</p>
                        )}
                        {errors.job_remarks && <p className="text-xs text-destructive">{errors.job_remarks}</p>}
                    </div>
                </CardContent>
            </Card>
        </form>
    );
}

function Row({ label, value, hint, htmlFor, error }: { label: string; value: ReactNode; hint?: string; htmlFor?: string; error?: string }) {
    return (
        <div className="grid grid-cols-[7rem_minmax(0,1fr)] items-center gap-3 py-2.5 text-sm">
            <dt>
                <Label htmlFor={htmlFor} className="font-normal text-muted-foreground">
                    {label}
                </Label>
            </dt>
            <dd className="min-w-0">
                <div className="font-medium break-words">{value}</div>
                {hint && <div className="text-xs text-muted-foreground">{hint}</div>}
                {error && <p className="mt-1 text-xs text-destructive">{error}</p>}
            </dd>
        </div>
    );
}

function ActivityLog({ logs }: { logs: Log[] }) {
    const [page, setPage] = useState(0);
    const pages = Math.max(1, Math.ceil(logs.length / LOGS_PER_PAGE));
    const visible = logs.slice(page * LOGS_PER_PAGE, (page + 1) * LOGS_PER_PAGE);

    return (
        <Card className="gap-4">
            <CardHeader className="flex flex-row items-center justify-between gap-2">
                <CardTitle>Activity log</CardTitle>
                <Badge variant="secondary">
                    {logs.length} record{logs.length === 1 ? '' : 's'}
                </Badge>
            </CardHeader>
            <CardContent className="grid gap-2">
                {logs.length === 0 && <p className="text-sm text-muted-foreground">Changes and workflow actions will appear here.</p>}
                {visible.map((log) => (
                    <div key={log.id} className="grid gap-2 rounded-lg border p-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                        <div className="min-w-0">
                            <div className="text-sm">
                                <span className="font-medium">{log.user}</span> <span className="text-muted-foreground">{log.action}</span>
                            </div>
                            {log.meta.length > 0 && (
                                <div className="mt-1 grid gap-0.5 text-xs text-muted-foreground">
                                    {log.meta.map((item) => (
                                        <div key={item.key} className="break-words">
                                            <span className="font-medium text-foreground">{item.key}:</span>{' '}
                                            {item.value ?? (
                                                <>
                                                    <span className="line-through decoration-muted-foreground/50">{item.old}</span> <ArrowRight className="inline size-3" /> <span className="text-foreground">{item.new}</span>
                                                </>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                        <div className="text-xs text-muted-foreground sm:text-right">
                            <div>{log.date}</div>
                            <div>{log.time}</div>
                        </div>
                    </div>
                ))}
                {pages > 1 && (
                    <div className="flex items-center justify-end gap-2 text-sm">
                        <Button variant="outline" size="sm" disabled={page === 0} onClick={() => setPage(page - 1)}>
                            Previous
                        </Button>
                        <span className="text-muted-foreground">
                            Page {page + 1} of {pages}
                        </span>
                        <Button variant="outline" size="sm" disabled={page >= pages - 1} onClick={() => setPage(page + 1)}>
                            Next
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function NoteDialog({ open, onOpenChange, reasons, url }: { open: boolean; onOpenChange: (open: boolean) => void; reasons: string[]; url: string }) {
    const modal = useModal();
    const form = useForm({ reason: '', details: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(
            url,
            modal.visit({
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            }),
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Add job order note</DialogTitle>
                        <DialogDescription>Record an issue, observation, or additional information.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label>
                            Reason <span className="text-destructive">*</span>
                        </Label>
                        <RadioGroup value={form.data.reason} onValueChange={(reason) => form.setData('reason', reason)} className="grid grid-cols-2 gap-2">
                            {reasons.map((reason) => (
                                <Label key={reason} className={cn('flex cursor-pointer items-center gap-2 rounded-lg border p-3 font-normal', form.data.reason === reason && 'border-primary bg-accent/50')}>
                                    <RadioGroupItem value={reason} />
                                    {reason}
                                </Label>
                            ))}
                        </RadioGroup>
                        {form.errors.reason && <p className="text-xs text-destructive">{form.errors.reason}</p>}
                    </div>
                    <div className="grid gap-1.5">
                        <Label htmlFor="note-details">Additional details</Label>
                        <Textarea id="note-details" rows={4} placeholder="Enter additional details about the issue..." value={form.data.details} onChange={(event) => form.setData('details', event.target.value)} />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={!form.data.reason || form.processing}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Plus />}
                            Add note
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function UploadDialog({ open, onOpenChange, url }: { open: boolean; onOpenChange: (open: boolean) => void; url: string }) {
    const modal = useModal();
    const form = useForm<{ files: File[] }>({ files: [] });
    const errors = form.errors as Record<string, string>;
    const error = errors.files ?? Object.entries(errors).find(([key]) => key.startsWith('files.'))?.[1];

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(
            url,
            modal.visit({
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            }),
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(value) => {
                if (form.processing) return;
                if (!value) {
                    form.reset();
                    form.clearErrors();
                }
                onOpenChange(value);
            }}
        >
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Upload files</DialogTitle>
                        <DialogDescription>Multiple files may be selected.</DialogDescription>
                    </DialogHeader>
                    <FileDrop files={form.data.files} onChange={(files) => form.setData('files', files)} invalid={!!error} />
                    {error && <p className="text-xs text-destructive">{error}</p>}
                    {form.progress && (
                        <div className="grid gap-1">
                            <div className="h-2 overflow-hidden rounded-full bg-muted">
                                <div className="h-full bg-primary transition-all" style={{ width: `${form.progress.percentage ?? 0}%` }} />
                            </div>
                            <span className="text-center text-xs text-muted-foreground">Uploading... {form.progress.percentage ?? 0}%</span>
                        </div>
                    )}
                    <DialogFooter>
                        <Button type="button" variant="outline" disabled={form.processing} onClick={() => onOpenChange(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.data.files.length === 0 || form.processing}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <CloudUpload />}
                            Upload files
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function Step({ done, title, text }: { done: boolean; title: string; text: string }) {
    return (
        <li className="flex gap-3">
            <span className={cn('mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border', done ? 'border-emerald-500 bg-emerald-500 text-white' : 'text-muted-foreground')}>
                {done && <Check className="size-3" />}
            </span>
            <span>
                <span className="block text-sm font-medium">{title}</span>
                <span className="block text-xs text-muted-foreground">{text}</span>
            </span>
        </li>
    );
}
