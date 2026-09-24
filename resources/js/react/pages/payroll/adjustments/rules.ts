/**
 * Per-type UI behaviour of the adjustment form. Mirrors the rules the Blade
 * form enforced in the browser; the server applies the same rules again
 * (PayrollAttendanceAdjustmentController::buildPayload), so these only drive
 * what is shown and pre-filled.
 */

export type Section = 'leave' | 'single-date' | 'manual-time' | 'offset' | 'cash' | 'disaster';

export interface SwitchState {
    checked: boolean;
    disabled: boolean;
    help: string;
}

export const isDisasterType = (type: string) => type.startsWith('typhoon_disaster');

export function disasterHours(type: string): number {
    const match = type.match(/_(3|4|5|6)h$/);
    return match ? Number(match[1]) : 3;
}

export function sectionsFor(type: string): Section[] {
    if (type === 'sick_leave' || type === 'medical_leave') return ['leave'];
    if (['change_schedule', 'official_business', 'holiday_work', 'overtime'].includes(type)) return ['single-date', 'manual-time'];
    if (type === 'offset') return ['single-date', 'offset'];
    if (type === 'cash_adjustment') return ['single-date', 'cash'];
    if (isDisasterType(type)) return ['single-date', 'disaster'];
    return [];
}

export function workDateLabel(type: string): string {
    if (type === 'offset') return 'Offset Target Date';
    if (type === 'cash_adjustment') return 'Salary Adjustment Date';
    if (isDisasterType(type)) return `Typhoon / Disaster Date (${disasterHours(type)}hrs threshold)`;
    return 'Work Date';
}

export const manualTimeText: Record<string, { title: string; help: string; inLabel: string; outLabel: string }> = {
    change_schedule: {
        title: 'Temporary Scheduled Time In / Time Out',
        help: 'Changes the EXPECTED schedule for this date. Actual attendance still comes from biometrics.',
        inLabel: 'New Scheduled Time In',
        outLabel: 'New Scheduled Time Out',
    },
    official_business: {
        title: 'Approved Official Business Time',
        help: 'Approved OB time may substitute actual attendance for this date and ignores late/undertime.',
        inLabel: 'Approved OB Time In',
        outLabel: 'Approved OB Time Out',
    },
    holiday_work: {
        title: 'Approved Holiday Work Time',
        help: 'Use when holiday actual Time In/Out needs approved manual correction or proof. Regular 2.0x and Special 1.3x premiums are automatic from the Holiday Calendar.',
        inLabel: 'Approved Holiday Time In',
        outLabel: 'Approved Holiday Time Out',
    },
    overtime: {
        title: 'Approved Overtime Interval',
        help: 'Enter only the OT period. New OT requests are PENDING until Head Manager approval. Ordinary-day OT = daily rate / 8 × 125% × approved OT hours.',
        inLabel: 'OT Start Time',
        outLabel: 'OT End Time',
    },
};

export function ruleGuide(type: string): string {
    if (isDisasterType(type)) {
        const hours = disasterHours(type);
        return `Typhoon / Disaster - ${hours}hrs: applies to all payroll-active employees. A valid biometric time-in/time-out pair must complete at least ${hours} paid work hour(s), excluding the configured unpaid lunch break. Employees who meet the threshold are paid a full day and late/undertime are ignored. Employees below the threshold remain on normal attendance computation.`;
    }

    return (
        {
            sick_leave: 'Sick Leave: date range only. Paid by default; no manual time. Late and undertime are ignored.',
            medical_leave: 'Medical Leave: date range only. Paid by default; no manual time. Late and undertime are ignored.',
            change_schedule: 'Change Schedule: changes scheduled Time In/Out only. It does not create extra pay by itself.',
            offset: 'Offset / Company Compensatory Leave: use verified excess work from one or more earlier source dates as a company attendance credit on the target date. No cash addition is created. OT approval/pay remains separate; only minutes already allocated to another Offset request are unavailable.',
            official_business: 'Official Business: approved manual actual time is payable and late/undertime are ignored.',
            holiday_work: 'Holiday Work: manual approved actual Time In/Out correction/proof for a plotted holiday. Normal holiday premium is automatic from Holiday Calendar + valid attendance.',
            overtime: 'Overtime: payroll ignores automatic/raw excess time. Only an APPROVED OT adjustment is paid. Ordinary day = daily rate / 8 × 125%.',
            cash_adjustment: "Salary Adjustment: a one-time amount for this employee's cutoff pay. A positive amount adds pay; a negative amount (e.g. -1000) is deducted. Applied immediately on save. Does not affect attendance, late, or undertime.",
        }[type] ?? 'Select an adjustment type to view its payroll rule.'
    );
}

/** is_paid / ignore_late / ignore_undertime switches for a type. */
export function effectSwitches(type: string): { is_paid: SwitchState; ignore_late: SwitchState; ignore_undertime: SwitchState } {
    if (type === 'sick_leave' || type === 'medical_leave') {
        return {
            is_paid: { checked: true, disabled: false, help: 'Paid by default. Turn off only when company leave balance/policy makes this leave unpaid.' },
            ignore_late: { checked: true, disabled: true, help: 'Leave ignores late by rule.' },
            ignore_undertime: { checked: true, disabled: true, help: 'Leave ignores undertime by rule.' },
        };
    }

    if (type === 'official_business') {
        return {
            is_paid: { checked: true, disabled: true, help: 'Official Business is payable attendance.' },
            ignore_late: { checked: true, disabled: true, help: 'Official Business ignores late by rule.' },
            ignore_undertime: { checked: true, disabled: true, help: 'Official Business ignores undertime by rule.' },
        };
    }

    if (isDisasterType(type)) {
        const hours = disasterHours(type);
        return {
            is_paid: { checked: true, disabled: true, help: `Typhoon / Disaster is paid only when a valid biometric in/out pair completes at least ${hours} paid work hour(s).` },
            ignore_late: { checked: true, disabled: true, help: 'Late is ignored only after the selected disaster threshold is met.' },
            ignore_undertime: { checked: true, disabled: true, help: 'Undertime is ignored only after the selected disaster threshold is met.' },
        };
    }

    if (type === 'cash_adjustment') {
        return {
            is_paid: { checked: true, disabled: true, help: 'Salary Adjustment is always applied to pay as soon as it is saved (positive adds, negative deducts).' },
            ignore_late: { checked: false, disabled: true, help: 'Not applicable. Salary Adjustment does not touch attendance.' },
            ignore_undertime: { checked: false, disabled: true, help: 'Not applicable. Salary Adjustment does not touch attendance.' },
        };
    }

    const isHoliday = type === 'holiday_work';

    return {
        is_paid: {
            checked: false,
            disabled: true,
            help:
                type === 'offset'
                    ? 'Offset is a company attendance credit. It restores eligible shortage, creates no separate cash payment, and does not cancel separately approved OT.'
                    : type === 'overtime'
                      ? 'OT is a premium payment after approval, not a generic paid attendance adjustment.'
                      : isHoliday
                        ? 'Holiday Work is manual attendance proof/correction; holiday premium is automatic from the Holiday Calendar.'
                        : 'This adjustment does not create generic paid attendance by itself.',
        },
        ignore_late: {
            checked: isHoliday,
            disabled: true,
            help: isHoliday
                ? 'Holiday Work ignores late for the approved holiday interval.'
                : 'Late handling follows attendance unless the adjustment rule says otherwise.',
        },
        ignore_undertime: {
            checked: isHoliday,
            disabled: true,
            help: isHoliday
                ? 'Holiday Work ignores undertime for the approved holiday interval.'
                : 'Undertime handling follows attendance unless the adjustment rule says otherwise.',
        },
    };
}
