import { Link, router } from '@inertiajs/react';
import { Building2, CalendarClock, ChevronRight, Gavel, Plane, Search, UserCheck, UserCog, UserPen, Users, X } from 'lucide-react';
import { useEffect, useState, type FormEvent, type ReactNode } from 'react';
import { DashboardCard, KpiCard, SimpleBarChart, DonutChart } from '@/components/dashboard/charts';
import { DataPagination } from '@/components/data-pagination';
import { PageHeader } from '@/components/page-header';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { leaveStatusClass } from '@/lib/dashboard-tones';
import { employeeStatusClass } from '@/lib/employee-status';
import { initials } from '@/lib/format';
import type { Paginated } from '@/types';

interface Option {
    value: string;
    label: string;
}

interface Props {
    today: string;
    employees: Paginated<{ id: number; name: string; email: string | null; department: string | null; position: string | null; status: string | null; show_url: string }>;
    kpis: { total: number; active: number; active_pct: number; on_leave: number; for_action: number; offense_levels: { first: number; second: number; termination: number } };
    leaveSummary: Record<'active' | 'not_started' | 'ongoing' | 'expired_today' | 'cancelled' | 'completed', number>;
    timeline: { time: string; actor: string; action: string; kind: 'leave' | 'profile' }[];
    offences: { id: number; employee: string; level: string; status: string; when: string }[];
    employeesByDepartment: { label: string; value: number }[];
    leavesByType: { label: string; value: number }[];
    filters: { q: string; department: string; position: string; status: string; company: string };
    options: { departments: Option[]; positions: Option[]; statuses: string[]; companies: string[] };
    links: Record<'employees' | 'leaves' | 'offenses' | 'departments' | 'users', string>;
    urls: { index: string };
}

const ALL = '__all';
const number = new Intl.NumberFormat('en-US');

const LEAVE_ROWS: { key: keyof Props['leaveSummary']; label: string; color: string }[] = [
    { key: 'active', label: 'Active (approved)', color: '#10b981' },
    { key: 'ongoing', label: 'Ongoing', color: '#0ea5e9' },
    { key: 'not_started', label: 'Not started', color: '#6366f1' },
    { key: 'expired_today', label: 'Ends today', color: '#f59e0b' },
    { key: 'completed', label: 'Completed', color: '#64748b' },
    { key: 'cancelled', label: 'Cancelled', color: '#ef4444' },
];

export default function HrDashboard({ today, employees, kpis, leaveSummary, timeline, offences, employeesByDepartment, leavesByType, filters, options, links, urls }: Props) {
    const [search, setSearch] = useState(filters.q);
    useEffect(() => setSearch(filters.q), [filters.q]);

    const apply = (next: Partial<Props['filters']>) => {
        const merged = { ...filters, ...next };
        const query: Record<string, string> = {};
        if (merged.q) query.q = merged.q;
        if (merged.department) query.filter_department = merged.department;
        if (merged.position) query.filter_position = merged.position;
        if (merged.status) query.filter_status = merged.status;
        if (merged.company) query.filter_company = merged.company;
        router.get(urls.index, query, { preserveScroll: true, preserveState: true });
    };

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        apply({ q: search.trim() });
    };

    const hasFilters = Object.values(filters).some(Boolean);

    return (
        <AppLayout title="HR Dashboard">
            <PageHeader title="Human Resources" description={`Workforce overview for ${today}.`} />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <KpiCard label="Total employees" value={number.format(kpis.total)} hint="All employee records" icon={<Users />} />
                <KpiCard label="Active" value={number.format(kpis.active)} hint={`${kpis.active_pct}% of total`} icon={<UserCheck />} tone="text-emerald-600 dark:text-emerald-400" />
                <KpiCard label="On leave today" value={number.format(kpis.on_leave)} hint="Driver leaves covering today" icon={<Plane />} tone="text-amber-600 dark:text-amber-400" />
                <KpiCard
                    label="For action"
                    value={number.format(kpis.for_action)}
                    hint={`1st: ${kpis.offense_levels.first} · 2nd: ${kpis.offense_levels.second} · 3rd+: ${kpis.offense_levels.termination}`}
                    icon={<Gavel />}
                    tone="text-red-600 dark:text-red-400"
                />
            </div>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <QuickLink href={links.employees} icon={<Users />} title="Employees" hint="Manage employee records" />
                <QuickLink href={links.leaves} icon={<Plane />} title="Leave requests" hint="Approve or review leaves" />
                <QuickLink href={links.offenses} icon={<Gavel />} title="Offenses" hint="View offenses & actions" />
                <QuickLink href={links.departments} icon={<Building2 />} title="Departments" hint="Overview by department" />
                <QuickLink href={links.users} icon={<UserCog />} title="User management" hint="Create and manage users" />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <DashboardCard title="Employees by department" description="Headcount per department" className="lg:col-span-2">
                    <SimpleBarChart data={employeesByDepartment.slice(0, 12)} horizontal height={Math.max(220, Math.min(12, employeesByDepartment.length) * 30)} valueLabel="Employees" />
                </DashboardCard>
                <DashboardCard title="Driver leaves by type" description="All recorded driver leaves">
                    <DonutChart data={leavesByType.slice(0, 6)} centerLabel="Leaves" />
                </DashboardCard>
            </div>

            <div className="grid gap-4 xl:grid-cols-3">
                <DashboardCard
                    title="Employee status"
                    description={`${number.format(employees.total)} employee(s)`}
                    className="gap-0 pb-0 xl:col-span-2"
                    contentClassName="px-0 pt-4"
                    action={
                        <form onSubmit={submitSearch} className="flex gap-2">
                            <div className="relative">
                                <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input aria-label="Search employees" placeholder="Search employee..." className="w-56 pl-8" value={search} onChange={(event) => setSearch(event.target.value)} />
                            </div>
                            <Button type="submit" variant="outline">
                                Search
                            </Button>
                        </form>
                    }
                >
                    <div className="flex flex-wrap items-center gap-2 border-b px-6 pb-4">
                        <FilterSelect label="Department" allLabel="All departments" value={filters.department} options={options.departments} onChange={(department) => apply({ department })} />
                        <FilterSelect label="Position" allLabel="All positions" value={filters.position} options={options.positions} onChange={(position) => apply({ position })} />
                        <FilterSelect label="Status" allLabel="All statuses" value={filters.status} options={options.statuses.map((status) => ({ value: status, label: status }))} onChange={(status) => apply({ status })} />
                        <FilterSelect label="Company" allLabel="All companies" value={filters.company} options={options.companies.map((company) => ({ value: company, label: company }))} onChange={(company) => apply({ company })} />
                        {hasFilters && (
                            <Button variant="ghost" size="sm" onClick={() => router.get(urls.index, {}, { preserveScroll: true })}>
                                <X />
                                Clear
                            </Button>
                        )}
                    </div>
                    <Table className="min-w-[640px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Name</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead className="pr-6 text-right">Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {employees.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={4} className="py-10 text-center text-muted-foreground">
                                        No employees found.
                                    </TableCell>
                                </TableRow>
                            )}
                            {employees.data.map((employee) => (
                                <TableRow key={employee.id}>
                                    <TableCell className="pl-6">
                                        <Link href={employee.show_url} className="flex items-center gap-3 hover:underline">
                                            <Avatar className="size-8">
                                                <AvatarFallback className="text-xs">{initials(employee.name)}</AvatarFallback>
                                            </Avatar>
                                            <span className="min-w-0">
                                                <span className="block font-medium">{employee.name}</span>
                                                <span className="block text-xs text-muted-foreground">{employee.email ?? '-'}</span>
                                            </span>
                                        </Link>
                                    </TableCell>
                                    <TableCell>{employee.department ?? '-'}</TableCell>
                                    <TableCell>{employee.position ?? '-'}</TableCell>
                                    <TableCell className="pr-6 text-right">
                                        <Badge variant="outline" className={employeeStatusClass(employee.status)}>
                                            {employee.status ?? '—'}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    <div className="border-t px-6 py-3">
                        <DataPagination paginator={employees} noun="employee" />
                    </div>
                </DashboardCard>

                <div className="grid content-start gap-4">
                    <DashboardCard title="Driver leave summary" description="Status of driver leaves as of today">
                        <ul className="grid gap-2">
                            {LEAVE_ROWS.map((row) => (
                                <li key={row.key} className="flex items-center gap-2 text-sm">
                                    <span className="size-2.5 rounded-full" style={{ background: row.color }} />
                                    <span className="flex-1">{row.label}</span>
                                    <span className="font-semibold tabular-nums">{number.format(leaveSummary[row.key] ?? 0)}</span>
                                </li>
                            ))}
                        </ul>
                    </DashboardCard>

                    <DashboardCard title="Recent activity" description="Latest leave and profile updates">
                        {timeline.length === 0 && <p className="text-sm text-muted-foreground">No recent activity.</p>}
                        <ol className="relative grid gap-4 border-l pl-5">
                            {timeline.map((item, index) => (
                                <li key={index} className="relative">
                                    <span className="absolute top-0.5 -left-[27px] flex size-4 items-center justify-center rounded-full border bg-background text-muted-foreground [&_svg]:size-2.5">
                                        {item.kind === 'leave' ? <CalendarClock /> : <UserPen />}
                                    </span>
                                    <p className="text-sm font-medium">{item.actor}</p>
                                    <p className="text-sm text-muted-foreground">{item.action}</p>
                                    <p className="text-xs text-muted-foreground">{item.time}</p>
                                </li>
                            ))}
                        </ol>
                    </DashboardCard>

                    <DashboardCard title="Recent offences" description="Driver leave records with an offense level">
                        {offences.length === 0 && <p className="text-sm text-muted-foreground">No offences recorded.</p>}
                        <ul className="grid gap-2">
                            {offences.map((offence) => (
                                <li key={offence.id} className="flex items-center justify-between gap-2 rounded-md border px-3 py-2">
                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-medium">{offence.employee}</p>
                                        <p className="text-xs text-muted-foreground">{offence.when}</p>
                                    </div>
                                    <div className="flex shrink-0 gap-1">
                                        <Badge variant="outline">{offence.level}</Badge>
                                        <Badge variant="outline" className={leaveStatusClass(offence.status)}>
                                            {offence.status}
                                        </Badge>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </DashboardCard>
                </div>
            </div>
        </AppLayout>
    );
}

function QuickLink({ href, icon, title, hint }: { href: string; icon: ReactNode; title: string; hint: string }) {
    return (
        <Link href={href} className="group flex items-center gap-3 rounded-xl border bg-card p-3 shadow-xs transition-colors hover:bg-accent">
            <span className="flex size-9 shrink-0 items-center justify-center rounded-lg border bg-muted/40 text-primary [&_svg]:size-4">{icon}</span>
            <span className="min-w-0 flex-1">
                <span className="block text-sm font-medium">{title}</span>
                <span className="block truncate text-xs text-muted-foreground">{hint}</span>
            </span>
            <ChevronRight className="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
        </Link>
    );
}

function FilterSelect({ label, allLabel, value, options, onChange }: { label: string; allLabel: string; value: string; options: Option[]; onChange: (value: string) => void }) {
    return (
        <Select value={value || ALL} onValueChange={(next) => onChange(next === ALL ? '' : next)}>
            <SelectTrigger size="sm" className="w-44" aria-label={label}>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value={ALL}>{allLabel}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
