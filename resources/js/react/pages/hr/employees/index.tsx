import { router, useForm } from '@inertiajs/react';
import { Building2, Eye, Loader2, Mail, Phone, Plus, Trash2, UserCheck, UserMinus, Users, UserX } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { ModalLink } from '@/components/modal/modal-link';
import { openModal } from '@/components/modal/modal-store';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { definePage } from '@/lib/define-page';
import { employeeStatusClass, type DepartmentOption } from '@/lib/employee-status';
import { initials } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface EmployeeRow {
    id: number;
    name: string;
    employee_no: string | null;
    age: number | null;
    position: string;
    department: string;
    company: string;
    garage: string;
    email: string | null;
    phone: string | null;
    hired: string;
    tenure: string;
    status: string;
    show_url: string;
    destroy_url: string;
}

interface Filters {
    search: string;
    status: string;
    company: string;
    garage: string;
    per_page: string;
}

interface Props {
    employees: Paginated<EmployeeRow>;
    stats: { total: number; active: number; inactive: number; suspended: number; companies: number; garages: number };
    departments: DepartmentOption[];
    companies: string[];
    garages: string[];
    statusOptions: string[];
    companyOptions: string[];
    garageOptions: string[];
    filters: Filters;
    can: { create: boolean; delete: boolean };
    urls: { index: string; store: string };
}

const toOptions = (values: string[]) => values.map((value) => ({ value, label: value }));

export default definePage<Props>({
    title: () => 'Employees Directory',
    description: () => 'Employee profiles, employment status, contact details, company assignment and HR records. Click a row to open the full 201 profile.',
    actions: (props) => <Actions {...props} />,
    size: 'xl',
    Content: EmployeesIndex,
});

function Actions({ can, departments, companyOptions, garageOptions, urls }: Props) {
    const [adding, setAdding] = useState(false);
    if (!can.create) return null;

    return (
        <>
            <Button onClick={() => setAdding(true)}>
                <Plus />
                Add employee
            </Button>
            {adding && <AddEmployee departments={departments} companies={companyOptions} garages={garageOptions} url={urls.store} onClose={() => setAdding(false)} />}
        </>
    );
}

function EmployeesIndex({ employees, stats, companies, garages, statusOptions, filters, can, urls }: Props) {
    const modal = useModal();
    // The count cards are one-click status filters (other filters are kept).
    const byStatus = (status: string) => modal.get(urls.index, { ...filters, status });

    const columns: DataTableColumn<EmployeeRow>[] = [
        {
            key: 'employee',
            header: 'Employee',
            cell: (employee) => (
                <div className="flex items-center gap-3">
                    <Avatar className="size-9">
                        <AvatarFallback className="bg-primary/10 text-xs font-semibold text-primary">{initials(employee.name)}</AvatarFallback>
                    </Avatar>
                    <div className="min-w-0">
                        <div className="max-w-56 truncate font-medium" title={employee.name}>
                            {employee.name}
                        </div>
                        <div className="text-xs whitespace-nowrap text-muted-foreground">
                            {employee.employee_no ? `ID ${employee.employee_no}` : 'No ID'}
                            {employee.age !== null && ` · ${employee.age} yrs`}
                        </div>
                    </div>
                </div>
            ),
        },
        {
            key: 'position',
            header: 'Position',
            cell: (employee) => (
                <>
                    <div className="max-w-48 truncate font-medium" title={employee.position}>
                        {employee.position}
                    </div>
                    <div className="max-w-48 truncate text-xs text-muted-foreground" title={employee.department}>
                        {employee.department}
                    </div>
                </>
            ),
        },
        {
            key: 'company',
            header: 'Company',
            className: 'whitespace-nowrap',
            cell: (employee) => employee.company,
            filter: { type: 'select', param: 'company', options: toOptions(companies), placeholder: 'All company' },
        },
        {
            key: 'garage',
            header: 'Garage',
            cell: (employee) => employee.garage,
            filter: { type: 'select', param: 'garage', options: toOptions(garages), placeholder: 'All garage' },
        },
        {
            key: 'contact',
            header: 'Contact',
            hideBelow: 'xl',
            className: 'text-xs text-muted-foreground',
            cell: (employee) => (
                <div className="grid max-w-52 gap-0.5">
                    <span className={cn('flex items-center gap-1 truncate', !employee.email && 'text-amber-700 dark:text-amber-400')}>
                        <Mail className="size-3 shrink-0" />
                        <span className="truncate">{employee.email || 'No email'}</span>
                    </span>
                    <span className={cn('flex items-center gap-1', !employee.phone && 'text-amber-700 dark:text-amber-400')}>
                        <Phone className="size-3 shrink-0" />
                        {employee.phone || 'No contact number'}
                    </span>
                </div>
            ),
        },
        {
            key: 'employment',
            header: 'Hired / tenure',
            hideBelow: 'lg',
            className: 'text-xs whitespace-nowrap',
            cell: (employee) => (
                <>
                    <div>{employee.hired}</div>
                    <div className="text-muted-foreground">{employee.tenure}</div>
                </>
            ),
        },
        {
            key: 'status',
            header: 'Status',
            cell: (employee) => (
                <Badge variant="outline" className={employeeStatusClass(employee.status)}>
                    {employee.status}
                </Badge>
            ),
            filter: { type: 'select', param: 'status', options: toOptions(statusOptions), placeholder: 'All status' },
        },
    ];

    return (
        <>
            <div className="grid grid-cols-2 gap-3 lg:grid-cols-5">
                <Stat icon={<Users />} label="Total" value={stats.total} active={!filters.status} onClick={() => byStatus('')} />
                <Stat icon={<UserCheck className="text-emerald-600" />} label="Active" value={stats.active} active={filters.status === 'Active'} onClick={() => byStatus('Active')} />
                <Stat icon={<UserMinus />} label="Inactive" value={stats.inactive} active={filters.status === 'Inactive'} onClick={() => byStatus('Inactive')} />
                <Stat icon={<UserX className="text-amber-600" />} label="Suspended" value={stats.suspended} active={filters.status === 'Suspended'} onClick={() => byStatus('Suspended')} />
                <Stat icon={<Building2 className="text-sky-600" />} label="Companies" value={stats.companies} />
            </div>

            <DataTable
                title="Employee list"
                noun="employee"
                paginator={employees}
                url={urls.index}
                filters={filters}
                columns={columns}
                rowKey={(employee) => employee.id}
                searchPlaceholder="Name, ID, email, phone, company..."
                emptyText="No employees found. Try adjusting your search or filters."
                minWidth={680}
                toolbar={<PerPage value={filters.per_page} onChange={(per_page) => modal.get(urls.index, { ...filters, per_page })} />}
                onRowClick={(employee) => openModal(employee.show_url, { size: 'xl' })}
                rowActions={(employee) => (
                    <span className="flex gap-1" onClick={(event) => event.stopPropagation()}>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon" className="size-8" asChild>
                                    <ModalLink href={employee.show_url} size="xl" aria-label={`View ${employee.name}`}>
                                        <Eye />
                                    </ModalLink>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>Open 201 profile</TooltipContent>
                        </Tooltip>
                        {can.delete && (
                            <ConfirmAction
                                title="Delete this employee?"
                                description={`${employee.name} will be deleted. This cannot be undone.`}
                                confirmLabel="Delete"
                                destructive
                                onConfirm={() => router.delete(employee.destroy_url, modal.visit({ preserveScroll: true, preserveState: true }))}
                                trigger={
                                    <IconButton label={`Delete ${employee.name}`}>
                                        <Trash2 className="text-destructive" />
                                    </IconButton>
                                }
                            />
                        )}
                    </span>
                )}
            />
        </>
    );
}

function PerPage({ value, onChange }: { value: string; onChange: (value: string) => void }) {
    return (
        <div className="flex items-center gap-2 text-sm text-muted-foreground">
            Show
            <Select value={value || '10'} onValueChange={onChange}>
                <SelectTrigger size="sm" className="w-20" aria-label="Rows per page">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {['10', '25', '50', '100'].map((size) => (
                        <SelectItem key={size} value={size}>
                            {size}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}

function AddEmployee({ departments, companies, garages, url, onClose }: { departments: DepartmentOption[]; companies: string[]; garages: string[]; url: string; onClose: () => void }) {
    const modal = useModal();
    const form = useForm({ full_name: '', department_id: '', position_id: '', email: '', phone_number: '', company: '', garage: '' });
    const errors = form.errors as Record<string, string>;
    const positions = departments.find((department) => department.id === form.data.department_id)?.positions ?? [];

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(url, modal.visit({ onSuccess: onClose }));
    };

    const select = (key: 'department_id' | 'position_id' | 'company' | 'garage', options: { value: string; label: string }[], placeholder: string) => (
        <Select
            value={form.data[key]}
            onValueChange={(value) => (key === 'department_id' ? form.setData({ ...form.data, department_id: value, position_id: '' }) : form.setData(key, value))}
            disabled={key === 'position_id' && !form.data.department_id}
        >
            <SelectTrigger id={`add-${key}`} className="w-full" aria-invalid={!!errors[key]}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="sm:max-w-2xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Add employee</DialogTitle>
                        <DialogDescription>The rest of the 201 file (IDs, address, documents) is filled in from the employee's profile after saving.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField id="add-full_name" label="Full name" required error={errors.full_name} className="sm:col-span-2">
                            <Input id="add-full_name" required value={form.data.full_name} onChange={(event) => form.setData('full_name', event.target.value)} />
                        </FormField>
                        <FormField id="add-department_id" label="Department" required error={errors.department_id}>
                            {select('department_id', departments.map((department) => ({ value: department.id, label: department.name })), '-- Select department --')}
                        </FormField>
                        <FormField id="add-position_id" label="Position" required error={errors.position_id}>
                            {select('position_id', positions.map((position) => ({ value: position.id, label: position.title })), '-- Select position --')}
                        </FormField>
                        <FormField id="add-email" label="Email" error={errors.email}>
                            <Input id="add-email" type="email" value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} />
                        </FormField>
                        <FormField id="add-phone_number" label="Phone number" error={errors.phone_number} hint="11 digits, e.g. 09171234567">
                            <Input id="add-phone_number" inputMode="numeric" maxLength={11} value={form.data.phone_number} onChange={(event) => form.setData('phone_number', event.target.value.replace(/\D/g, ''))} />
                        </FormField>
                        <FormField id="add-company" label="Company" required error={errors.company}>
                            {select('company', companies.map((company) => ({ value: company, label: company })), '-- Select company --')}
                        </FormField>
                        <FormField id="add-garage" label="Garage" required error={errors.garage}>
                            {select('garage', garages.map((garage) => ({ value: garage, label: garage })), '-- Select garage --')}
                        </FormField>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing || !form.data.department_id || !form.data.position_id || !form.data.company || !form.data.garage}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Save
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function Stat({ icon, label, value, active, onClick }: { icon: ReactNode; label: string; value: number; active?: boolean; onClick?: () => void }) {
    const body = (
        <>
            <div className="flex items-center justify-between text-sm text-muted-foreground [&_svg]:size-4">
                {label}
                {icon}
            </div>
            <div className="mt-1 text-2xl font-semibold tabular-nums">{value.toLocaleString()}</div>
        </>
    );
    const className = cn('rounded-xl border bg-card p-4 text-left shadow-xs', active && 'border-primary ring-1 ring-primary');

    return onClick ? (
        <button type="button" onClick={onClick} aria-pressed={active} className={cn(className, 'transition-colors hover:bg-accent/50')}>
            {body}
        </button>
    ) : (
        <div className={className}>{body}</div>
    );
}
