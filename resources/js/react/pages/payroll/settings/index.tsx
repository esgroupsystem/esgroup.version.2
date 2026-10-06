import { router } from '@inertiajs/react';
import { CalendarClock, Copy, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { ModalLink } from '@/components/modal/modal-link';
import { useModal } from '@/components/modal/modal-context';
import { SettingsTabs } from '@/components/payroll/settings/settings-tabs';
import { formatSetting, type SettingSection, type SettingValues, type SettingsTabUrls } from '@/components/payroll/settings/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';

interface VersionRow {
    id: number;
    label: string;
    effective_from: string;
    effective_label: string;
    notes: string;
    created_by: string | null;
    updated_by: string | null;
    updated_at: string | null;
    is_current: boolean;
    is_future: boolean;
    first: boolean;
    changes: { key: string; label: string; from: string; to: string }[];
    usage: { total: number; finalized: number };
    urls: { edit: string; destroy: string; copy: string } | null;
}

interface Props {
    versions: VersionRow[];
    current: { id: number | null; label: string; effective_from: string | null; values: SettingValues };
    sections: SettingSection[];
    can: { manage: boolean };
    urls: SettingsTabUrls & { create: string };
}

export default definePage<Props>({
    title: () => 'Payroll Settings',
    description: () => 'Every rate and contribution the payroll uses. A change starts on the date you choose; finalized payrolls never change.',
    actions: ({ can, urls }) =>
        can.manage && (
            <Button asChild>
                <ModalLink href={urls.create} mode="form">
                    <Plus />
                    New settings version
                </ModalLink>
            </Button>
        ),
    size: 'xl',
    Content: SettingsIndex,
});

function SettingsIndex({ versions, current, sections, urls }: Props) {
    const upcoming = versions.filter((version) => version.is_future);

    return (
        <div className="grid min-w-0 gap-4">
            <SettingsTabs active="settings" urls={urls} />

            {upcoming.length > 0 && (
                <div className="flex items-start gap-3 rounded-lg border border-sky-300 bg-sky-50 px-4 py-3 text-sm text-sky-900 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-200">
                    <CalendarClock className="mt-0.5 size-4 shrink-0" aria-hidden />
                    <div>
                        {upcoming.map((version) => (
                            <div key={version.id}>
                                <span className="font-medium">{version.label}</span> starts on {version.effective_label} ({version.changes.length} change
                                {version.changes.length === 1 ? '' : 's'}). Payrolls from that date use it automatically.
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="flex flex-wrap items-baseline justify-between gap-2">
                <h2 className="text-base font-semibold">In effect today: {current.label}</h2>
                {current.effective_from && <span className="text-sm text-muted-foreground">since {new Date(`${current.effective_from}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}</span>}
            </div>

            <div className="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                {sections.map((section) => (
                    <Card key={section.key} className="gap-3">
                        <CardHeader>
                            <CardTitle className="text-base">{section.title}</CardTitle>
                            <CardDescription>{section.description}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl className="divide-y text-sm">
                                {section.fields.map((field) => (
                                    <div key={field.key} className="grid grid-cols-[minmax(0,1fr)_minmax(0,auto)] items-start gap-4 py-1.5">
                                        <dt className="min-w-0 break-words text-muted-foreground">{field.label}</dt>
                                        <dd className="max-w-[14rem] min-w-0 text-right font-medium break-words tabular-nums">{formatSetting(field, current.values[field.key])}</dd>
                                    </div>
                                ))}
                            </dl>
                        </CardContent>
                    </Card>
                ))}
            </div>

            <h2 className="mt-2 text-base font-semibold">Versions and history</h2>
            <div className="grid gap-3">
                {versions.map((version) => (
                    <VersionCard key={version.id} version={version} canDelete={versions.length > 1} />
                ))}
            </div>
        </div>
    );
}

function VersionCard({ version, canDelete }: { version: VersionRow; canDelete: boolean }) {
    const modal = useModal();
    const [expanded, setExpanded] = useState(false);
    const changes = expanded ? version.changes : version.changes.slice(0, 6);

    return (
        <Card className={cn('gap-3', version.is_current && 'border-primary/50')}>
            <CardHeader className="flex flex-row flex-wrap items-start justify-between gap-3">
                <div className="grid min-w-0 gap-1">
                    <CardTitle className="flex flex-wrap items-center gap-2 text-base">
                        {version.label}
                        {version.is_current && <Badge className="bg-primary/10 text-primary hover:bg-primary/10">In effect</Badge>}
                        {version.is_future && (
                            <Badge variant="outline" className="border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-300">
                                Upcoming
                            </Badge>
                        )}
                    </CardTitle>
                    <CardDescription>
                        From {version.effective_label}
                        {version.usage.total > 0 && ` · used by ${version.usage.total} payroll${version.usage.total === 1 ? '' : 's'} (${version.usage.finalized} finalized)`}
                        {version.updated_at && ` · saved ${version.updated_at}${version.updated_by ? ` by ${version.updated_by}` : ''}`}
                    </CardDescription>
                </div>
                {version.urls && (
                    <div className="flex items-center gap-1">
                        <ModalLink href={version.urls.edit} mode="form" aria-label={`Edit ${version.label}`} className="inline-flex size-8 items-center justify-center rounded-md hover:bg-accent">
                            <Pencil className="size-4" />
                        </ModalLink>
                        <ModalLink href={version.urls.copy} mode="form" aria-label={`Copy ${version.label} as a new version`} className="inline-flex size-8 items-center justify-center rounded-md hover:bg-accent">
                            <Copy className="size-4" />
                        </ModalLink>
                        {canDelete && (
                            <ConfirmAction
                                title={`Delete "${version.label}"?`}
                                description={
                                    version.usage.finalized > 0
                                        ? 'Finalized payrolls keep the values they were computed with. Draft payrolls in this period use the previous version when recomputed.'
                                        : 'Payrolls in this period will use the previous version instead.'
                                }
                                confirmLabel="Delete"
                                destructive
                                onConfirm={() => router.delete(version.urls!.destroy, modal.visit({ preserveScroll: true }))}
                                trigger={
                                    <IconButton label={`Delete ${version.label}`}>
                                        <Trash2 />
                                    </IconButton>
                                }
                            />
                        )}
                    </div>
                )}
            </CardHeader>
            <CardContent className="grid gap-2 text-sm">
                {version.notes && <p className="text-muted-foreground">{version.notes}</p>}
                {version.changes.length === 0 ? (
                    <p className="text-muted-foreground">{version.first ? 'Same as the built-in starting values.' : 'No values changed from the previous version.'}</p>
                ) : (
                    <>
                        <p className="text-xs font-medium text-muted-foreground uppercase">
                            {version.first ? 'Different from the built-in starting values' : 'Changed from the previous version'}
                        </p>
                        <ul className="grid gap-1 sm:grid-cols-2">
                            {changes.map((change) => (
                                <li key={change.key} className="flex flex-wrap items-baseline gap-x-2">
                                    <span className="text-muted-foreground">{change.label}:</span>
                                    <span className="tabular-nums line-through decoration-muted-foreground/60">{change.from}</span>
                                    <span aria-hidden>→</span>
                                    <span className="font-medium tabular-nums">{change.to}</span>
                                </li>
                            ))}
                        </ul>
                        {version.changes.length > 6 && (
                            <Button type="button" variant="link" size="sm" className="h-auto w-fit p-0" onClick={() => setExpanded((value) => !value)}>
                                {expanded ? 'Show less' : `Show all ${version.changes.length} changes`}
                            </Button>
                        )}
                    </>
                )}
            </CardContent>
        </Card>
    );
}
