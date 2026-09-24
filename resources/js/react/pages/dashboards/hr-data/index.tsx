import { router } from '@inertiajs/react';
import { Building2, CalendarDays, UserCheck, UserRound, Users } from 'lucide-react';
import { BreakdownList, DashboardCard, DonutChart, KpiCard, SimpleBarChart } from '@/components/dashboard/charts';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { leaveStatusClass } from '@/lib/dashboard-tones';
import { cn } from '@/lib/utils';

interface Breakdown {
    label: string;
    value: number;
}

interface LeaveReport {
    label: string;
    total: number;
    total_days: number;
    by_status: Breakdown[];
    by_type: Breakdown[];
    recent: { id: number; employee: string; department: string; leave_type: string; start: string | null; end: string | null; days: number; status: string }[];
}

interface Props {
    year: number;
    years: number[];
    totals: { employees: number; active: number; other: number; departments: number };
    statusSummary: Breakdown[];
    departments: { name: string; total: number; active: number; other: number }[];
    leaveReports: LeaveReport[];
    employeeHistory: {
        id: number;
        employee_id: string;
        name: string;
        department: string;
        position: string;
        count: number;
        ir_number: string;
        title: string;
        offense: string | null;
        remarks: string;
    }[];
    holidays: { month: string; items: { id: number; name: string; date: string; moved_from: string | null; type: string }[] }[];
    urls: { index: string };
}

const number = new Intl.NumberFormat('en-US');

export default function HrDataReport({ year, years, totals, statusSummary, departments, leaveReports, employeeHistory, holidays, urls }: Props) {
    const recentLeaves = leaveReports.flatMap((report) => report.recent.map((leave) => ({ ...leave, group: report.label })));

    return (
        <AppLayout title="HR Data Report">
            <PageHeader
                title="HR Data Overview"
                description="Read-only summary of employee status, department count, leave records, violations, and holidays."
                actions={
                    <div className="grid gap-1.5">
                        <Label htmlFor="report-year" className="text-xs text-muted-foreground">
                            Report year
                        </Label>
                        <Select value={String(year)} onValueChange={(value) => router.get(urls.index, { year: value }, { preserveScroll: true })}>
                            <SelectTrigger id="report-year" className="w-32">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {years.map((option) => (
                                    <SelectItem key={option} value={String(option)}>
                                        {option}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                }
            />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <KpiCard label="Total employees" value={number.format(totals.employees)} hint="All employee records" icon={<Users />} />
                <KpiCard label="Active employees" value={number.format(totals.active)} hint="Current active manpower" icon={<UserCheck />} tone="text-emerald-600 dark:text-emerald-400" />
                <KpiCard label="Other status" value={number.format(totals.other)} hint="Resigned, inactive, hold, or others" icon={<UserRound />} tone="text-amber-600 dark:text-amber-400" />
                <KpiCard label="Departments" value={number.format(totals.departments)} hint="Departments with employee count" icon={<Building2 />} tone="text-sky-600 dark:text-sky-400" />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <DashboardCard title="Total employees per status" description="Breakdown by employee status" className="lg:col-span-2">
                    <SimpleBarChart data={statusSummary} multicolor valueLabel="Employees" />
                </DashboardCard>
                <DashboardCard title="Active vs other status" description="Current manpower health summary">
                    <DonutChart
                        centerLabel="Employees"
                        data={[
                            { label: 'Active', value: totals.active, color: '#10b981' },
                            { label: 'Other status', value: totals.other, color: '#f59e0b' },
                        ]}
                    />
                </DashboardCard>
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <DashboardCard title="Department summary" description="Total and active employees per department" className="gap-0 pb-0 lg:col-span-2" contentClassName="px-0 pt-4">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Department</TableHead>
                                <TableHead className="text-right">Total</TableHead>
                                <TableHead className="text-right">Active</TableHead>
                                <TableHead className="text-right">Other</TableHead>
                                <TableHead className="pr-6 text-right">Active rate</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {departments.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-10 text-center text-muted-foreground">
                                        No department data found.
                                    </TableCell>
                                </TableRow>
                            )}
                            {departments.map((department) => {
                                const rate = department.total > 0 ? Math.round((department.active / department.total) * 100) : 0;

                                return (
                                    <TableRow key={department.name}>
                                        <TableCell className="pl-6 font-medium">{department.name}</TableCell>
                                        <TableCell className="text-right tabular-nums">{number.format(department.total)}</TableCell>
                                        <TableCell className="text-right tabular-nums">{number.format(department.active)}</TableCell>
                                        <TableCell className="text-right tabular-nums">{number.format(department.other)}</TableCell>
                                        <TableCell className="pr-6">
                                            <div className="ml-auto flex w-32 items-center gap-2">
                                                <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-muted">
                                                    <div className="h-full rounded-full bg-emerald-500" style={{ width: `${rate}%` }} />
                                                </div>
                                                <span className="w-9 text-right text-xs tabular-nums">{rate}%</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                );
                            })}
                        </TableBody>
                    </Table>
                </DashboardCard>

                <DashboardCard title="Holiday calendar" description={`Active holidays for ${year}`} contentClassName="max-h-[480px] overflow-y-auto">
                    {holidays.length === 0 && <p className="py-6 text-center text-sm text-muted-foreground">No active holidays found for {year}.</p>}
                    <div className="grid gap-4">
                        {holidays.map((month) => (
                            <div key={month.month} className="grid gap-2">
                                <h4 className="flex items-center gap-1.5 text-sm font-semibold">
                                    <CalendarDays className="size-4 text-muted-foreground" />
                                    {month.month}
                                </h4>
                                {month.items.map((holiday) => (
                                    <div key={holiday.id} className="flex items-start justify-between gap-2 rounded-md border px-3 py-2">
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium">{holiday.name}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {holiday.date}
                                                {holiday.moved_from && ` · moved from ${holiday.moved_from}`}
                                            </p>
                                        </div>
                                        <Badge
                                            variant="outline"
                                            className={cn(
                                                'capitalize',
                                                holiday.type === 'regular' ? 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400' : 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
                                            )}
                                        >
                                            {holiday.type}
                                        </Badge>
                                    </div>
                                ))}
                            </div>
                        ))}
                    </div>
                </DashboardCard>
            </div>

            <DashboardCard title="Employee leave report" description={`Admin, driver, and conductor leave summary for ${year}`}>
                <div className="grid gap-4 lg:grid-cols-3">
                    {leaveReports.map((report) => (
                        <div key={report.label} className="grid gap-4 rounded-lg border p-4">
                            <div className="flex items-start justify-between gap-2">
                                <div>
                                    <h4 className="font-semibold">{report.label}</h4>
                                    <p className="text-xs text-muted-foreground">Total leave records: {number.format(report.total)}</p>
                                </div>
                                <Badge variant="secondary" className="tabular-nums">
                                    {number.format(report.total_days)} days
                                </Badge>
                            </div>
                            <div className="grid gap-2">
                                <p className="text-xs font-medium text-muted-foreground uppercase">By status</p>
                                <BreakdownList items={report.by_status} emptyText="No leave status data." />
                            </div>
                            <div className="grid gap-2">
                                <p className="text-xs font-medium text-muted-foreground uppercase">By leave type</p>
                                <BreakdownList items={report.by_type} emptyText="No leave type data." />
                            </div>
                        </div>
                    ))}
                </div>

                <Tabs defaultValue="recent" className="mt-6">
                    <TabsList>
                        <TabsTrigger value="recent">Recent leave records</TabsTrigger>
                        <TabsTrigger value="history">Employee history / violations</TabsTrigger>
                    </TabsList>
                    <TabsContent value="recent" className="overflow-x-auto rounded-lg border">
                        <Table className="min-w-[860px]">
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="pl-4">Group</TableHead>
                                    <TableHead>Employee</TableHead>
                                    <TableHead>Department</TableHead>
                                    <TableHead>Leave type</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead className="text-right">Days</TableHead>
                                    <TableHead className="pr-4">Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {recentLeaves.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={7} className="py-10 text-center text-muted-foreground">
                                            No recent leave records found.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {recentLeaves.map((leave) => (
                                    <TableRow key={`${leave.group}-${leave.id}`}>
                                        <TableCell className="pl-4">
                                            <Badge variant="secondary">{leave.group}</Badge>
                                        </TableCell>
                                        <TableCell className="font-medium">{leave.employee}</TableCell>
                                        <TableCell>{leave.department}</TableCell>
                                        <TableCell>{leave.leave_type}</TableCell>
                                        <TableCell className="whitespace-nowrap">
                                            {leave.start} - {leave.end}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">{number.format(leave.days)}</TableCell>
                                        <TableCell className="pr-4">
                                            <Badge variant="outline" className={leaveStatusClass(leave.status)}>
                                                {leave.status}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TabsContent>
                    <TabsContent value="history" className="overflow-x-auto rounded-lg border">
                        <Table className="min-w-[1000px]">
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="pl-4">Employee ID</TableHead>
                                    <TableHead>Employee</TableHead>
                                    <TableHead>Department</TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead className="text-right">History</TableHead>
                                    <TableHead>Latest IR</TableHead>
                                    <TableHead>Latest history</TableHead>
                                    <TableHead className="pr-4">Action / remarks</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {employeeHistory.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={8} className="py-10 text-center text-muted-foreground">
                                            No employee history or violation records found.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {employeeHistory.map((employee) => (
                                    <TableRow key={employee.id}>
                                        <TableCell className="pl-4 font-mono text-xs">{employee.employee_id}</TableCell>
                                        <TableCell className="font-medium">{employee.name}</TableCell>
                                        <TableCell>{employee.department}</TableCell>
                                        <TableCell>{employee.position}</TableCell>
                                        <TableCell className="text-right">
                                            <Badge variant="outline" className="tabular-nums">
                                                {number.format(employee.count)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="font-mono text-xs">{employee.ir_number}</TableCell>
                                        <TableCell>
                                            <div className="font-medium">{employee.title}</div>
                                            {employee.offense && <div className="text-xs text-muted-foreground">Offense: {employee.offense}</div>}
                                        </TableCell>
                                        <TableCell className="max-w-72 pr-4 text-sm whitespace-normal text-muted-foreground">{employee.remarks}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TabsContent>
                </Tabs>
            </DashboardCard>
        </AppLayout>
    );
}
