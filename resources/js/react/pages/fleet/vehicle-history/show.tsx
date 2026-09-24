import { Link, router } from '@inertiajs/react';
import { ArrowLeft, CalendarDays, ClipboardList, Cog, Inbox, Search, Star } from 'lucide-react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { DataPagination } from '@/components/data-pagination';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { busStatusClass } from '@/lib/bus-status';
import type { Paginated } from '@/types';

interface RecordRow {
    id: number;
    date: string;
    number: string;
    mechanic: string;
    requested_by: string;
    job_order_no: string;
    odometer: string | number;
    purpose: string;
    remarks: string;
    creator: string;
    items: { name: string; qty: number; unit: string; part_number: string | null; remarks: string | null }[];
    show_url: string;
}

interface Props {
    bus: { plate_number: string | null; body_number: string | null; name: string | null; garage: string | null; status: string | null };
    records: Paginated<RecordRow>;
    summary: { transactions: number; parts_used: number; latest: string | null; most_used: string | null; most_used_qty: number | null };
    filters: { search: string };
    urls: { self: string; back: string };
}

export default function VehicleHistoryShow({ bus, records, summary, filters, urls }: Props) {
    const [search, setSearch] = useState(filters.search);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(urls.self, search ? { search } : {});
    };

    return (
        <AppLayout title="Vehicle Maintenance History">
            <PageHeader
                title="Vehicle Maintenance History"
                description="Active posted parts-out maintenance records only. Rolled back records are excluded."
                actions={
                    <Button variant="outline" asChild>
                        <Link href={urls.back}>
                            <ArrowLeft />
                            Back
                        </Link>
                    </Button>
                }
            />

            <Card>
                <CardContent className="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    <Info label="Plate number" value={bus.plate_number ?? 'N/A'} />
                    <Info label="Body number" value={bus.body_number ?? 'N/A'} />
                    <Info label="Bus name" value={bus.name ?? 'N/A'} />
                    <Info label="Garage" value={bus.garage ?? 'N/A'} />
                    <Info
                        label="Status"
                        value={
                            <Badge variant="outline" className={busStatusClass(bus.status)}>
                                {bus.status ?? 'N/A'}
                            </Badge>
                        }
                    />
                </CardContent>
            </Card>

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Tile icon={<ClipboardList className="text-primary" />} label="Total transactions" value={summary.transactions.toLocaleString()} hint="Posted only" />
                <Tile icon={<Cog className="text-emerald-600" />} label="Total parts used" value={summary.parts_used.toLocaleString()} hint="Rolled back excluded" />
                <Tile icon={<CalendarDays className="text-sky-600" />} label="Latest maintenance" value={summary.latest ?? 'N/A'} hint="Latest posted record" small />
                <Tile
                    icon={<Star className="text-amber-500" />}
                    label="Most used part"
                    value={summary.most_used ?? 'N/A'}
                    hint={summary.most_used_qty !== null ? `Qty used: ${summary.most_used_qty.toLocaleString()}` : 'No parts recorded'}
                    small
                />
            </div>

            <Card className="gap-0 py-0">
                <CardHeader className="flex flex-col gap-3 border-b py-4 md:flex-row md:items-center md:justify-between">
                    <div className="grid gap-1.5">
                        <CardTitle>Maintenance records</CardTitle>
                        <CardDescription>All active posted parts-out records for this vehicle.</CardDescription>
                    </div>
                    <form onSubmit={submit} className="flex gap-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input aria-label="Search records" placeholder="Search part, mechanic, JO no..." className="w-72 pl-8" value={search} onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        {filters.search && (
                            <Button type="button" variant="outline" onClick={() => router.get(urls.self)}>
                                Clear
                            </Button>
                        )}
                        <Button type="submit">Search</Button>
                    </form>
                </CardHeader>
                <CardContent className="px-0">
                    <Table className="min-w-[1500px] text-sm">
                        <TableHeader>
                            <TableRow>
                                <TableHead className="pl-6">Date</TableHead>
                                <TableHead>Reference no.</TableHead>
                                <TableHead>Mechanic</TableHead>
                                <TableHead>Requested by</TableHead>
                                <TableHead>Job order no.</TableHead>
                                <TableHead>Odometer</TableHead>
                                <TableHead>Purpose / work details</TableHead>
                                <TableHead>Parts used</TableHead>
                                <TableHead>Remarks</TableHead>
                                <TableHead className="pr-6">Created by</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {records.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={10} className="py-12">
                                        <div className="flex flex-col items-center gap-2 text-center text-muted-foreground">
                                            <Inbox className="size-8" />
                                            {filters.search ? (
                                                <>
                                                    <span>
                                                        No maintenance history found for <strong className="text-foreground">{filters.search}</strong>.
                                                    </span>
                                                    <Button variant="outline" size="sm" onClick={() => router.get(urls.self)}>
                                                        Clear search
                                                    </Button>
                                                </>
                                            ) : (
                                                'No posted maintenance history found for this vehicle.'
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )}
                            {records.data.map((record) => (
                                <TableRow key={record.id} className="align-top">
                                    <TableCell className="pl-6 whitespace-nowrap">{record.date}</TableCell>
                                    <TableCell>
                                        <Link href={record.show_url}>
                                            <Badge variant="outline" className="border-sky-300 text-sky-700 hover:underline dark:border-sky-800 dark:text-sky-400">
                                                {record.number}
                                            </Badge>
                                        </Link>
                                    </TableCell>
                                    <TableCell>{record.mechanic}</TableCell>
                                    <TableCell>{record.requested_by}</TableCell>
                                    <TableCell>{record.job_order_no}</TableCell>
                                    <TableCell className="tabular-nums">{record.odometer}</TableCell>
                                    <TableCell className="max-w-56 whitespace-normal">{record.purpose}</TableCell>
                                    <TableCell className="max-w-72 whitespace-normal">
                                        {record.items.length === 0 && <span className="text-muted-foreground">No parts recorded</span>}
                                        <ul className="grid gap-1.5">
                                            {record.items.map((item, index) => (
                                                <li key={index}>
                                                    <div className="font-medium">{item.name}</div>
                                                    <div className="text-xs text-muted-foreground">
                                                        Qty: <strong className="text-foreground">{item.qty.toLocaleString()}</strong> {item.unit}
                                                        {item.part_number && ` · Part #: ${item.part_number}`}
                                                    </div>
                                                    {item.remarks && <div className="text-xs text-muted-foreground">Note: {item.remarks}</div>}
                                                </li>
                                            ))}
                                        </ul>
                                    </TableCell>
                                    <TableCell className="max-w-52 whitespace-normal">{record.remarks}</TableCell>
                                    <TableCell className="pr-6">{record.creator}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DataPagination paginator={records} noun="record" />
        </AppLayout>
    );
}

function Info({ label, value }: { label: string; value: ReactNode }) {
    return (
        <div>
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="mt-0.5 font-semibold">{value}</div>
        </div>
    );
}

function Tile({ icon, label, value, hint, small }: { icon: ReactNode; label: string; value: string; hint: string; small?: boolean }) {
    return (
        <div className="rounded-xl border bg-card p-4 shadow-xs">
            <div className="flex items-center justify-between text-sm text-muted-foreground [&_svg]:size-4">
                {label}
                {icon}
            </div>
            <div className={small ? 'mt-1 truncate font-semibold' : 'mt-1 text-2xl font-semibold tabular-nums'} title={value}>
                {value}
            </div>
            <div className="mt-1 text-xs text-muted-foreground">{hint}</div>
        </div>
    );
}
