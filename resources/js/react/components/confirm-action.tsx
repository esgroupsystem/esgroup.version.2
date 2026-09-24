import type { ReactNode } from 'react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

/** Confirmation dialog in front of a destructive or important action. */
export function ConfirmAction({
    title,
    description,
    confirmLabel,
    destructive = false,
    onConfirm,
    trigger,
}: {
    title: string;
    description: ReactNode;
    confirmLabel: string;
    destructive?: boolean;
    onConfirm: () => void;
    trigger: ReactNode;
}) {
    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>
                <span>{trigger}</span>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>{description}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={onConfirm}
                        className={destructive ? 'bg-destructive text-white hover:bg-destructive/90' : undefined}
                    >
                        {confirmLabel}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

/** Small ghost icon button with a tooltip; `label` is also its accessible name. */
export function IconButton({ label, children, disabled, onClick }: { label: string; children: ReactNode; disabled?: boolean; onClick?: () => void }) {
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <Button variant="ghost" size="icon" className="size-8" aria-label={label} disabled={disabled} onClick={onClick}>
                    {children}
                </Button>
            </TooltipTrigger>
            <TooltipContent>{label}</TooltipContent>
        </Tooltip>
    );
}
