import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Bus, CheckCircle2, ClipboardCheck, FileSpreadsheet, FileText, Gauge, Hash, History, Pencil, Printer, Timer, TriangleAlert, UserCheck } from 'lucide-react';
import type { ReactNode } from 'react';
import { JobStatusBadge, RepairTypeBadge, type Option } from '@/components/maintenance/job-order-parts';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';

interface JobOrder {
    id: number;
    job_order_no: string;
    creator: string;
    bus_no: string;
    plate_no: string;
    company: string;
    garage: string;
    requester: string;
    status: { value: string | null; label: string; description: string };
    created: string;
    created_date: string;
    created_time: string;
    updated: string;
    work: string;
    mechanics: string[];
    mechanics_label: string;
    repair_types: { value: string; label: string }[];
    repair_types_label: string;
    downtime: string;
    downtime_running: boolean;
    downtime_breakdown: { value: string; label: string; duration: string; current: boolean }[];
    periods: { id: number; status: string; label: string; started: string; ended: string | null; duration: string; by: string }[];
    odometer: string | null;
    last_odometer: string | null;
    odometer_difference: string | null;
    odometer_lower: boolean;
    odometer_note: string;
    histories: { id: number; action: string; at: string | null; by: string; old: string | null; new: string | null; remarks: string | null }[];
}

interface Props {
    jobOrder: JobOrder;
    statuses: Option[];
    can: { updateNumber: boolean; updateStatus: boolean };
    urls: { index: string; csv: string; xls: string; editNumber: string; editStatus: string };
}

export default function JobOrderShow({ jobOrder, statuses, can, urls }: Props) {
    return (
        <AppLayout title={`Job Order ${jobOrder.job_order_no}`}>
            <Card>
                <CardContent className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div className="grid gap-2">
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-2xl font-semibold tracking-tight">{jobOrder.job_order_no}</h2>
                            <JobStatusBadge value={jobOrder.status.value} label={jobOrder.status.label} />
                        </div>
                        <p className="text-sm text-muted-foreground">{jobOrder.status.description}</p>
                        <div className="flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted-foreground">
                            <span className="flex items-center gap-1">
                                <Bus className="size-4" />
                                {jobOrder.bus_no}
                            </span>
                            <span>{jobOrder.plate_no}</span>
                            <span>{jobOrder.company}</span>
                            <span>{jobOrder.created}</span>
                            <span className="flex items-center gap-1">
                                <Timer className="size-4" />
                                Downtime: {jobOrder.downtime}
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2 print:hidden">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={urls.index}>
                                <ArrowLeft />
                                Back
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <a href={urls.csv}>
                                <FileText />
                                CSV
                            </a>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <a href={urls.xls}>
                                <FileSpreadsheet />
                                Excel
                            </a>
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => window.print()}>
                            <Printer />
                            Print
                        </Button>
                        {can.updateNumber && (
                            <Button variant="outline" size="sm" asChild>
                                <Link href={urls.editNumber}>
                                    <Hash />
                                    Edit JO-NO
                                </Link>
                            </Button>
                        )}
                        {can.updateStatus && (
                            <Button size="sm" asChild>
                                <Link href={urls.editStatus}>
                                    <Pencil />
                                    Edit status
                                </Link>
                            </Button>
                        )}
                    </div>
                </CardContent>
            </Card>

            <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
                <div className="grid min-w-0 content-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Job order overview</CardTitle>
                            <CardDescription>Created by {jobOrder.creator}</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-4">
                            <Info label="Bus no." value={jobOrder.bus_no} large />
                            <Info label="Plate no." value={jobOrder.plate_no} large />
                            <Info label="Requester / staff" value={jobOrder.requester} />
                            <Info label="Created date" value={jobOrder.created_date} hint={jobOrder.created_time} />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                <Timer className="size-4" />
                                Downtime analysis
                            </CardTitle>
                            <Badge
                                variant="outline"
                                className={jobOrder.downtime_running ? 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400' : 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400'}
                            >
                                {jobOrder.downtime_running ? 'Counter running' : 'Counter stopped'}
                            </Badge>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-3 sm:grid-cols-3">
                                {jobOrder.downtime_breakdown.map((item) => (
                                    <div key={item.value} className={cn('rounded-lg border p-3', item.current && 'border-primary')}>
                                        <div className="flex items-center justify-between gap-2">
                                            <JobStatusBadge value={item.value} label={item.label} />
                                            {item.current && <Badge variant="secondary">Counting</Badge>}
                                        </div>
                                        <div className="mt-2 text-xs text-muted-foreground uppercase">Accumulated time</div>
                                        <div className="text-lg font-semibold">{item.duration}</div>
                                    </div>
                                ))}
                            </div>
                            <Alert>
                                {jobOrder.downtime_running ? <Timer /> : <CheckCircle2 />}
                                <AlertDescription>
                                    <span className="font-semibold text-foreground">Total downtime: {jobOrder.downtime}.</span>{' '}
                                    {jobOrder.downtime_running
                                        ? `The counter is active because the current status is ${jobOrder.status.label}.`
                                        : 'The counter stopped when the job order became Operational.'}
                                </AlertDescription>
                            </Alert>
                            <div className="overflow-x-auto rounded-lg border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Status period</TableHead>
                                            <TableHead>Started</TableHead>
                                            <TableHead>Ended</TableHead>
                                            <TableHead>Duration</TableHead>
                                            <TableHead>Changed by</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {jobOrder.periods.length === 0 && (
                                            <TableRow>
                                                <TableCell colSpan={5} className="py-6 text-center text-muted-foreground">
                                                    No downtime periods are available for this legacy record.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                        {jobOrder.periods.map((period) => (
                                            <TableRow key={period.id}>
                                                <TableCell>
                                                    <JobStatusBadge value={period.status} label={period.label} />
                                                </TableCell>
                                                <TableCell>{period.started}</TableCell>
                                                <TableCell>{period.ended ?? <Badge variant="outline" className="border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400">Still counting</Badge>}</TableCell>
                                                <TableCell className="font-medium">{period.duration}</TableCell>
                                                <TableCell>{period.by}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UserCheck className="size-4" />
                                Repair completion details
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <div className="text-xs text-muted-foreground">Mechanic(s) who fixed the unit</div>
                                {jobOrder.mechanics.length === 0 ? (
                                    <div className="mt-1 text-sm text-muted-foreground">Not assigned</div>
                                ) : (
                                    <ul className="mt-1 grid gap-1 text-sm font-medium">
                                        {jobOrder.mechanics.map((name) => (
                                            <li key={name} className="flex items-center gap-2">
                                                <CheckCircle2 className="size-4 text-emerald-600" />
                                                {name}
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                            <div>
                                <div className="text-xs text-muted-foreground">Type of repair done</div>
                                <div className="mt-1 flex flex-wrap gap-1">
                                    {jobOrder.repair_types.length === 0 && <span className="text-sm text-muted-foreground">Not encoded</span>}
                                    {jobOrder.repair_types.map((type) => (
                                        <RepairTypeBadge key={type.value} value={type.value} label={type.label} />
                                    ))}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <ClipboardCheck className="size-4" />
                                Description of work
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm whitespace-pre-line">{jobOrder.work}</CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Gauge className="size-4" />
                                Odometer analysis
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-4 sm:grid-cols-3">
                                <Info label="Current reading" value={jobOrder.odometer ?? 'Not encoded'} large />
                                <Info label="Previous reading" value={jobOrder.last_odometer ?? 'N/A'} large />
                                <Info label="Difference" value={<span className={jobOrder.odometer_lower ? 'text-destructive' : 'text-emerald-600'}>{jobOrder.odometer_difference ?? 'N/A'}</span>} large />
                            </div>
                            <Alert variant={jobOrder.odometer_lower ? 'destructive' : 'default'}>
                                {jobOrder.odometer_lower && <TriangleAlert />}
                                <AlertDescription>
                                    {jobOrder.odometer_lower
                                        ? 'Current odometer reading is lower than the previous reading. Verify the encoded value before using this record for analytics.'
                                        : jobOrder.odometer_note}
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <History className="size-4" />
                                Update history
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            {jobOrder.histories.length === 0 && <p className="py-4 text-center text-sm text-muted-foreground">No update history available.</p>}
                            {jobOrder.histories.map((history) => (
                                <div key={history.id} className="rounded-lg border p-3 text-sm">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <span className="font-medium">{history.action}</span>
                                        <span className="text-xs text-muted-foreground">
                                            {history.at} · by {history.by}
                                        </span>
                                    </div>
                                    {(history.old || history.new) && (
                                        <div className="mt-1 flex flex-wrap items-center gap-1 text-xs">
                                            {history.old && <Badge variant="outline">{history.old}</Badge>}
                                            <ArrowRight className="size-3 text-muted-foreground" />
                                            {history.new && <Badge variant="secondary">{history.new}</Badge>}
                                        </div>
                                    )}
                                    {history.remarks && (
                                        <div className="mt-2">
                                            <div className="text-xs text-muted-foreground">Remarks</div>
                                            <p className="whitespace-pre-line">{history.remarks}</p>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>

                <div className="grid content-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Maintenance workflow</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ol className="grid gap-3 border-l pl-4">
                                {statuses.map((status) => {
                                    const current = jobOrder.status.value === status.value;

                                    return (
                                        <li key={status.value} className="relative">
                                            <span className={cn('absolute top-1.5 -left-[21px] size-2.5 rounded-full border-2 border-background', current ? 'bg-primary' : 'bg-muted-foreground/30')} />
                                            <div className="flex items-center gap-2">
                                                <JobStatusBadge value={status.value} label={status.label} />
                                                {current && <Badge variant="secondary">Current</Badge>}
                                            </div>
                                            <p className="mt-1 text-xs text-muted-foreground">{status.description}</p>
                                        </li>
                                    );
                                })}
                            </ol>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Vehicle information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            <Info label="Bus no." value={jobOrder.bus_no} />
                            <Info label="Plate no." value={jobOrder.plate_no} />
                            <Info label="Company" value={jobOrder.company} />
                            <Info label="Garage" value={jobOrder.garage} />
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Record information</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            <Info label="Created by" value={jobOrder.creator} />
                            <Info label="Current status" value={<JobStatusBadge value={jobOrder.status.value} label={jobOrder.status.label} />} />
                            <Info label="Mechanic(s)" value={jobOrder.mechanics_label} />
                            <Info label="Repair type(s)" value={jobOrder.repair_types_label} />
                            <Info label="Created at" value={jobOrder.created} />
                            <Info label="Last updated" value={jobOrder.updated} />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

function Info({ label, value, hint, large }: { label: string; value: ReactNode; hint?: string; large?: boolean }) {
    return (
        <div>
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className={large ? 'mt-0.5 text-lg font-semibold' : 'mt-0.5 font-medium'}>{value}</div>
            {hint && <div className="text-xs text-muted-foreground">{hint}</div>}
        </div>
    );
}
