import { useEffect, useState } from 'react';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { fromDisplay, sameValue, toDisplay, type SettingField } from './types';

const SUFFIX: Partial<Record<SettingField['type'], string>> = {
    percent: '%',
    hours: 'hr',
    minutes: 'min',
    days: 'days',
};

/** One Payroll Settings value, with the right input for its type (percent shows 125 for 1.25). */
export function SettingInput({
    id,
    field,
    value,
    onChange,
    invalid,
}: {
    id: string;
    field: SettingField;
    value: string | number | undefined;
    onChange: (value: string | number) => void;
    invalid?: boolean;
}) {
    if (field.type === 'select') {
        return (
            <Select value={String(value ?? '')} onValueChange={onChange}>
                <SelectTrigger id={id} className="w-full" aria-invalid={invalid}>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {Object.entries(field.options ?? {}).map(([option, label]) => (
                        <SelectItem key={option} value={option}>
                            {label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        );
    }

    if (field.type === 'time' || field.type === 'date' || field.type === 'text') {
        return (
            <Input
                id={id}
                type={field.type === 'text' ? 'text' : field.type}
                value={String(value ?? '')}
                aria-invalid={invalid}
                onChange={(event) => onChange(event.target.value)}
            />
        );
    }

    return <NumberSettingInput id={id} field={field} value={value} onChange={onChange} invalid={invalid} />;
}

function NumberSettingInput({
    id,
    field,
    value,
    onChange,
    invalid,
}: {
    id: string;
    field: SettingField;
    value: string | number | undefined;
    onChange: (value: string | number) => void;
    invalid?: boolean;
}) {
    // Local text keeps half-typed numbers ("12.") while the stored value is converted.
    const [text, setText] = useState(() => toDisplay(field, value));

    useEffect(() => {
        setText((current) => (sameValue(fromDisplay(field, current), value) ? current : toDisplay(field, value)));
    }, [field, value]);

    const suffix = SUFFIX[field.type];
    const isPercent = field.type === 'percent';

    return (
        <div className="relative">
            {field.type === 'money' && <span className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground">₱</span>}
            <Input
                id={id}
                type="number"
                inputMode="decimal"
                step="any"
                min={field.min !== undefined ? (isPercent ? field.min * 100 : field.min) : undefined}
                max={field.max !== undefined ? (isPercent ? field.max * 100 : field.max) : undefined}
                value={text}
                aria-invalid={invalid}
                className={cn('tabular-nums', field.type === 'money' && 'pl-7', suffix && 'pr-12')}
                onChange={(event) => {
                    setText(event.target.value);
                    onChange(fromDisplay(field, event.target.value));
                }}
            />
            {suffix && <span className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-sm text-muted-foreground">{suffix}</span>}
        </div>
    );
}
