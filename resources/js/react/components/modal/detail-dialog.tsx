import type { ReactNode } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { ModalSize } from '@/lib/define-page';
import { cn } from '@/lib/utils';

const SIZE_CLASS: Record<ModalSize, string> = {
    sm: 'sm:max-w-md',
    md: 'sm:max-w-2xl',
    lg: 'sm:max-w-4xl',
    xl: 'sm:max-w-6xl',
    full: 'sm:max-w-[min(96vw,1600px)] sm:h-[94vh]',
};

/**
 * Same look as modal pages, for records whose data is already on the page
 * (no server round trip): header, scrolling body, optional footer.
 *
 *   <DetailDialog open={!!log} onOpenChange={() => setLog(null)} title="Log #12" size="lg">...</DetailDialog>
 */
export function DetailDialog({
    open,
    onOpenChange,
    title,
    description,
    actions,
    footer,
    size = 'lg',
    children,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    footer?: ReactNode;
    size?: ModalSize;
    children: ReactNode;
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className={cn('flex max-h-[94vh] flex-col gap-0 overflow-hidden p-0', SIZE_CLASS[size])}>
                <DialogHeader className="flex-row items-start justify-between gap-4 space-y-0 border-b px-6 py-4 pr-12 text-left">
                    <div className="min-w-0 space-y-1">
                        <DialogTitle className="text-lg">{title}</DialogTitle>
                        {description ? (
                            <DialogDescription asChild>
                                <div className="text-sm text-muted-foreground">{description}</div>
                            </DialogDescription>
                        ) : (
                            <DialogDescription className="sr-only">Details</DialogDescription>
                        )}
                    </div>
                    {actions && <div className="flex shrink-0 flex-wrap items-center gap-2">{actions}</div>}
                </DialogHeader>
                <div className="min-h-0 flex-1 overflow-y-auto bg-muted/20 px-6 py-5">{children}</div>
                {footer && <DialogFooter className="border-t px-6 py-3">{footer}</DialogFooter>}
            </DialogContent>
        </Dialog>
    );
}

/** Label / value grid used inside detail dialogs. */
export function DetailGrid({ items, columns = 2 }: { items: { label: ReactNode; value: ReactNode; wide?: boolean }[]; columns?: 1 | 2 | 3 }) {
    return (
        <dl className={cn('grid gap-x-6 gap-y-3 rounded-lg border bg-card p-4 text-sm', columns === 2 && 'sm:grid-cols-2', columns === 3 && 'sm:grid-cols-3')}>
            {items.map((item, index) => (
                <div key={index} className={cn('min-w-0', item.wide && 'sm:col-span-full')}>
                    <dt className="text-xs text-muted-foreground">{item.label}</dt>
                    <dd className="mt-0.5 break-words">{item.value ?? '—'}</dd>
                </div>
            ))}
        </dl>
    );
}
