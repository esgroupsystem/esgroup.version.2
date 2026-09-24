import type { ReactNode } from 'react';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

/** Label + control + error (or hint) stack used by the converted forms. */
export function FormField({
    id,
    label,
    required,
    error,
    hint,
    className,
    children,
}: {
    id?: string;
    label: ReactNode;
    required?: boolean;
    error?: string;
    hint?: ReactNode;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={cn('grid content-start gap-1.5', className)}>
            <Label htmlFor={id}>
                {label}
                {required && <span className="text-destructive">*</span>}
            </Label>
            {children}
            {error ? <p className="text-xs text-destructive">{error}</p> : hint ? <p className="text-xs text-muted-foreground">{hint}</p> : null}
        </div>
    );
}
