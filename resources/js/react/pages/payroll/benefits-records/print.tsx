import type { ReactNode } from 'react';
import { PrintShell, printTable } from '@/components/print/print-shell';
import { cn } from '@/lib/utils';

type Summary = Record<string, number | boolean | string[] | string | null> & { posted: boolean; payroll_numbers: string[] };

interface Row {
    id: number;
    name: string;
    employee_no: string | null;
    company: string;
    ids: { sss: string | null; philhealth: string | null; pagibig: string | null };
    summary: Summary;
}

interface Props {
    period: string;
    generated: string;
    activeEmployeeCount: number;
    postedEmployeeCount: number;
    rows: Row[];
    totals: Record<string, number>;
    companyTotals: { company_name: string; employee_count: number; totals: Record<string, number> }[];
    urls: { back: string };
}

const money = (value: unknown) => `PHP ${Number(value ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const percent = (value: unknown) => `${(Number(value ?? 0) * 100).toFixed(2)}%`;
const EMPTY = 'No payroll-active or posted separation records found.';

/** Benefits Contribution Register (A4 landscape): SSS, PhilHealth, Pag-IBIG, consolidated and per company. */
export default function BenefitsPrint({ period, generated, activeEmployeeCount, postedEmployeeCount, rows, totals, companyTotals }: Props) {
    const t = (key: string) => money(totals[key]);

    return (
        <PrintShell
            title="Benefits Contribution Register"
            subtitle={`SSS · PhilHealth · Pag-IBIG / HDMF — ${period}`}
            landscape
            className="max-w-[1500px] text-[10px]"
            meta={
                <>
                    <div>
                        <b>Contribution month:</b> {period}
                    </div>
                    <div>
                        <b>Active employees:</b> {activeEmployeeCount.toLocaleString()} · <b>Posted:</b> {postedEmployeeCount.toLocaleString()}
                    </div>
                    <div>
                        <b>Generated:</b> {generated}
                    </div>
                </>
            }
        >
            <div className="mb-4 grid grid-cols-5 gap-2">
                {[
                    ['Employee Share Due', 'employee_total'],
                    ['Collected in Payroll', 'employee_collected_total'],
                    ['Unrecovered / Advanced', 'employee_share_unrecovered'],
                    ['Company Contribution', 'employer_total'],
                    ['Combined Contribution', 'grand_total'],
                ].map(([label, key]) => (
                    <div key={key} className="rounded border border-slate-300 px-2 py-1.5">
                        <div className="text-[9px] font-bold tracking-wider text-slate-500 uppercase">{label}</div>
                        <div className="text-sm font-bold tabular-nums">{t(key)}</div>
                    </div>
                ))}
            </div>

            <Section title="Overall Government Contribution Summary">
                <table className={printTable}>
                    <thead>
                        <tr>
                            <th>Program</th>
                            <Num>Employee Share Due</Num>
                            <Num>Collected in Payroll</Num>
                            <Num>Company Share</Num>
                            <Num>Combined Contribution</Num>
                        </tr>
                    </thead>
                    <tbody>
                        {[
                            ['SSS', 'sss'],
                            ['PhilHealth', 'philhealth'],
                            ['Pag-IBIG / HDMF', 'pagibig'],
                        ].map(([label, key]) => (
                            <tr key={key}>
                                <td className="font-bold">{label}</td>
                                <Money value={totals[`${key}_employee`]} />
                                <Money value={totals[`${key}_employee_collected`]} />
                                <Money value={totals[`${key}_employer`]} />
                                <Money value={totals[`${key}_total`]} strong />
                            </tr>
                        ))}
                    </tbody>
                    <tfoot className="font-bold">
                        <tr>
                            <td>OVERALL</td>
                            <Money value={totals.employee_total} />
                            <Money value={totals.employee_collected_total} />
                            <Money value={totals.employer_total} />
                            <Money value={totals.grand_total} />
                        </tr>
                    </tfoot>
                </table>
            </Section>

            <Section title="SSS Detailed Contribution Register">
                <table className={printTable}>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Company</th>
                            <th>SSS No.</th>
                            <Num>
                                1st Gross <span className="font-normal text-slate-500">26-10</span>
                            </Num>
                            <Num>
                                2nd Gross <span className="font-normal text-slate-500">11-25</span>
                            </Num>
                            <Num>Monthly SSS Basis</Num>
                            <Num>MSC</Num>
                            <Num>Regular SS MSC</Num>
                            <Num>MPF MSC</Num>
                            <Num>EE Regular SS</Num>
                            <Num>EE MPF</Num>
                            <Num>EE Total Due</Num>
                            <Num>ER Regular SS</Num>
                            <Num>ER MPF</Num>
                            <Num>ER EC</Num>
                            <Num>ER Total</Num>
                            <Num>Combined</Num>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 && <Empty span={18} />}
                        {rows.map((row) => (
                            <tr key={row.id}>
                                <Employee row={row} />
                                <td>{row.company}</td>
                                <td className="font-mono">{row.ids.sss || '-'}</td>
                                {[
                                    'business_first_cutoff_gross',
                                    'business_second_cutoff_gross',
                                    'sss_compensation_basis',
                                    'sss_msc',
                                    'sss_regular_ss_msc',
                                    'sss_mpf_msc',
                                    'sss_employee_regular_ss',
                                    'sss_employee_mpf',
                                    'sss_employee_total',
                                    'sss_employer_regular_ss',
                                    'sss_employer_mpf',
                                    'sss_employer_ec',
                                    'sss_employer_total',
                                    'sss_total_contribution',
                                ].map((key) => (
                                    <Money key={key} value={row.summary[key]} strong={key === 'sss_compensation_basis'} />
                                ))}
                                <Posted posted={row.summary.posted} />
                            </tr>
                        ))}
                    </tbody>
                    <tfoot className="font-bold">
                        <tr>
                            <td colSpan={11}>SSS TOTAL</td>
                            <Money value={totals.sss_employee} />
                            <td colSpan={3} />
                            <Money value={totals.sss_employer} />
                            <Money value={totals.sss_total} />
                            <td />
                        </tr>
                    </tfoot>
                </table>
            </Section>

            <Section title="PhilHealth Detailed Contribution Register" pageBreak>
                <table className={printTable}>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Company</th>
                            <th>PhilHealth No.</th>
                            <Num>Monthly Basic Salary</Num>
                            <Num>Contribution Basis</Num>
                            <Num>Premium Salary Base</Num>
                            <Num>Employee Share Due</Num>
                            <Num>Company Share</Num>
                            <Num>Combined</Num>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 && <Empty span={10} />}
                        {rows.map((row) => (
                            <tr key={row.id}>
                                <Employee row={row} />
                                <td>{row.company}</td>
                                <td className="font-mono">{row.ids.philhealth || '-'}</td>
                                {['monthly_basic_salary', 'philhealth_basis', 'philhealth_salary_base', 'philhealth_employee', 'philhealth_employer', 'philhealth_total'].map((key) => (
                                    <Money key={key} value={row.summary[key]} />
                                ))}
                                <Posted posted={row.summary.posted} />
                            </tr>
                        ))}
                    </tbody>
                    <tfoot className="font-bold">
                        <tr>
                            <td colSpan={6}>PHILHEALTH TOTAL</td>
                            <Money value={totals.philhealth_employee} />
                            <Money value={totals.philhealth_employer} />
                            <Money value={totals.philhealth_total} />
                            <td />
                        </tr>
                    </tfoot>
                </table>
            </Section>

            <Section title="Pag-IBIG / HDMF Detailed Contribution Register" pageBreak>
                <table className={printTable}>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Company</th>
                            <th>Pag-IBIG MID No.</th>
                            <Num>Monthly Basis</Num>
                            <Num>Fund Salary</Num>
                            <th className="text-center">EE Rate</th>
                            <Num>Employee Share Due</Num>
                            <th className="text-center">ER Rate</th>
                            <Num>Company Share</Num>
                            <Num>Combined</Num>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 && <Empty span={11} />}
                        {rows.map((row) => (
                            <tr key={row.id}>
                                <Employee row={row} />
                                <td>{row.company}</td>
                                <td className="font-mono">{row.ids.pagibig || '-'}</td>
                                <Money value={row.summary.pagibig_basis} />
                                <Money value={row.summary.pagibig_fund_salary} />
                                <td className="text-center">{percent(row.summary.pagibig_employee_rate)}</td>
                                <Money value={row.summary.pagibig_employee} />
                                <td className="text-center">{percent(row.summary.pagibig_employer_rate)}</td>
                                <Money value={row.summary.pagibig_employer} />
                                <Money value={row.summary.pagibig_total} />
                                <Posted posted={row.summary.posted} />
                            </tr>
                        ))}
                    </tbody>
                    <tfoot className="font-bold">
                        <tr>
                            <td colSpan={6}>PAG-IBIG / HDMF TOTAL</td>
                            <Money value={totals.pagibig_employee} />
                            <td />
                            <Money value={totals.pagibig_employer} />
                            <Money value={totals.pagibig_total} />
                            <td />
                        </tr>
                    </tfoot>
                </table>
            </Section>

            <Section title="Consolidated Employee / Company Contribution Register" pageBreak>
                <table className={printTable}>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Company</th>
                            <Num>SSS EE</Num>
                            <Num>SSS ER</Num>
                            <Num>PHIC EE</Num>
                            <Num>PHIC ER</Num>
                            <Num>HDMF EE</Num>
                            <Num>HDMF ER</Num>
                            <Num>Employee Due</Num>
                            <Num>Collected</Num>
                            <Num>Unrecovered</Num>
                            <Num>Company Total</Num>
                            <Num>Grand Total</Num>
                            <th>Payroll Source</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 && <Empty span={15} />}
                        {rows.map((row) => (
                            <tr key={row.id}>
                                <Employee row={row} />
                                <td>{row.company}</td>
                                {[
                                    'sss_employee_total',
                                    'sss_employer_total',
                                    'philhealth_employee',
                                    'philhealth_employer',
                                    'pagibig_employee',
                                    'pagibig_employer',
                                    'employee_total',
                                    'employee_collected_total',
                                    'employee_share_unrecovered',
                                    'employer_total',
                                    'grand_total',
                                ].map((key) => (
                                    <Money key={key} value={row.summary[key]} strong={key === 'grand_total'} />
                                ))}
                                <td>{row.summary.payroll_numbers?.length ? row.summary.payroll_numbers.join(', ') : '-'}</td>
                                <Posted posted={row.summary.posted} />
                            </tr>
                        ))}
                    </tbody>
                    <tfoot className="font-bold">
                        <tr>
                            <td colSpan={2}>OVERALL TOTAL</td>
                            {[
                                'sss_employee',
                                'sss_employer',
                                'philhealth_employee',
                                'philhealth_employer',
                                'pagibig_employee',
                                'pagibig_employer',
                                'employee_total',
                                'employee_collected_total',
                                'employee_share_unrecovered',
                                'employer_total',
                                'grand_total',
                            ].map((key) => (
                                <Money key={key} value={totals[key]} />
                            ))}
                            <td colSpan={2} />
                        </tr>
                    </tfoot>
                </table>
            </Section>

            <Section title="Company Contribution Totals">
                <table className={printTable}>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th className="text-center">Posted Employees</th>
                            <Num>Employee Share Due</Num>
                            <Num>Company Share</Num>
                            <Num>Combined Contribution</Num>
                        </tr>
                    </thead>
                    <tbody>
                        {companyTotals.length === 0 && (
                            <tr>
                                <td colSpan={5} className="text-center">
                                    No finalized contribution records for this period.
                                </td>
                            </tr>
                        )}
                        {companyTotals.map((company) => (
                            <tr key={company.company_name}>
                                <td className="font-bold">{company.company_name}</td>
                                <td className="text-center">{company.employee_count.toLocaleString()}</td>
                                <Money value={company.totals.employee_total} />
                                <Money value={company.totals.employer_total} />
                                <Money value={company.totals.grand_total} strong />
                            </tr>
                        ))}
                    </tbody>
                </table>
            </Section>

            <div className="mt-10 grid grid-cols-3 gap-10 break-inside-avoid">
                {['Prepared By', 'Checked By', 'Approved By'].map((label) => (
                    <div key={label} className="border-t border-slate-700 pt-1 text-center text-[10px] font-bold tracking-wider uppercase">
                        {label}
                    </div>
                ))}
            </div>

            <p className="mt-6 text-[9px] text-slate-500">
                Exact monthly contribution amounts are read from finalized Benefits Records. SSS compensation is the finalized 1st cutoff gross (26-10) plus finalized 2nd cutoff gross (11-25),
                then the official MSC / Regular SS / MPF / EC schedule is applied. This print view does not recalculate values while rendering.
            </p>
        </PrintShell>
    );
}

function Section({ title, pageBreak, children }: { title: string; pageBreak?: boolean; children: ReactNode }) {
    return (
        <section className={cn('mb-5', pageBreak && 'print:break-before-page')}>
            <h2 className="mb-1.5 text-[11px] font-bold tracking-wider text-blue-800 uppercase">{title}</h2>
            {children}
        </section>
    );
}

function Num({ children }: { children: ReactNode }) {
    return <th className="text-right">{children}</th>;
}

function Money({ value, strong }: { value: unknown; strong?: boolean }) {
    return <td className={cn('text-right whitespace-nowrap tabular-nums', strong && 'font-bold')}>{money(value)}</td>;
}

function Employee({ row }: { row: Row }) {
    return (
        <td>
            <div className="font-semibold">{row.name}</div>
            <div className="text-slate-500">{row.employee_no || '-'}</div>
        </td>
    );
}

function Posted({ posted }: { posted: boolean }) {
    return <td className={cn('text-center font-bold whitespace-nowrap', posted ? 'text-emerald-700' : 'text-amber-700')}>{posted ? 'POSTED' : 'NOT POSTED'}</td>;
}

function Empty({ span }: { span: number }) {
    return (
        <tr>
            <td colSpan={span} className="text-center">
                {EMPTY}
            </td>
        </tr>
    );
}
