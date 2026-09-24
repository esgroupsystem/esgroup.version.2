import { Plus, Trash2, UserCog } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export interface Option {
    value: string;
    label: string;
    description?: string;
}

const STATUS_TONE: Record<string, string> = {
    standby: 'text-muted-foreground',
    waiting_parts: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    on_going_repair: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    operational: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
};

const REPAIR_TONE: Record<string, string> = {
    mechanical: 'border-sky-300 text-sky-700 dark:border-sky-800 dark:text-sky-400',
    electrical: 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400',
    aircon: 'border-cyan-300 text-cyan-700 dark:border-cyan-800 dark:text-cyan-400',
    body_repair: 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400',
    repainting: 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400',
};

export function JobStatusBadge({ value, label }: { value: string | null; label: string }) {
    return (
        <Badge variant="outline" className={STATUS_TONE[value ?? ''] ?? ''}>
            {label}
        </Badge>
    );
}

export function RepairTypeBadge({ value, label }: { value: string; label: string }) {
    return (
        <Badge variant="outline" className={REPAIR_TONE[value] ?? ''}>
            {label}
        </Badge>
    );
}

/** Mechanic name rows + repair type checkboxes (the Blade _repair-details-fields partial). */
export function RepairDetailsFields({
    mechanics,
    onMechanicsChange,
    repairTypes,
    onRepairTypesChange,
    options,
    errors,
}: {
    mechanics: string[];
    onMechanicsChange: (names: string[]) => void;
    repairTypes: string[];
    onRepairTypesChange: (types: string[]) => void;
    options: Option[];
    errors: Record<string, string>;
}) {
    const mechanicError = errors.mechanic_names ?? Object.entries(errors).find(([key]) => key.startsWith('mechanic_names.'))?.[1];
    const repairError = errors.repair_types ?? Object.entries(errors).find(([key]) => key.startsWith('repair_types.'))?.[1];

    return (
        <div className="grid gap-5">
            <div className="grid gap-2">
                <div className="flex items-center justify-between gap-2">
                    <div>
                        <Label>Mechanic name(s)</Label>
                        <p className="text-xs text-muted-foreground">Add every mechanic who performed or supervised the repair.</p>
                    </div>
                    <Button type="button" variant="outline" size="sm" onClick={() => onMechanicsChange([...mechanics, ''])}>
                        <Plus />
                        Add mechanic
                    </Button>
                </div>
                {mechanics.map((name, index) => (
                    <div key={index} className="flex gap-2">
                        <div className="relative flex-1">
                            <UserCog className="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                className="pl-8"
                                maxLength={255}
                                aria-label={`Mechanic ${index + 1}`}
                                placeholder="Enter mechanic full name"
                                value={name}
                                aria-invalid={!!errors[`mechanic_names.${index}`]}
                                onChange={(event) => onMechanicsChange(mechanics.map((current, i) => (i === index ? event.target.value : current)))}
                            />
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            aria-label={`Remove mechanic ${index + 1}`}
                            disabled={mechanics.length === 1}
                            onClick={() => onMechanicsChange(mechanics.filter((_, i) => i !== index))}
                        >
                            <Trash2 className="text-destructive" />
                        </Button>
                    </div>
                ))}
                {mechanicError && <p className="text-xs text-destructive">{mechanicError}</p>}
            </div>

            <div className="grid gap-2">
                <div>
                    <Label>Type of repair done</Label>
                    <p className="text-xs text-muted-foreground">Multiple repair types may be selected for one job order.</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {options.map((option) => {
                        const checked = repairTypes.includes(option.value);

                        return (
                            <label key={option.value} className={cn('flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm', checked && 'border-primary bg-accent/50')}>
                                <Checkbox checked={checked} onCheckedChange={(value) => onRepairTypesChange(value ? [...repairTypes, option.value] : repairTypes.filter((type) => type !== option.value))} />
                                <RepairTypeBadge value={option.value} label={option.label} />
                            </label>
                        );
                    })}
                </div>
                {repairError && <p className="text-xs text-destructive">{repairError}</p>}
            </div>
        </div>
    );
}

/** Mechanic rows are optional; blank ones are dropped before submitting. */
export const cleanMechanics = (names: string[]) => names.map((name) => name.trim()).filter(Boolean);
