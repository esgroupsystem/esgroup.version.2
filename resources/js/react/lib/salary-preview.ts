/**
 * Live salary preview. A direct port of the former Blade
 * _salary_preview_script so the React form shows the same numbers; the
 * saved rates are still computed on the server (PayrollDeductionService).
 */

export type Schedule = 'none' | 'first_cutoff' | 'second_cutoff' | 'every_cutoff';
/** Legacy cutoff keys: "first" = business 2nd cutoff (11-25), "second" = business 1st cutoff (26-10). */
export type CutoffKey = 'first' | 'second';

export interface SssRules {
    minimum_msc: number | string;
    maximum_msc: number | string;
    msc_increment: number | string;
    first_middle_range: number | string;
    maximum_range_start: number | string;
    employee_rate: number | string;
    employer_rate: number | string;
    ec_low_msc_maximum: number | string;
    ec_low_amount: number | string;
    ec_high_amount: number | string;
}

const ANNUAL_CALENDAR_DAYS = 365;
const ANNUAL_MONTHS = 12;

export const num = (value: unknown): number => {
    const parsed = Number.parseFloat(String(value ?? ''));
    return Number.isFinite(parsed) ? parsed : 0;
};

export function monthlyBasicSalary(rateType: string, basicSalary: number): number {
    if (basicSalary <= 0) return 0;
    return rateType === 'monthly' ? basicSalary : (basicSalary * ANNUAL_CALENDAR_DAYS) / ANNUAL_MONTHS;
}

export function salaryRates(rateType: string, basicSalary: number, paidHoursPerDay: number) {
    const dailyRate = basicSalary > 0 ? (rateType === 'monthly' ? (basicSalary * ANNUAL_MONTHS) / ANNUAL_CALENDAR_DAYS : basicSalary) : 0;
    const hourlyRate = dailyRate / Math.max(1, paidHoursPerDay || 8);
    const perMinute = hourlyRate / 60;

    return { dailyRate, hourlyRate, perMinute };
}

function sssMonthlySalaryCredit(monthlySalary: number, rules: SssRules): number {
    if (monthlySalary <= 0) return 0;

    const minimumMsc = num(rules.minimum_msc);
    const maximumMsc = num(rules.maximum_msc);
    const increment = num(rules.msc_increment);
    const firstMiddleRange = num(rules.first_middle_range);
    const maximumRangeStart = num(rules.maximum_range_start);

    if (monthlySalary < firstMiddleRange) return minimumMsc;
    if (monthlySalary >= maximumRangeStart) return maximumMsc;

    const step = Math.floor((monthlySalary - firstMiddleRange) / increment) + 1;
    return Math.min(maximumMsc, minimumMsc + step * increment);
}

export function sssBreakdown(monthlySalary: number, rules: SssRules) {
    if (monthlySalary <= 0) return { msc: 0, employee: 0, employer: 0, ec: 0, total: 0 };

    const msc = sssMonthlySalaryCredit(monthlySalary, rules);
    const employee = msc * num(rules.employee_rate);
    const employer = msc * num(rules.employer_rate);
    const ec = msc <= num(rules.ec_low_msc_maximum) ? num(rules.ec_low_amount) : num(rules.ec_high_amount);

    return { msc, employee, employer, ec, total: employee + employer + ec };
}

export function pagibigEmployeeShare(monthlySalary: number): number {
    if (monthlySalary <= 0) return 0;
    const base = Math.min(monthlySalary, 10000);
    return base * (base <= 1500 ? 0.01 : 0.02);
}

export function philhealthEmployeeShare(monthlySalary: number): number {
    if (monthlySalary <= 0) return 0;
    const base = Math.min(Math.max(monthlySalary, 10000), 100000);
    return (base * 0.05) / 2;
}

export function monthlyToCutoff(monthlyAmount: number, schedule: string, cutoff: CutoffKey): number {
    if (monthlyAmount <= 0 || schedule === 'none') return 0;
    if (schedule === 'every_cutoff') return monthlyAmount / 2;
    if (schedule === 'first_cutoff' && cutoff === 'first') return monthlyAmount;
    if (schedule === 'second_cutoff' && cutoff === 'second') return monthlyAmount;
    return 0;
}

export function fixedDeductionToCutoff(paymentAmount: number, schedule: string, cutoff: CutoffKey): number {
    if (paymentAmount <= 0 || schedule === 'none') return 0;
    if (schedule === 'every_cutoff') return paymentAmount;
    if (schedule === 'first_cutoff' && cutoff === 'first') return paymentAmount;
    if (schedule === 'second_cutoff' && cutoff === 'second') return paymentAmount;
    return 0;
}

function nextCutoffDate(afterDate: Date, schedule: string): Date {
    const allowedDays = schedule === 'first_cutoff' ? [25] : schedule === 'second_cutoff' ? [11] : [11, 25];
    const candidates: Date[] = [];

    for (let monthOffset = 0; monthOffset <= 24; monthOffset++) {
        allowedDays.forEach((day) => {
            const candidate = new Date(afterDate.getFullYear(), afterDate.getMonth() + monthOffset, day);
            if (candidate > afterDate) candidates.push(candidate);
        });
    }

    candidates.sort((a, b) => a.getTime() - b.getTime());
    return candidates[0] ?? afterDate;
}

export function estimatedLastPayment(totalAmount: number, paymentAmount: number, schedule: string, startDate: string): string {
    if (totalAmount <= 0 || paymentAmount <= 0 || schedule === 'none') return '—';

    const paymentCount = Math.ceil(totalAmount / paymentAmount);
    let cursor = startDate ? new Date(`${startDate}T00:00:00`) : new Date();
    cursor.setDate(cursor.getDate() - 1);

    for (let i = 0; i < paymentCount; i++) cursor = nextCutoffDate(cursor, schedule);

    return cursor.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: '2-digit' });
}
