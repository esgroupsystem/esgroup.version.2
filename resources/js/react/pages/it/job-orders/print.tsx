import type { ReactNode } from 'react';
import { PrintShell } from '@/components/print/print-shell';
import { cn } from '@/lib/utils';

interface Props {
    job: {
        number: string;
        status: string;
        created_short: string;
        created_long: string;
        creator: string | null;
        assigned_to: string | null;
        job_type: string | null;
        direction: string | null;
        date_start: string;
        time_start: string;
        time_end: string;
        seat: string | null;
        driver: string | null;
        conductor: string | null;
        remarks: string | null;
        bus: { name: string | null; body_number: string | null; plate_number: string | null; garage: string | null };
    };
    appName: string;
    printed: string;
}

const STATUS_TONE: Record<string, string> = {
    pending: 'border-amber-300 bg-amber-50 text-amber-800',
    'in progress': 'border-sky-300 bg-sky-50 text-sky-800',
    completed: 'border-emerald-300 bg-emerald-50 text-emerald-800',
    cancelled: 'border-red-300 bg-red-50 text-red-800',
    canceled: 'border-red-300 bg-red-50 text-red-800',
};

const na = (value: string | null | undefined, empty = 'N/A') => (value && value.trim() ? value : empty);

/** Printable IT job order (A4): details, remarks and signature blocks. */
export default function JobOrderPrint({ job, appName, printed }: Props) {
    return (
        <PrintShell
            title={`IT Job Order #${job.number}`}
            subtitle="Internal service request and work assignment document"
            meta={
                <div className="grid justify-items-end gap-1">
                    <span className="text-sm font-bold text-slate-900">#{job.number}</span>
                    <span className={cn('rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase', STATUS_TONE[job.status.toLowerCase()] ?? 'border-slate-300 bg-slate-50 text-slate-700')}>
                        {na(job.status, 'Unknown')}
                    </span>
                </div>
            }
            className="max-w-[900px]"
        >
            <div className="mb-4 grid grid-cols-4 gap-2">
                <Summary label="Date created" value={job.created_short} />
                <Summary label="Requester" value={na(job.creator)} />
                <Summary label="Assigned to" value={na(job.assigned_to, 'Not assigned')} />
                <Summary label="Job type" value={na(job.job_type)} />
            </div>

            <Section title="Request information" description="General job-order information">
                <Pairs
                    rows={[
                        ['Job Order No.', `#${job.number}`, 'Current Status', na(job.status)],
                        ['Date Created', job.created_long, 'Requester', na(job.creator)],
                        ['Assigned To', na(job.assigned_to, 'Not assigned'), 'Direction', na(job.direction)],
                    ]}
                />
            </Section>

            <Section title="Bus information" description="Vehicle associated with the job order">
                <Pairs
                    rows={[
                        ['Bus Name', na(job.bus.name), 'Body Number', na(job.bus.body_number)],
                        ['Plate Number', na(job.bus.plate_number), 'Garage', na(job.bus.garage)],
                    ]}
                />
            </Section>

            <Section title="Job details" description="Incident schedule and assigned personnel">
                <Pairs
                    rows={[
                        ['Job Type', na(job.job_type), 'Date Start', job.date_start],
                        ['Start Time', job.time_start, 'End Time', job.time_end],
                        ['Seat Number', na(job.seat), 'Direction', na(job.direction)],
                        ['Driver', na(job.driver), 'Conductor', na(job.conductor)],
                    ]}
                />
            </Section>

            <Section title="Remarks" description="Findings and additional information">
                <div className="min-h-16 rounded border border-slate-300 p-2 whitespace-pre-line">
                    {job.remarks?.trim() ? job.remarks : <span className="text-slate-400 italic">No remarks were provided for this job order.</span>}
                </div>
            </Section>

            <div className="mt-8 grid grid-cols-2 gap-10 break-inside-avoid">
                <Signature label="Prepared by" name={na(job.creator, 'Requester')} position="Requesting Personnel" />
                <Signature label="Assigned to / Received by" name={na(job.assigned_to, 'Assigned Personnel')} position="IT Department" />
            </div>

            <div className="mt-8 break-inside-avoid">
                <div className="mb-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase">Completion and approval acknowledgment</div>
                <div className="grid grid-cols-3 border border-slate-300">
                    {['Completed by / Signature', 'Approved by / Signature', 'Date Completed'].map((label) => (
                        <div key={label} className="h-16 border-r border-slate-300 p-1.5 text-[10px] text-slate-500 last:border-r-0">
                            {label}
                        </div>
                    ))}
                </div>
            </div>

            <footer className="mt-6 flex justify-between border-t border-slate-200 pt-2 text-[10px] text-slate-500">
                <span>Internal document generated by {appName}</span>
                <span>Printed: {printed}</span>
            </footer>
        </PrintShell>
    );
}

function Summary({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded border border-slate-200 bg-slate-50 px-2 py-1.5">
            <div className="text-[9px] font-bold tracking-wider text-slate-500 uppercase">{label}</div>
            <div className="truncate font-semibold">{value}</div>
        </div>
    );
}

function Section({ title, description, children }: { title: string; description: string; children: ReactNode }) {
    return (
        <section className="mb-3 break-inside-avoid">
            <div className="mb-1 flex items-baseline justify-between border-b border-slate-200 pb-0.5">
                <span className="text-[11px] font-bold tracking-wider text-blue-700 uppercase">{title}</span>
                <span className="text-[10px] text-slate-400">{description}</span>
            </div>
            {children}
        </section>
    );
}

function Pairs({ rows }: { rows: [string, string, string, string][] }) {
    return (
        <table className="w-full border-collapse text-[11px]">
            <tbody>
                {rows.map(([labelA, valueA, labelB, valueB]) => (
                    <tr key={labelA}>
                        <td className="w-[18%] border border-slate-300 bg-slate-50 px-1.5 py-1 font-semibold text-slate-600">{labelA}</td>
                        <td className="w-[32%] border border-slate-300 px-1.5 py-1">{valueA}</td>
                        <td className="w-[18%] border border-slate-300 bg-slate-50 px-1.5 py-1 font-semibold text-slate-600">{labelB}</td>
                        <td className="w-[32%] border border-slate-300 px-1.5 py-1">{valueB}</td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}

function Signature({ label, name, position }: { label: string; name: string; position: string }) {
    return (
        <div>
            <div className="mb-8 text-[10px] font-bold tracking-wider text-slate-500 uppercase">{label}</div>
            <div className="border-t border-slate-700 pt-1 text-center">
                <div className="font-semibold">{name}</div>
                <div className="text-[10px] text-slate-500">{position}</div>
            </div>
        </div>
    );
}
