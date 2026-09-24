import { useForm } from '@inertiajs/react';
import { Loader2, Undo2 } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { FormField } from '@/components/form-field';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';

/** Confirm a whole-transaction rollback with an optional reason (rollback_reason). */
export function RollbackDialog({
    open,
    onOpenChange,
    title,
    description,
    effect,
    url,
    method = 'patch',
    defaultReason,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description: ReactNode;
    effect: ReactNode;
    url: string;
    method?: 'patch' | 'post';
    defaultReason: string;
}) {
    const form = useForm({ rollback_reason: defaultReason });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form[method](url, { preserveScroll: true, onSuccess: () => onOpenChange(false) });
    };

    return (
        <Dialog open={open} onOpenChange={(value) => !form.processing && onOpenChange(value)}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{title}</DialogTitle>
                        <DialogDescription>{description}</DialogDescription>
                    </DialogHeader>
                    <Alert>
                        <AlertTitle>Rollback effect</AlertTitle>
                        <AlertDescription>{effect}</AlertDescription>
                    </Alert>
                    <FormField id="rollback-reason" label="Rollback reason" error={form.errors.rollback_reason}>
                        <Textarea
                            id="rollback-reason"
                            rows={3}
                            maxLength={1000}
                            placeholder="Optional rollback reason..."
                            value={form.data.rollback_reason}
                            onChange={(event) => form.setData('rollback_reason', event.target.value)}
                        />
                    </FormField>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={form.processing}>
                            Cancel
                        </Button>
                        <Button type="submit" variant="destructive" disabled={form.processing}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Undo2 />}
                            Confirm rollback
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

/** Badge colours for inventory transaction statuses (posted / rolled_back / cancelled / partial). */
export function inventoryStatusClass(key: string): string {
    switch (key) {
        case 'posted':
        case 'completed':
            return 'border-emerald-300 text-emerald-700 dark:border-emerald-800 dark:text-emerald-400';
        case 'rolled_back':
        case 'partially_rolled_back':
            return 'border-amber-300 text-amber-700 dark:border-amber-800 dark:text-amber-400';
        case 'cancelled':
            return 'border-red-300 text-red-700 dark:border-red-800 dark:text-red-400';
        default:
            return 'text-muted-foreground';
    }
}
