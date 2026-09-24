import { Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    ChevronLeft,
    ChevronRight,
    ClipboardList,
    Eye,
    FileText,
    FolderOpen,
    Hash,
    History,
    IdCard,
    Paperclip,
    Pencil,
    Plus,
    Printer,
    QrCode,
    ShieldAlert,
    Trash2,
    TriangleAlert,
    Upload,
    User,
} from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import {
    Edit201Dialog,
    EditProfileDialog,
    StatusDetailsDialog,
    UploadAttachmentDialog,
    type AssetFile,
    type AssetNumber,
    type ProfileValues,
    type StatusDetailValues,
} from '@/components/hr/profile-dialogs';
import { emptyViolation, ViolationDialog, type OffenseOption, type ViolationValues } from '@/components/hr/violation-dialog';
import { useModal } from '@/components/modal/modal-context';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { definePage } from '@/lib/define-page';
import { employeeStatusClass, type DepartmentOption } from '@/lib/employee-status';
import { initials, peso } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

interface Employee {
    id: number;
    name: string;
    position: string;
    department: string;
    status: string;
    employee_id_permanent: string | null;
    photo_url: string | null;
    qr_svg: string | null;
    hired: string;
    tenure: string;
    age: string;
    address_1: string | null;
    address_2: string | null;
    emergency_name: string | null;
    emergency_contact: string | null;
    email: string | null;
    phone_number: string | null;
    company: string | null;
    garage: string | null;
}

interface IrGroup {
    id: number;
    ir_number: string;
    count: number;
    actions: string[];
    remarks: string[];
    recorded: string;
    updated: string;
    records: { offense_id: string; section: string; type: string | null; description: string }[];
    sda_amount: number | null;
    sda_terms: number | null;
    sda_start: string;
    sda_end: string;
    suspension_start: string;
    suspension_end: string;
    values: ViolationValues;
    update_url: string;
    destroy_url: string;
}

interface Log {
    id: number;
    label: string;
    tone: string;
    actor: string;
    date: string | null;
    time: string | null;
    changes: { field: string; from: string; to: string }[];
}

interface Props {
    employee: Employee;
    profileValues: ProfileValues;
    assets: { updated: string | null; numbers: AssetNumber[]; files: AssetFile[] };
    statusDetails: StatusDetailValues;
    attachments: { id: number; name: string; meta: string; download_url: string; destroy_url: string }[];
    irGroups: IrGroup[];
    irStats: { irs: number; violations: number; sda: number; suspension: number; final_warning: number; remarks: number };
    logs: Paginated<Log>;
    departments: DepartmentOption[];
    offenses: OffenseOption[];
    options: { statuses: string[]; companies: string[]; garages: string[]; statusTypes: string[]; actions: string[] };
    can: { update: boolean };
    urls: { back: string; show: string; print: string; update: string; assets: string; statusDetails: string; attachments: string; historyStore: string; checkPermanentId: string };
}

type EditDialog = 'profile' | '201' | 'status' | 'attachment' | 'addIr' | null;
type Tab = 'overview' | 'ids' | 'status' | 'violations' | 'attachments' | 'activity';

const TONE: Record<string, string> = {
    success: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
    primary: 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400',
    info: 'border-cyan-300 text-cyan-700 dark:border-cyan-800 dark:text-cyan-400',
    warning: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    danger: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    secondary: 'text-muted-foreground',
};

const fmt = (value: string) => (value ? new Date(`${value}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '');
const filled = (value: string | null | undefined) => Boolean(value && String(value).trim() && value !== '—');

export default definePage<Props>({
    title: ({ employee }) => employee.name,
    description: ({ employee }) => (
        <span className="flex flex-wrap items-center gap-x-2 gap-y-1">
            <span>{employee.position}</span>
            <span>·</span>
            <span>{employee.department}</span>
            <Badge variant="outline" className={employeeStatusClass(employee.status)}>
                {employee.status}
            </Badge>
        </span>
    ),
    actions: (props) => <HeaderActions {...props} />,
    size: 'xl',
    Content: EmployeeProfile,
});

function HeaderActions({ urls }: Props) {
    const modal = useModal();

    return (
        <>
            <Button variant="outline" size={modal.inModal ? 'sm' : 'default'} asChild>
                <a href={urls.print} target="_blank" rel="noopener">
                    <Printer />
                    Print 201 (PDF)
                </a>
            </Button>
            {!modal.inModal && (
                <Button variant="outline" asChild>
                    <Link href={urls.back}>
                        <ArrowLeft />
                        Back
                    </Link>
                </Button>
            )}
        </>
    );
}

function EmployeeProfile(props: Props) {
    const { employee, profileValues, assets, statusDetails, attachments, irGroups, irStats, logs, can, urls } = props;
    const [dialog, setDialog] = useState<EditDialog>(null);
    const [tab, setTab] = useState<Tab>('overview');
    const [viewing, setViewing] = useState<IrGroup | null>(null);
    const [editing, setEditing] = useState<IrGroup | null>(null);
    const close = () => setDialog(null);

    // Every field HR is expected to fill, with where to fix it. Drives the completeness bar.
    const checklist: { label: string; ok: boolean; tab: Tab; edit: EditDialog }[] = [
        { label: 'Permanent ID', ok: filled(employee.employee_id_permanent), tab: 'overview', edit: 'profile' },
        { label: 'Date of birth', ok: filled(profileValues.date_of_birth), tab: 'overview', edit: 'profile' },
        { label: 'Date hired', ok: filled(profileValues.date_hired), tab: 'overview', edit: 'profile' },
        { label: 'Email', ok: filled(employee.email), tab: 'overview', edit: 'profile' },
        { label: 'Phone number', ok: filled(employee.phone_number), tab: 'overview', edit: 'profile' },
        { label: 'Address', ok: filled(employee.address_1), tab: 'overview', edit: 'profile' },
        { label: 'Emergency contact', ok: filled(employee.emergency_name) && filled(employee.emergency_contact), tab: 'overview', edit: 'profile' },
        { label: 'Company', ok: filled(employee.company), tab: 'overview', edit: 'profile' },
        { label: 'Garage', ok: filled(employee.garage), tab: 'overview', edit: 'profile' },
        ...assets.numbers.map((number) => ({ label: number.label, ok: filled(number.value), tab: 'ids' as Tab, edit: '201' as EditDialog })),
        ...assets.files.map((file) => ({ label: file.label, ok: Boolean(file.url), tab: 'ids' as Tab, edit: '201' as EditDialog })),
    ];
    const missing = checklist.filter((item) => !item.ok);
    const percent = Math.round(((checklist.length - missing.length) / checklist.length) * 100);
    const idsMissing = missing.filter((item) => item.tab === 'ids').length;
    const overviewMissing = missing.filter((item) => item.tab === 'overview').length;

    return (
        <>
            {/* Summary band: who, key facts, and what is still missing. */}
            <Card className="gap-0 py-0">
                <div className="grid gap-5 p-5 md:grid-cols-[auto_minmax(0,1fr)_auto] md:items-center">
                    <div className="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border bg-muted text-2xl font-semibold">
                        {employee.photo_url ? <img src={employee.photo_url} alt={employee.name} className="size-full object-cover" /> : initials(employee.name)}
                    </div>
                    <div className="grid min-w-0 gap-3">
                        <div className="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
                            <Fact label="Hired" value={employee.hired} />
                            <Fact label="Tenure" value={employee.tenure} />
                            <Fact label="Age" value={employee.age} />
                            <Fact label="Company / garage" value={[employee.company, employee.garage].filter(Boolean).join(' · ')} />
                        </div>
                        <div className="grid gap-1.5">
                            <div className="flex items-center justify-between gap-2 text-xs">
                                <span className="font-medium">Profile completeness</span>
                                <span className={cn('font-semibold tabular-nums', percent === 100 ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400')}>
                                    {checklist.length - missing.length} / {checklist.length} · {percent}%
                                </span>
                            </div>
                            <div className="h-2 overflow-hidden rounded-full bg-muted">
                                <div className={cn('h-full rounded-full', percent === 100 ? 'bg-emerald-500' : 'bg-amber-500')} style={{ width: `${percent}%` }} />
                            </div>
                            {missing.length > 0 && (
                                <div className="flex flex-wrap items-center gap-1 text-xs">
                                    <span className="text-muted-foreground">Missing:</span>
                                    {missing.map((item) => (
                                        <button
                                            key={item.label}
                                            type="button"
                                            onClick={() => (can.update ? setDialog(item.edit) : setTab(item.tab))}
                                            className="rounded-md border border-amber-300 px-1.5 py-0.5 text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-400 dark:hover:bg-amber-950/40"
                                            title={can.update ? `Fill in ${item.label}` : `Go to ${item.label}`}
                                        >
                                            {item.label}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                        {can.update && (
                            <div className="flex flex-wrap gap-2">
                                <Button size="sm" onClick={() => setDialog('profile')}>
                                    <Pencil />
                                    Edit profile
                                </Button>
                                <Button size="sm" variant="outline" onClick={() => setDialog('201')}>
                                    <FolderOpen />
                                    Edit 201 file
                                </Button>
                            </div>
                        )}
                    </div>
                    <div className="flex flex-col items-center gap-1 rounded-xl border p-3 text-center">
                        {employee.qr_svg ? (
                            <>
                                <div className="rounded bg-white p-1 [&_svg]:size-24" dangerouslySetInnerHTML={{ __html: employee.qr_svg }} />
                                <div className="text-xs text-muted-foreground">Permanent ID</div>
                                <div className="font-mono text-sm font-semibold">{employee.employee_id_permanent}</div>
                            </>
                        ) : (
                            <div className="flex items-center gap-1 px-2 py-6 text-xs text-muted-foreground">
                                <QrCode className="size-4" />
                                No permanent ID
                            </div>
                        )}
                    </div>
                </div>
            </Card>

            <Tabs value={tab} onValueChange={(value) => setTab(value as Tab)} className="gap-4">
                <div className="overflow-x-auto">
                    <TabsList>
                        <TabsTrigger value="overview">
                            <User />
                            Overview
                            <CountBadge value={overviewMissing} warn />
                        </TabsTrigger>
                        <TabsTrigger value="ids">
                            <IdCard />
                            Gov't IDs & files
                            <CountBadge value={idsMissing} warn />
                        </TabsTrigger>
                        <TabsTrigger value="status">
                            <ClipboardList />
                            Separation status
                        </TabsTrigger>
                        <TabsTrigger value="violations">
                            <ShieldAlert />
                            Violations
                            <CountBadge value={irStats.irs} />
                        </TabsTrigger>
                        <TabsTrigger value="attachments">
                            <Paperclip />
                            Attachments
                            <CountBadge value={attachments.length} />
                        </TabsTrigger>
                        <TabsTrigger value="activity">
                            <History />
                            Activity log
                            <CountBadge value={logs.total} />
                        </TabsTrigger>
                    </TabsList>
                </div>

                {/* ---------------- Overview: every profile field on its own labelled row. */}
                <TabsContent value="overview" className="grid gap-4 lg:grid-cols-2">
                    <Section title="Employment" onEdit={can.update ? () => setDialog('profile') : undefined}>
                        <Field label="Permanent ID" value={employee.employee_id_permanent} mono />
                        <Field label="Position" value={employee.position} />
                        <Field label="Department" value={employee.department} />
                        <Field
                            label="Status"
                            value={
                                <Badge variant="outline" className={employeeStatusClass(employee.status)}>
                                    {employee.status}
                                </Badge>
                            }
                        />
                        <Field label="Date hired" value={fmt(profileValues.date_hired)} />
                        <Field label="Tenure" value={employee.tenure} />
                    </Section>
                    <Section title="Personal" onEdit={can.update ? () => setDialog('profile') : undefined}>
                        <Field label="Full name" value={employee.name} />
                        <Field label="Date of birth" value={fmt(profileValues.date_of_birth)} />
                        <Field label="Age" value={employee.age} />
                    </Section>
                    <Section title="Contact & address" onEdit={can.update ? () => setDialog('profile') : undefined}>
                        <Field label="Email" value={employee.email} />
                        <Field label="Phone number" value={employee.phone_number} mono />
                        <Field label="Address 1" value={employee.address_1} />
                        <Field label="Address 2" value={employee.address_2} optional />
                    </Section>
                    <div className="grid content-start gap-4">
                        <Section title="Emergency contact" onEdit={can.update ? () => setDialog('profile') : undefined}>
                            <Field label="Name" value={employee.emergency_name} />
                            <Field label="Contact number" value={employee.emergency_contact} mono />
                        </Section>
                        <Section title="Company assignment" onEdit={can.update ? () => setDialog('profile') : undefined}>
                            <Field label="Company" value={employee.company} />
                            <Field label="Garage" value={employee.garage} />
                        </Section>
                    </div>
                </TabsContent>

                {/* ---------------- Government numbers and 201 documents. */}
                <TabsContent value="ids" className="grid gap-4 lg:grid-cols-2">
                    <Section title="Government numbers" description={`Last updated ${assets.updated ?? '—'}`} onEdit={can.update ? () => setDialog('201') : undefined}>
                        {assets.numbers.map((number) => (
                            <Field key={number.key} label={number.label} value={number.value} hint={number.date ? `Updated ${number.date}` : undefined} mono />
                        ))}
                    </Section>
                    <Section title="201 documents" onEdit={can.update ? () => setDialog('201') : undefined}>
                        {assets.files.map((file) => (
                            <Field
                                key={file.key}
                                label={file.label}
                                value={
                                    file.url ? (
                                        <span className="flex items-center justify-end gap-2">
                                            {file.date && <span className="text-xs font-normal text-muted-foreground">{file.date}</span>}
                                            <Button variant="outline" size="sm" asChild>
                                                <a href={file.url} target="_blank" rel="noopener">
                                                    <Eye />
                                                    View
                                                </a>
                                            </Button>
                                        </span>
                                    ) : null
                                }
                                emptyText="Not uploaded"
                            />
                        ))}
                    </Section>
                </TabsContent>

                {/* ---------------- Resignation / termination details. */}
                <TabsContent value="status">
                    <Section title="Separation status" description="Resignation, termination or retrenchment details and last pay." onEdit={can.update ? () => setDialog('status') : undefined}>
                        <Field
                            label="Type of status"
                            value={
                                statusDetails.type_of_status ? (
                                    <Badge variant="outline" className={statusDetails.type_of_status.startsWith('Terminated') ? TONE.danger : TONE.secondary}>
                                        {statusDetails.type_of_status}
                                    </Badge>
                                ) : null
                            }
                            optional
                        />
                        <Field label="Date of status" value={fmt(statusDetails.date_resigned)} optional />
                        <Field label="Last duty" value={fmt(statusDetails.last_duty)} optional />
                        <Field label="Clearance date" value={fmt(statusDetails.clearance_date)} optional />
                        <Field
                            label="Last pay"
                            value={
                                statusDetails.last_pay_status ? (
                                    <span className="flex items-center justify-end gap-2">
                                        {statusDetails.last_pay_date && <span className="text-xs font-normal text-muted-foreground">{fmt(statusDetails.last_pay_date)}</span>}
                                        <Badge variant="outline" className={statusDetails.last_pay_status === 'Released' ? TONE.success : TONE.warning}>
                                            {statusDetails.last_pay_status}
                                        </Badge>
                                    </span>
                                ) : null
                            }
                            optional
                        />
                    </Section>
                </TabsContent>

                {/* ---------------- Incident reports grouped by IR number. */}
                <TabsContent value="violations" className="grid gap-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="grid flex-1 grid-cols-3 gap-2 sm:grid-cols-6">
                            <MiniStat label="IR cases" value={irStats.irs} />
                            <MiniStat label="Violations" value={irStats.violations} tone="text-red-600" />
                            <MiniStat label="SDA" value={irStats.sda} tone="text-amber-600" />
                            <MiniStat label="Suspension" value={irStats.suspension} tone="text-red-600" />
                            <MiniStat label="Final warning" value={irStats.final_warning} />
                            <MiniStat label="With remarks" value={irStats.remarks} tone="text-sky-600" />
                        </div>
                        {can.update && (
                            <Button onClick={() => setDialog('addIr')}>
                                <Plus />
                                Add violation
                            </Button>
                        )}
                    </div>
                    {irGroups.length === 0 && <Empty icon={<ShieldAlert />} text="No violation records found." />}
                    {irGroups.map((group) => (
                        <IrCard
                            key={group.id}
                            group={group}
                            canUpdate={can.update}
                            onView={() => setViewing(group)}
                            onEdit={() => setEditing(group)}
                        />
                    ))}
                </TabsContent>

                {/* ---------------- Uploaded files. */}
                <TabsContent value="attachments">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between gap-2">
                            <CardTitle>Attachments</CardTitle>
                            {can.update && (
                                <Button variant="outline" size="sm" onClick={() => setDialog('attachment')}>
                                    <Upload />
                                    Upload
                                </Button>
                            )}
                        </CardHeader>
                        <CardContent className="grid gap-2 sm:grid-cols-2">
                            {attachments.length === 0 && <p className="text-sm text-muted-foreground sm:col-span-2">No attachments.</p>}
                            {attachments.map((attachment) => (
                                <div key={attachment.id} className="flex items-center gap-2 rounded-lg border p-2.5">
                                    <FileText className="size-4 shrink-0 text-muted-foreground" />
                                    <div className="min-w-0 flex-1">
                                        <a href={attachment.download_url} target="_blank" rel="noopener" className="block truncate text-sm font-medium hover:underline" title={attachment.name}>
                                            {attachment.name}
                                        </a>
                                        <div className="text-xs text-muted-foreground">{attachment.meta}</div>
                                    </div>
                                    {can.update && <RemoveAttachment name={attachment.name} url={attachment.destroy_url} />}
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </TabsContent>

                {/* ---------------- Change history. */}
                <TabsContent value="activity">
                    <ActivityLog logs={logs} url={urls.show} />
                </TabsContent>
            </Tabs>

            <EditProfileDialog
                open={dialog === 'profile'}
                onClose={close}
                values={profileValues}
                employeeId={employee.id}
                photoUrl={employee.photo_url}
                departments={props.departments}
                statuses={props.options.statuses}
                companies={props.options.companies}
                garages={props.options.garages}
                url={urls.update}
                checkUrl={urls.checkPermanentId}
            />
            <Edit201Dialog open={dialog === '201'} onClose={close} numbers={assets.numbers} files={assets.files} url={urls.assets} />
            <StatusDetailsDialog open={dialog === 'status'} onClose={close} values={statusDetails} statusTypes={props.options.statusTypes} url={urls.statusDetails} />
            <UploadAttachmentDialog open={dialog === 'attachment'} onClose={close} url={urls.attachments} />
            {dialog === 'addIr' && <ViolationDialog title="Add violation" values={emptyViolation()} offenses={props.offenses} actions={props.options.actions} url={urls.historyStore} method="post" onClose={close} />}
            {editing && <ViolationDialog title={`Edit ${editing.ir_number}`} values={editing.values} offenses={props.offenses} actions={props.options.actions} url={editing.update_url} method="put" onClose={() => setEditing(null)} />}
            {viewing && (
                <IrDetails
                    group={viewing}
                    onClose={() => setViewing(null)}
                    onEdit={
                        can.update
                            ? () => {
                                  setEditing(viewing);
                                  setViewing(null);
                              }
                            : undefined
                    }
                />
            )}
        </>
    );
}

/* ------------------------------------------------------------------ pieces */

function Fact({ label, value }: { label: string; value: string }) {
    return (
        <div className="min-w-0">
            <div className="text-xs text-muted-foreground">{label}</div>
            <div className="truncate font-medium" title={value}>
                {value || '—'}
            </div>
        </div>
    );
}

function CountBadge({ value, warn }: { value: number; warn?: boolean }) {
    if (!value) return null;

    return (
        <span
            className={cn(
                'ml-0.5 rounded-full px-1.5 text-[10px] font-semibold tabular-nums',
                warn ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-muted-foreground/15 text-foreground',
            )}
            title={warn ? `${value} missing` : undefined}
        >
            {value}
        </span>
    );
}

function Section({ title, description, onEdit, children }: { title: string; description?: string; onEdit?: () => void; children: ReactNode }) {
    return (
        <Card className="gap-2">
            <CardHeader className="flex flex-row items-start justify-between gap-2">
                <div className="grid gap-1">
                    <CardTitle className="text-base">{title}</CardTitle>
                    {description && <CardDescription>{description}</CardDescription>}
                </div>
                {onEdit && (
                    <Button variant="ghost" size="sm" className="-mt-1 h-7" onClick={onEdit}>
                        <Pencil />
                        Edit
                    </Button>
                )}
            </CardHeader>
            <CardContent>
                <dl className="divide-y">{children}</dl>
            </CardContent>
        </Card>
    );
}

/** One labelled value. Empty required values are flagged so they are not overlooked. */
function Field({
    label,
    value,
    hint,
    mono,
    optional,
    emptyText,
}: {
    label: string;
    value: ReactNode;
    hint?: string;
    mono?: boolean;
    /** Empty is normal (e.g. no resignation yet): show a dash instead of a warning. */
    optional?: boolean;
    emptyText?: string;
}) {
    const empty = value === null || value === undefined || (typeof value === 'string' && (!value.trim() || value === '—'));

    return (
        <div className="grid grid-cols-[minmax(0,10rem)_minmax(0,1fr)] items-center gap-3 py-2.5 text-sm">
            <dt className="text-muted-foreground">{label}</dt>
            <dd className="min-w-0 text-right">
                {empty ? (
                    optional ? (
                        <span className="text-muted-foreground">—</span>
                    ) : (
                        <span className="inline-flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                            <TriangleAlert className="size-3.5" />
                            {emptyText ?? 'Not set'}
                        </span>
                    )
                ) : (
                    <>
                        <div className={cn('font-medium break-words', mono && 'font-mono')}>{value}</div>
                        {hint && <div className="text-xs text-muted-foreground">{hint}</div>}
                    </>
                )}
            </dd>
        </div>
    );
}

function MiniStat({ label, value, tone }: { label: string; value: number; tone?: string }) {
    return (
        <div className="rounded-lg border bg-card p-2 text-center">
            <div className={cn('text-lg font-semibold tabular-nums', value > 0 && tone)}>{value}</div>
            <div className="text-xs text-muted-foreground">{label}</div>
        </div>
    );
}

function Empty({ icon, text }: { icon: ReactNode; text: string }) {
    return (
        <div className="flex flex-col items-center gap-1 rounded-xl border border-dashed py-10 text-center text-sm text-muted-foreground [&_svg]:size-8">
            {icon}
            {text}
        </div>
    );
}

function IrCard({ group, canUpdate, onView, onEdit }: { group: IrGroup; canUpdate: boolean; onView: () => void; onEdit: () => void }) {
    const modal = useModal();
    const sda = group.actions.includes('Salary Deduction Authorization');
    const suspension = group.actions.includes('Suspension');

    return (
        <Card className="gap-3">
            <CardHeader className="flex flex-row flex-wrap items-start justify-between gap-2">
                <div className="grid gap-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="flex items-center gap-1 font-semibold">
                            <Hash className="size-4 text-primary" />
                            {group.ir_number}
                        </span>
                        <Badge variant="secondary">
                            {group.count} violation{group.count === 1 ? '' : 's'}
                        </Badge>
                        {sda && (
                            <Badge variant="outline" className={TONE.warning}>
                                SDA
                            </Badge>
                        )}
                        {suspension && (
                            <Badge variant="outline" className={TONE.danger}>
                                Suspension
                            </Badge>
                        )}
                        {group.actions.includes('Final Warning') && <Badge variant="outline">Final warning</Badge>}
                    </div>
                    <span className="text-xs text-muted-foreground">
                        Recorded {group.recorded} · Updated {group.updated}
                    </span>
                </div>
                <div className="flex gap-1">
                    <Button variant="ghost" size="icon" className="size-8" aria-label={`View ${group.ir_number}`} onClick={onView}>
                        <Eye />
                    </Button>
                    {canUpdate && (
                        <>
                            <Button variant="ghost" size="icon" className="size-8" aria-label={`Edit ${group.ir_number}`} onClick={onEdit}>
                                <Pencil />
                            </Button>
                            <ConfirmAction
                                title="Delete this IR?"
                                description={`All ${group.count} violation record(s) under ${group.ir_number} will be removed.`}
                                confirmLabel="Delete"
                                destructive
                                onConfirm={() => router.delete(group.destroy_url, modal.visit({ preserveScroll: true }))}
                                trigger={
                                    <IconButton label={`Delete ${group.ir_number}`}>
                                        <Trash2 className="text-destructive" />
                                    </IconButton>
                                }
                            />
                        </>
                    )}
                </div>
            </CardHeader>
            <CardContent>
                <dl className="divide-y">
                    <Field label="Offenses" value={group.records.map((record) => record.section).join(', ')} optional />
                    <Field label="First offense" value={group.records[0]?.description || null} optional />
                    <Field label="Action" value={group.actions.length ? group.actions.join(', ') : 'No action selected'} />
                    {sda && <Field label="SDA" value={`${peso(group.sda_amount ?? 0)} · ${peso(group.sda_terms ?? 0)} per cutoff`} hint={`${group.sda_start} → ${group.sda_end}`} />}
                    {suspension && <Field label="Suspension" value={`${group.suspension_start} → ${group.suspension_end}`} />}
                    {group.remarks[0] && <Field label="Remarks" value={group.remarks[0]} />}
                </dl>
            </CardContent>
        </Card>
    );
}

function RemoveAttachment({ name, url }: { name: string; url: string }) {
    const modal = useModal();

    return (
        <ConfirmAction
            title="Remove attachment?"
            description={`${name} will be deleted.`}
            confirmLabel="Remove"
            destructive
            onConfirm={() => router.delete(url, modal.visit({ preserveScroll: true }))}
            trigger={
                <IconButton label={`Remove ${name}`}>
                    <Trash2 className="text-destructive" />
                </IconButton>
            }
        />
    );
}

function ActivityLog({ logs, url }: { logs: Paginated<Log>; url: string }) {
    const modal = useModal();
    const go = (page: number) => modal.get(url, { page: page > 1 ? page : undefined });

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between gap-2">
                <CardTitle>Activity log</CardTitle>
                <Badge variant="secondary">{logs.total} total</Badge>
            </CardHeader>
            <CardContent className="grid gap-2">
                {logs.data.length === 0 && <p className="py-4 text-center text-sm text-muted-foreground">No logs yet. Changes to this employee will appear here.</p>}
                {logs.data.map((log) => (
                    <div key={log.id} className="grid gap-2 rounded-lg border p-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                        <div className="grid gap-1.5">
                            <div className="flex flex-wrap items-center gap-2 text-sm">
                                <Badge variant="outline" className={TONE[log.tone]}>
                                    {log.label}
                                </Badge>
                                <span className="text-muted-foreground">
                                    by <strong className="text-foreground">{log.actor}</strong>
                                </span>
                            </div>
                            {log.changes.length === 0 ? (
                                <p className="text-xs text-muted-foreground">No field changes recorded.</p>
                            ) : (
                                <dl className="grid gap-1 text-xs">
                                    {log.changes.map((change) => (
                                        <div key={change.field} className="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                                            <dt className="font-medium">{change.field}</dt>
                                            <dd className="flex min-w-0 flex-wrap items-center gap-1 break-words">
                                                <span className="text-muted-foreground line-through decoration-muted-foreground/50">{change.from}</span>
                                                <ArrowRight className="size-3 shrink-0" />
                                                <span className="font-medium">{change.to}</span>
                                            </dd>
                                        </div>
                                    ))}
                                </dl>
                            )}
                        </div>
                        <div className="text-xs text-muted-foreground sm:text-right">
                            <div>{log.date}</div>
                            <div>{log.time}</div>
                        </div>
                    </div>
                ))}
                {logs.last_page > 1 && (
                    <div className="flex items-center justify-end gap-2 pt-1 text-sm">
                        <span className="text-muted-foreground">
                            Page {logs.current_page} of {logs.last_page}
                        </span>
                        <Button variant="outline" size="icon" className="size-8" aria-label="Newer logs" disabled={logs.current_page <= 1} onClick={() => go(logs.current_page - 1)}>
                            <ChevronLeft />
                        </Button>
                        <Button variant="outline" size="icon" className="size-8" aria-label="Older logs" disabled={logs.current_page >= logs.last_page} onClick={() => go(logs.current_page + 1)}>
                            <ChevronRight />
                        </Button>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function IrDetails({ group, onClose, onEdit }: { group: IrGroup; onClose: () => void; onEdit?: () => void }) {
    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>IR details · {group.ir_number}</DialogTitle>
                </DialogHeader>
                <dl className="divide-y">
                    <Field label="Total violations" value={String(group.count)} />
                    <Field label="Action" value={group.actions.length ? group.actions.join(', ') : 'No action selected'} />
                    <Field label="Recorded" value={group.recorded} />
                    <Field label="Updated" value={group.updated} />
                </dl>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead className="w-10">#</TableHead>
                            <TableHead className="w-40">Section</TableHead>
                            <TableHead>Description</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {group.records.map((record, index) => (
                            <TableRow key={index}>
                                <TableCell>{index + 1}</TableCell>
                                <TableCell>
                                    <div className="font-medium">{record.section}</div>
                                    {record.type && <div className="text-xs text-muted-foreground">Type {record.type}</div>}
                                </TableCell>
                                <TableCell className="whitespace-normal">{record.description || '—'}</TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <div className="grid gap-1 text-sm">
                    <span className="font-medium">Remarks</span>
                    {group.remarks.length ? group.remarks.map((remark) => <p key={remark} className="text-muted-foreground">{remark}</p>) : <p className="text-muted-foreground">No remarks.</p>}
                </div>
                {group.actions.includes('Salary Deduction Authorization') && (
                    <dl className="divide-y rounded-lg border border-amber-300/60 px-3">
                        <Field label="SDA total amount" value={peso(group.sda_amount ?? 0)} />
                        <Field label="Per cutoff" value={peso(group.sda_terms ?? 0)} />
                        <Field label="Start / end" value={`${group.sda_start} → ${group.sda_end}`} />
                    </dl>
                )}
                {group.actions.includes('Suspension') && (
                    <dl className="divide-y rounded-lg border border-red-300/60 px-3">
                        <Field label="Suspension" value={`${group.suspension_start} → ${group.suspension_end}`} />
                    </dl>
                )}
                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Close
                    </Button>
                    {onEdit && (
                        <Button onClick={onEdit}>
                            <Pencil />
                            Edit IR
                        </Button>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
