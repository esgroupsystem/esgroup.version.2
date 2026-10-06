import { router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { ConfirmAction, IconButton } from '@/components/confirm-action';
import { DataTable, type DataTableColumn } from '@/components/data-table/data-table';
import { ModalLink } from '@/components/modal/modal-link';
import { openModal } from '@/components/modal/modal-store';
import { useModal } from '@/components/modal/modal-context';
import { SettingsTabs } from '@/components/payroll/settings/settings-tabs';
import type { SettingsTabUrls } from '@/components/payroll/settings/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { definePage } from '@/lib/define-page';
import { cn } from '@/lib/utils';

interface RuleRow {
    id: number;
    name: string;
    code: string;
    kind: 'earning' | 'deduction';
    kind_label: string;
    method: string;
    method_label: string;
    description: string;
    cutoff_label: string;
    applies_to: string;
    dates: string;
    is_active: boolean;
    sort_order: number;
    notes: string | null;
    updated_by: string | null;
    updated_at: string | null;
    urls: { edit: string; destroy: string; active: string } | null;
}

interface Props {
    rules: RuleRow[];
    can: { manage: boolean };
    urls: SettingsTabUrls & { create: string };
}

const KIND_CLASS = {
    earning: 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
    deduction: 'border-destructive/40 bg-destructive/10 text-destructive',
};

export default definePage<Props>({
    title: () => 'Payroll Settings',
    description: () => 'Your own earnings and deductions: fixed amounts, percents, amounts per day / hour, or a formula. Earnings add to gross pay; deductions come out of net pay.',
    actions: ({ can, urls }) =>
        can.manage && (
            <Button asChild>
                <ModalLink href={urls.create} mode="form">
                    <Plus />
                    New rule
                </ModalLink>
            </Button>
        ),
    size: 'xl',
    Content: RulesIndex,
});

function RulesIndex({ rules, can, urls }: Props) {
    const modal = useModal();

    const columns: DataTableColumn<RuleRow>[] = [
        {
            key: 'name',
            header: 'Rule',
            className: 'min-w-44 whitespace-normal',
            value: (rule) => `${rule.name} ${rule.code}`,
            cell: (rule) => (
                <>
                    <div className={cn('font-medium', !rule.is_active && 'text-muted-foreground')}>{rule.name}</div>
                    <div className="font-mono text-xs text-muted-foreground">{rule.code}</div>
                </>
            ),
        },
        {
            key: 'kind',
            header: 'Type',
            value: (rule) => rule.kind,
            filter: {
                type: 'select',
                param: 'kind',
                placeholder: 'All types',
                options: [
                    { value: 'earning', label: 'Earning' },
                    { value: 'deduction', label: 'Deduction' },
                ],
            },
            cell: (rule) => (
                <Badge variant="outline" className={KIND_CLASS[rule.kind]}>
                    {rule.kind_label}
                </Badge>
            ),
        },
        {
            key: 'how',
            header: 'How it computes',
            value: (rule) => rule.description,
            cell: (rule) => (
                <div className="w-72 max-w-full whitespace-normal">
                    <div className={cn('text-sm', rule.method === 'formula' && 'font-mono text-xs')}>{rule.description}</div>
                    <div className="text-xs text-muted-foreground">{rule.method_label}</div>
                </div>
            ),
        },
        {
            key: 'when',
            header: 'When / who',
            hideBelow: 'lg',
            value: (rule) => `${rule.cutoff_label} ${rule.applies_to} ${rule.dates}`,
            cell: (rule) => (
                <div className="w-56 max-w-full text-xs whitespace-normal">
                    <div>{rule.cutoff_label}</div>
                    <div className="text-muted-foreground">{rule.applies_to}</div>
                    <div className="text-muted-foreground">{rule.dates}</div>
                </div>
            ),
        },
        {
            key: 'active',
            header: 'On',
            value: (rule) => (rule.is_active ? 'active' : 'inactive'),
            filter: {
                type: 'select',
                param: 'active',
                placeholder: 'All',
                options: [
                    { value: 'active', label: 'On' },
                    { value: 'inactive', label: 'Off' },
                ],
            },
            exportValue: (rule) => (rule.is_active ? 'On' : 'Off'),
            cell: (rule) => (
                <span onClick={(event) => event.stopPropagation()}>
                    <Switch
                        checked={rule.is_active}
                        disabled={!rule.urls}
                        aria-label={`${rule.is_active ? 'Turn off' : 'Turn on'} ${rule.name}`}
                        onCheckedChange={(checked) => rule.urls && router.put(rule.urls.active, { is_active: checked }, modal.visit({ preserveScroll: true }))}
                    />
                </span>
            ),
        },
    ];

    return (
        <div className="grid min-w-0 gap-4">
            <SettingsTabs active="rules" urls={urls} />

            <DataTable
                title="Custom rules"
                description="Rules run in order: earnings first, then deductions, each by its order number. A later rule can use an earlier rule's code in its formula."
                noun="rule"
                rows={rules}
                columns={columns}
                rowKey={(rule) => rule.id}
                onRowClick={can.manage ? (rule) => rule.urls && openModal(rule.urls.edit, { mode: 'form' }) : undefined}
                emptyText="No custom rules yet. Add one with New rule, e.g. a rice allowance or a canteen deduction."
                rowActions={(rule) =>
                    rule.urls && (
                        <span className="flex justify-end gap-1" onClick={(event) => event.stopPropagation()}>
                            <IconButton label={`Edit ${rule.name}`} onClick={() => openModal(rule.urls!.edit, { mode: 'form' })}>
                                <Pencil />
                            </IconButton>
                            <ConfirmAction
                                title={`Delete "${rule.name}"?`}
                                description="Finalized payrolls keep the amounts this rule gave. Draft payrolls drop it when recomputed. To pause it instead, switch it off."
                                confirmLabel="Delete rule"
                                destructive
                                onConfirm={() => router.delete(rule.urls!.destroy, modal.visit({ preserveScroll: true }))}
                                trigger={
                                    <IconButton label={`Delete ${rule.name}`}>
                                        <Trash2 />
                                    </IconButton>
                                }
                            />
                        </span>
                    )
                }
            />
        </div>
    );
}
