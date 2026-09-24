import { Loader2, Maximize2 } from 'lucide-react';
import { useRef } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { resolveSize, type ModalSize } from '@/lib/define-page';
import { cn } from '@/lib/utils';
import { ModalScope } from './modal-context';
import { remove, useModalStack, type ModalEntry } from './modal-store';
import { useFitWidth } from './use-fit-width';

/**
 * Starting widths. useFitWidth() then widens the modal to fit its widest table
 * (up to 96vw), so data never needs a sideways scrollbar. Tall content scrolls
 * inside the body while the header stays put.
 */
const SIZE_CLASS: Record<ModalSize, string> = {
    sm: 'sm:max-w-md',
    md: 'sm:max-w-2xl',
    lg: 'sm:max-w-4xl',
    xl: 'sm:max-w-6xl',
    full: 'sm:max-w-[96vw]',
};

/** Renders every open modal page. Mounted once by AppLayout; the stack itself is global. */
export function ModalStack() {
    const { stack } = useModalStack();

    return (
        <>
            {stack.map((entry, index) => (
                <ModalWindow key={entry.id} entry={entry} depth={index} />
            ))}
        </>
    );
}

function ModalWindow({ entry, depth }: { entry: ModalEntry; depth: number }) {
    const ready = entry.status === 'ready' && entry.Page && entry.props;
    const descriptor = entry.Page?.page;
    const props = entry.props ?? {};
    const size = entry.size ?? (ready && descriptor ? resolveSize(descriptor, props) : 'lg');
    const Content = descriptor?.Content;
    const dialogRef = useRef<HTMLDivElement>(null);
    const bodyRef = useRef<HTMLDivElement>(null);
    const fitWidth = useFitWidth(dialogRef, bodyRef, size, Boolean(ready));

    return (
        <Dialog open onOpenChange={(open) => !open && remove(entry.id)}>
            <DialogContent
                ref={dialogRef}
                className={cn('flex max-h-[94vh] flex-col gap-0 overflow-hidden p-0', SIZE_CLASS[size])}
                style={{ zIndex: 50 + depth * 2, ...(fitWidth ? { maxWidth: fitWidth } : {}) }}
                aria-describedby={undefined}>
                {/* Header actions and body share the modal context (useModal()). */}
                <ModalScope id={entry.id}>
                    <DialogHeader className="flex-row items-start justify-between gap-4 space-y-0 border-b px-6 py-4 pr-12 text-left">
                        <div className="min-w-0 space-y-1">
                            {ready && descriptor ? (
                                <>
                                    <DialogTitle className="truncate text-lg">{descriptor.title(props)}</DialogTitle>
                                    {descriptor.description && <DialogDescription asChild><div className="text-sm text-muted-foreground">{descriptor.description(props)}</div></DialogDescription>}
                                </>
                            ) : (
                                <>
                                    <DialogTitle className="sr-only">Loading</DialogTitle>
                                    <Skeleton className="h-6 w-56" />
                                    <Skeleton className="h-4 w-80" />
                                </>
                            )}
                        </div>
                        <div className="flex shrink-0 flex-wrap items-center justify-end gap-2">
                            {ready && descriptor?.actions?.(props)}
                            {ready && (
                                <Button variant="ghost" size="icon" className="size-8" asChild title="Open as full page">
                                    <a href={entry.url} aria-label="Open as full page">
                                        <Maximize2 />
                                    </a>
                                </Button>
                            )}
                        </div>
                    </DialogHeader>

                    <div ref={bodyRef} className="min-h-0 flex-1 overflow-y-auto bg-muted/20 px-6 py-5 [scrollbar-gutter:stable]">
                        {ready && Content ? (
                            <div className="flex flex-col gap-4 md:gap-5">
                                <Content {...props} />
                            </div>
                        ) : (
                            <div className="flex min-h-48 items-center justify-center text-muted-foreground">
                                <Loader2 className="size-6 animate-spin" />
                            </div>
                        )}
                    </div>
                </ModalScope>
            </DialogContent>
        </Dialog>
    );
}
