import { PrintShell } from '@/components/print/print-shell';
import { cn } from '@/lib/utils';

interface AttendanceRecord {
    day: string | null;
    date: string | null;
    in: string | null;
    out: string | null;
    worked_hours: number;
    payable_days: number;
    status: string;
}

interface EmployeeCard {
    id: number;
    name: string | null;
    employee_no: string | null;
    biometric_id: string | null;
    records: AttendanceRecord[];
    totals: { absent: number; review: number; holiday_paid: number; holiday_unpaid: number; late_minutes: number; undertime_minutes: number; payable_days: number };
}

interface Props {
    cutoffLabel: string;
    groupLabel: string;
    recordCount: number;
    stats: Record<string, number>;
    employees: EmployeeCard[];
    printed: string;
}

/** Employees per printed page (3 × 3 cards, like the old export). */
const PER_PAGE = 9;
/** Rows per card, padded so every card is the same height. */
const ROWS = 15;

const DANGER = ['holiday_unpaid', 'no_schedule', 'incomplete_log', 'absent'];
const WARNING = ['half_day', 'late', 'undertime', 'late_undertime'];

const rowTone = (record: AttendanceRecord) =>
    DANGER.includes(record.status) ? 'bg-red-50 text-red-800' : WARNING.includes(record.status) ? 'bg-amber-50 text-amber-800' : record.payable_days > 0 ? 'bg-emerald-50/60' : '';

const n = (value: number, digits = 0) => Number(value ?? 0).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });

/** Payroll Attendance Summary export: printable per-employee cutoff cards (Letter size). */
export default function AttendanceExport({ cutoffLabel, groupLabel, recordCount, stats, employees, printed }: Props) {
    const pages: EmployeeCard[][] = [];
    for (let index = 0; index < employees.length; index += PER_PAGE) pages.push(employees.slice(index, index + PER_PAGE));

    return (
        <PrintShell title={`Payroll Attendance Export — ${cutoffLabel}`} pageSize="8.5in 11in" bare className="max-w-[900px] p-6 text-[9px]">
            {pages.length === 0 && (
                <section>
                    <h1 className="text-center text-base font-bold">Payroll Attendance Summary</h1>
                    <p className="text-center text-slate-600">No records found for {cutoffLabel}</p>
                </section>
            )}

            {pages.map((page, pageIndex) => (
                <section key={pageIndex} className={cn(pageIndex > 0 && 'mt-10 border-t-2 border-dashed pt-6 print:mt-0 print:border-0 print:pt-0', 'print:break-after-page print:last:break-after-auto')}>
                    <div className="mb-2 flex items-center gap-2 border-b-2 border-blue-700 pb-1.5">
                        <img src="/assets/img/favicons/esgroup-logo180x180.png" alt="" className="size-7 object-contain" />
                        <div>
                            <div className="text-[8px] font-bold tracking-wider text-slate-500 uppercase">Jell Group of Company</div>
                            <h1 className="text-sm leading-tight font-bold">Payroll Attendance Summary</h1>
                        </div>
                        <div className="ml-auto text-right text-[9px] text-slate-600">
                            Cutoff: {cutoffLabel} | {groupLabel} | Page {pageIndex + 1} of {pages.length}
                        </div>
                    </div>

                    <div className="mb-1.5 grid grid-cols-8 gap-1 text-[8px]">
                        <Stat label="Roster" value={`${n(stats.eligible_employees ?? employees.length)} eligible / ${n(employees.length)} shown`} />
                        <Stat label="Records" value={n(recordCount)} />
                        <Stat label="Pay Units" value={n(stats.total_payable_days ?? 0, 2)} />
                        <Stat label="Needs Review" value={n(stats.needs_review ?? 0)} />
                        <Stat label="Holiday Paid" value={n(stats.holiday_paid ?? 0)} />
                        <Stat label="Holiday Unpaid" value={n(stats.holiday_unpaid ?? 0)} />
                        <Stat label="Rest Day Paid" value={n(stats.rest_day_paid ?? 0)} />
                        <Stat label="Adjustments" value={n(stats.adjustment ?? 0)} />
                    </div>

                    <p className="mb-2 rounded border border-slate-300 bg-slate-50 p-1 text-[7.5px] leading-snug">
                        <b>Payroll Rules:</b> Rest day/day off is 100% paid. Holiday without work is paid only when before/after dates are qualified by biometrics, leave, adjustment, holiday, or
                        plotted rest day. Regular holiday worked = 2.00 pay units. Special/non-regular holiday worked = 1.30 pay units. No Schedule, Incomplete Log, Half Day, Absent, and Unpaid
                        Holiday must be checked before payroll.
                    </p>

                    <div className="grid grid-cols-3 gap-1.5">
                        {page.map((employee) => (
                            <Card key={employee.id} employee={employee} />
                        ))}
                    </div>

                    <div className="mt-4 grid grid-cols-3 gap-8 text-center text-[8px] break-inside-avoid">
                        {[
                            ['Prepared By', 'Payroll / HR Staff'],
                            ['Checked By', 'HR Supervisor'],
                            ['Approved By', 'Authorized Signatory'],
                        ].map(([label, role]) => (
                            <div key={label}>
                                <div className="mt-6 border-t border-slate-700 pt-0.5 font-bold">{label}</div>
                                <div className="text-slate-500">{role}</div>
                            </div>
                        ))}
                    </div>
                    <div className="mt-2 text-right text-[7.5px] text-slate-500">Printed: {printed}</div>
                </section>
            ))}
        </PrintShell>
    );
}

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded border border-slate-300 px-1 py-0.5">
            <b>{label}</b>
            <br />
            {value}
        </div>
    );
}

function Card({ employee }: { employee: EmployeeCard }) {
    const padding = Math.max(0, ROWS - employee.records.length);

    return (
        <div className="rounded border border-slate-400 p-1 break-inside-avoid">
            <div className="truncate text-[9.5px] font-bold">{employee.name || 'NO NAME'}</div>
            <div className="mb-0.5 flex justify-between text-[7.5px] text-slate-600">
                <span>Emp No: {employee.employee_no || '—'}</span>
                <span>Bio ID: {employee.biometric_id || '—'}</span>
            </div>
            {employee.records.length === 0 && <div className="mb-0.5 bg-red-50 p-0.5 text-[7.5px] font-bold text-red-800">NO ATTENDANCE SUMMARY — REBUILD / CHECK SCHEDULE</div>}
            <table className="w-full table-fixed border-collapse text-[7px] [&_td]:border [&_td]:border-slate-300 [&_td]:px-0.5 [&_th]:border [&_th]:border-slate-300 [&_th]:bg-slate-100 [&_th]:px-0.5">
                <thead>
                    <tr>
                        <th className="w-[12%]">Day</th>
                        <th className="w-[13%]">Date</th>
                        <th className="w-[15%]">In</th>
                        <th className="w-[15%]">Out</th>
                        <th className="w-[13%]">Work</th>
                        <th className="w-[13%]">Pay</th>
                        <th className="w-[19%]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {employee.records.map((record, index) => (
                        <tr key={index} className={rowTone(record)}>
                            <td>{record.day ?? '—'}</td>
                            <td>{record.date ?? '—'}</td>
                            <td>{record.in ?? '—'}</td>
                            <td>{record.out ?? '—'}</td>
                            <td>{n(record.worked_hours, 2)}</td>
                            <td>{n(record.payable_days, 2)}</td>
                            <td className="truncate">{record.status ? record.status.replace(/_/g, ' ').toUpperCase() : '—'}</td>
                        </tr>
                    ))}
                    {Array.from({ length: padding }, (_, index) => (
                        <tr key={`pad-${index}`}>
                            <td>&nbsp;</td>
                            <td />
                            <td />
                            <td />
                            <td />
                            <td />
                            <td />
                        </tr>
                    ))}
                </tbody>
            </table>
            <div className="mt-0.5 grid grid-cols-3 gap-x-1 text-[7px]">
                <div>
                    <b>Absent:</b> {n(employee.totals.absent)}
                </div>
                <div>
                    <b>Review:</b> {n(employee.totals.review)}
                </div>
                <div>
                    <b>Hol Paid:</b> {n(employee.totals.holiday_paid)}
                </div>
                <div>
                    <b>Hol Unpaid:</b> {n(employee.totals.holiday_unpaid)}
                </div>
                <div>
                    <b>Late/UT:</b> {n(employee.totals.late_minutes)}/{n(employee.totals.undertime_minutes)} min
                </div>
                <div>
                    <b>Pay Units:</b> {n(employee.totals.payable_days, 2)}
                </div>
            </div>
        </div>
    );
}
