import { Link } from '@inertiajs/react';
import { CheckCircle2, Clock, Hourglass, Plus, Ticket, Wrench } from 'lucide-react';
import { BreakdownList, DashboardCard, KpiCard, SeriesChart, SimpleBarChart } from '@/components/dashboard/charts';
import { PageHeader } from '@/components/page-header';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { initials } from '@/lib/format';
import { ticketStatusClass } from '@/lib/it-status';

interface Props {
    stats: { new: number; pending: number; progress: number; completed: number };
    weekly: { labels: string[]; created: number[]; pending: number[]; progress: number[]; completed: number[] };
    categories: { label: string; value: number }[];
    agents: { id: number; name: string; role: string | null; assigned: number }[];
    unresolved: { id: number; bus: string; issue: string; requester: string; assignee: string | null; status: string; age: string | null; url: string }[];
    urls: { tickets: string; create: string | null };
}

const number = new Intl.NumberFormat('en-US');

export default function ItDashboard({ stats, weekly, categories, agents, unresolved, urls }: Props) {
    const total = stats.pending + stats.progress + stats.completed;
    const completionRate = total > 0 ? Math.round((stats.completed / total) * 100) : 0;

    return (
        <AppLayout title="IT Dashboard">
            <PageHeader
                title="IT Department"
                description="Job order tickets at a glance: today's intake, open work, and team workload."
                actions={
                    <>
                        <Button variant="outline" asChild>
                            <Link href={urls.tickets}>
                                <Ticket />
                                All tickets
                            </Link>
                        </Button>
                        {urls.create && (
                            <Button asChild>
                                <Link href={urls.create}>
                                    <Plus />
                                    New ticket
                                </Link>
                            </Button>
                        )}
                    </>
                }
            />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <KpiCard label="New today" value={number.format(stats.new)} hint="Tickets filed today" icon={<Ticket />} />
                <KpiCard label="Pending" value={number.format(stats.pending)} hint="Waiting for approval or pickup" icon={<Hourglass />} tone="text-amber-600 dark:text-amber-400" />
                <KpiCard label="In progress" value={number.format(stats.progress)} hint="Currently being worked on" icon={<Wrench />} tone="text-sky-600 dark:text-sky-400" />
                <KpiCard label="Completed" value={number.format(stats.completed)} hint={`${completionRate}% of all tickets`} icon={<CheckCircle2 />} tone="text-emerald-600 dark:text-emerald-400" />
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <DashboardCard title="Tickets per week" description="Last 6 weeks, by date filed and current status" className="lg:col-span-2">
                    <SeriesChart
                        categories={weekly.labels}
                        series={[
                            { key: 'pending', label: 'Pending', values: weekly.pending, color: '#f59e0b' },
                            { key: 'progress', label: 'In progress', values: weekly.progress, color: '#0ea5e9' },
                            { key: 'completed', label: 'Completed', values: weekly.completed, color: '#10b981' },
                        ]}
                        stacked
                    />
                </DashboardCard>
                <DashboardCard title="Issues by category" description="All tickets by concern type">
                    <SimpleBarChart data={categories.filter((category) => category.value > 0)} horizontal multicolor height={Math.max(220, categories.filter((category) => category.value > 0).length * 30)} valueLabel="Tickets" />
                </DashboardCard>
            </div>

            <div className="grid gap-4 lg:grid-cols-3">
                <DashboardCard title="Unresolved tickets" description="Latest pending and in-progress tickets" className="gap-0 pb-0 lg:col-span-2" contentClassName="px-0 pt-4">
                    <Table className="min-w-[720px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Bus</TableHead>
                                <TableHead>Issue</TableHead>
                                <TableHead>Requester</TableHead>
                                <TableHead>Assigned</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="pr-6 text-right">Filed</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {unresolved.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-10 text-center text-muted-foreground">
                                        No unresolved tickets. Nice work!
                                    </TableCell>
                                </TableRow>
                            )}
                            {unresolved.map((ticket) => (
                                <TableRow key={ticket.id}>
                                    <TableCell className="pl-6">
                                        <Link href={ticket.url} className="font-medium hover:underline">
                                            {ticket.bus}
                                        </Link>
                                    </TableCell>
                                    <TableCell className="text-xs font-medium">{ticket.issue}</TableCell>
                                    <TableCell>{ticket.requester}</TableCell>
                                    <TableCell className="text-muted-foreground">{ticket.assignee || 'Unassigned'}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" className={ticketStatusClass(ticket.status)}>
                                            {ticket.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="pr-6 text-right text-xs whitespace-nowrap text-muted-foreground">
                                        <span className="inline-flex items-center gap-1">
                                            <Clock className="size-3" />
                                            {ticket.age ?? '-'}
                                        </span>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </DashboardCard>

                <div className="grid content-start gap-4">
                    <DashboardCard title="Ticket status" description={`${number.format(total)} ticket(s) in total`}>
                        <BreakdownList
                            items={[
                                { label: 'Pending', value: stats.pending, color: '#f59e0b' },
                                { label: 'In progress', value: stats.progress, color: '#0ea5e9' },
                                { label: 'Completed', value: stats.completed, color: '#10b981' },
                            ]}
                        />
                    </DashboardCard>
                    <DashboardCard title="Team workload" description="Tickets assigned per IT staff">
                        {agents.length === 0 && <p className="text-sm text-muted-foreground">No IT staff accounts found.</p>}
                        <ul className="grid gap-3">
                            {agents.map((agent) => (
                                <li key={agent.id} className="flex items-center gap-3">
                                    <Avatar className="size-8">
                                        <AvatarFallback className="text-xs">{initials(agent.name)}</AvatarFallback>
                                    </Avatar>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-sm font-medium">{agent.name}</p>
                                        <p className="text-xs text-muted-foreground">{agent.role ?? 'IT'}</p>
                                    </div>
                                    <Badge variant="secondary" className="tabular-nums">
                                        {number.format(agent.assigned)}
                                    </Badge>
                                </li>
                            ))}
                        </ul>
                    </DashboardCard>
                </div>
            </div>
        </AppLayout>
    );
}
