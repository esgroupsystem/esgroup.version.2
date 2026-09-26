import { router } from '@inertiajs/react';
import { Pencil, Plus, RefreshCw, Trash2 } from 'lucide-react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { ModalLink } from '@/components/modal/modal-link';
import { useModal } from '@/components/modal/modal-context';
import { openModal } from '@/components/modal/modal-store';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { definePage } from '@/lib/define-page';
import { initials, peso } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface ScheduledAmount {
    label: string;
    schedule: string | null;
    amount: number;
}

interface SalaryRow {
    id: number;
    name: string;
    employee_no: string | null;
    employee_biometric_id: number | null;
    rate_type: string;
    paid_day_off: boolean;
    basic_salary: number;
    ot_rate_per_hour: number;
    late_deduction_per_minute: number;
    government: ScheduledAmount[];
    allowances: ScheduledAmount[];
    is_active: boolean;
    bio_included: boolean;
    urls: { edit: string; destroy: string };
}

interface Props {
    salaries: Paginated<SalaryRow>;
    filters: { search: string; group_name: string; employment_status: string };
    groups: string[];
    can: { create: boolean; update: boolean; delete: boolean };
    urls: { index: string; create: string; sync: string };
}

const SCHEDULE_BADGE: Record<string, { label: string; className: string }> = {
    first_cutoff: { label: '2nd', className: 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400' },
    second_cutoff: { label: '1st', className: 'border-violet-300 text-violet-700 dark:border-violet-800 dark:text-violet-400' },
    every_cutoff: { label: 'Every', className: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400' },
};

const STATUS_OPTIONS = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

export default definePage<Props>({
    title: () => 'Employee Salary Rates',
    description: () => 'Salary rate, government contribution schedule, allowances, and loan deductions used by payroll generation.',
    actions: (props) => <Actions {...props} />,
    size: 'xl',
    Content: EmployeeSalariesIndex,
});

function Actions({ can, urls }: Props) {
    const modal = useModal();

    return (
        <>
            {can.update && (
                <ConfirmAction
                    title="Sync from biometrics?"
                    description="Creates default salary records for payroll-active biometric employees that do not have one yet and refreshes employee identity snapshots."
                    confirmLabel="Sync"
                    onConfirm={() => router.post(urls.sync, {}, modal.visit({ preserveScroll: true }))}
                    trigger={
                        <Button variant="outline">
                            <RefreshCw />
                            Sync from biometrics
                        </Button>
                    }
                />
            )}
            {can.create && (
                <Button asChild>
                    <ModalLink href={urls.create} mode="form">
                        <Plus />
                        Add salary
                    </ModalLink>
                </Button>
            )}
        </>
    );
}

function EmployeeSalariesIndex({ salaries, filters, groups, can, urls }: Props) {
    const modal = useModal();

    const columns: DataTableColumn<SalaryRow>[] = [
        {
            key: 'employee',
            header: 'Employee',
            cell: (salary) => (
                <div className="flex items-start gap-3">
                    <Avatar className="size-8 rounded-lg">
                        <AvatarFallback className="rounded-lg text-xs">{initials(salary.name)}</AvatarFallback>
                    </Avatar>
                    <div className="min-w-0">
                        <div className="max-w-56 truncate font-medium" title={salary.name}>
                            {salary.name}
                        </div>
                        <div className="text-xs whitespace-nowrap text-muted-foreground">
                            No. {salary.employee_no || 'N/A'} · Bio ID {salary.employee_biometric_id ?? 'N/A'}
                        </div>
                    </div>
                </div>
            ),
            filter: { type: 'select', param: 'group_name', options: groups.map((group) => ({ value: group, label: `Group ${group}` })), placeholder: 'All groups' },
        },
        {
            key: 'rate',
            header: 'Salary rate',
            className: 'text-sm',
            cell: (salary) => (
                <>
                    <div className="font-medium whitespace-nowrap tabular-nums">
                        {peso(salary.basic_salary)} <span className="text-xs font-normal text-muted-foreground">/ {salary.rate_type === 'monthly' ? 'month' : 'day'}</span>
                    </div>
                    <div className="text-xs whitespace-nowrap text-muted-foreground tabular-nums">
                        OT {peso(salary.ot_rate_per_hour)}/hr · Late ₱ {salary.late_deduction_per_minute.toFixed(4)}/min
                    </div>
                    <div className={cn('text-xs whitespace-nowrap', salary.paid_day_off ? 'text-emerald-700 dark:text-emerald-400' : 'text-muted-foreground')}>
                        Day off: {salary.paid_day_off ? 'Paid' : 'Not paid'}
                    </div>
                </>
            ),
        },
        {
            key: 'government',
            header: 'Government / month',
            hideBelow: 'lg',
            cell: (salary) => <ScheduledList items={salary.government} />,
        },
        {
            key: 'allowances',
            header: 'Allowances',
            hideBelow: 'xl',
            cell: (salary) => <ScheduledList items={salary.allowances} />,
        },
        {
            key: 'status',
            header: 'Payroll status',
            cell: (salary) => (
                <div className="flex flex-col items-start gap-1">
                    <Badge variant={salary.is_active ? 'secondary' : 'outline'}>{salary.is_active ? 'Salary active' : 'Salary inactive'}</Badge>
                    <Badge variant="outline" className={cn(!salary.bio_included && 'text-destructive')}>
                        {salary.bio_included ? 'Bio included' : 'Bio excluded'}
                    </Badge>
                </div>
            ),
            filter: { type: 'select', param: 'employment_status', options: STATUS_OPTIONS, placeholder: 'All employees' },
        },
    ];

    return (
        <DataTable
            title="Salary records"
            description={`${salaries.total.toLocaleString()} employee${salaries.total === 1 ? '' : 's'} · schedule tags: 1st = 26-10, 2nd = 11-25`}
            noun="employee"
            paginator={salaries}
            url={urls.index}
            filters={filters}
            columns={columns}
            rowKey={(salary) => salary.id}
            searchPlaceholder="Employee name, no., or biometric ID..."
            emptyText="No salary records found."
            minWidth={720}
            onRowClick={can.update ? (salary) => openModal(salary.urls.edit, { mode: 'form' }) : undefined}
            rowActions={
                can.update || can.delete
                    ? (salary) => (
                          // Stop clicks here from also opening the row.
                          <span className="flex gap-1" onClick={(event) => event.stopPropagation()}>
                              {can.update && (
                                  <Tooltip>
                                      <TooltipTrigger asChild>
                                          <Button variant="ghost" size="icon" className="size-8" asChild>
                                              <ModalLink href={salary.urls.edit} mode="form" aria-label={`Edit ${salary.name}`}>
                                                  <Pencil />
                                              </ModalLink>
                                          </Button>
                                      </TooltipTrigger>
                                      <TooltipContent>Edit salary</TooltipContent>
                                  </Tooltip>
                              )}
                              {can.delete && (
                                  <ConfirmAction
                                      title="Delete salary record?"
                                      description="Delete this employee salary record? This action cannot be undone."
                                      confirmLabel="Delete"
                                      destructive
                                      onConfirm={() => router.delete(salary.urls.destroy, modal.visit({ preserveScroll: true, preserveState: true }))}
                                      trigger={
                                          <IconButton label="Delete salary record">
                                              <Trash2 className="text-destructive" />
                                          </IconButton>
                                      }
                                  />
                              )}
                          </span>
                      )
                    : undefined
            }
        />
    );
}

function ScheduledList({ items }: { items: ScheduledAmount[] }) {
    return (
        <div className="grid gap-1 text-xs">
            {items.map((item) => {
                const badge = SCHEDULE_BADGE[item.schedule ?? ''] ?? { label: 'None', className: 'text-muted-foreground' };

                return (
                    <div key={item.label} className="flex items-center gap-2 whitespace-nowrap">
                        <span className="w-16 text-muted-foreground">{item.label}</span>
                        <Badge variant="outline" className={cn('w-12 justify-center px-1', badge.className)}>
                            {badge.label}
                        </Badge>
                        <span className="tabular-nums">{peso(item.amount)}</span>
                    </div>
                );
            })}
        </div>
    );
}
