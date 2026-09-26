import { useForm } from '@inertiajs/react';
import { Link2, Link2Off, LoaderCircle } from 'lucide-react';
import type { FormEvent } from 'react';
import { useModal } from '@/components/modal/modal-context';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

export interface LinkedBiometric {
    id: number;
    name: string;
    employee_no: string | null;
    company: string | null;
    active: boolean;
    last_check: string | null;
    total_logs: number;
    edit_url: string;
}

/**
 * Links an HR employee to its biometric record (PUT employees.biometric-link.update),
 * for employees whose Employee ID is not encoded the same way in both places.
 * Options come from App\Support\HR\BiometricLink (name matches first).
 */
export function BiometricLinkDialog({
    open,
    onOpenChange,
    employeeName,
    current,
    options,
    url,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    employeeName: string;
    current: LinkedBiometric | null;
    options: SearchOption[];
    url: string;
}) {
    const modal = useModal();
    const form = useForm({ employee_biometric_id: current ? String(current.id) : '' });

    const save = (value: string) => {
        form.transform(() => ({ employee_biometric_id: value === '' ? null : Number(value) }));
        form.put(url, modal.visit({ preserveScroll: true, onSuccess: () => onOpenChange(false) }));
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        save(form.data.employee_biometric_id);
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Link to biometrics</DialogTitle>
                        <DialogDescription>
                            Pick the biometric record of {employeeName}. Use this when the Employee ID is not yet encoded the same in HR and in the
                            biometrics. Name matches are listed first.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-1.5">
                        <Label htmlFor="biometric-link">Biometric record</Label>
                        <SearchSelect
                            id="biometric-link"
                            ariaLabel="Biometric record"
                            options={options}
                            value={form.data.employee_biometric_id}
                            onChange={(value) => form.setData('employee_biometric_id', value)}
                            placeholder="Select biometric record"
                            searchPlaceholder="Search name or employee no..."
                            invalid={Boolean(form.errors.employee_biometric_id)}
                        />
                        {form.errors.employee_biometric_id && <p className="text-sm text-destructive">{form.errors.employee_biometric_id}</p>}
                    </div>
                    <DialogFooter className="gap-2 sm:justify-between">
                        {current ? (
                            <Button type="button" variant="outline" className="text-destructive" disabled={form.processing} onClick={() => save('')}>
                                <Link2Off />
                                Unlink
                            </Button>
                        ) : (
                            <span />
                        )}
                        <Button type="submit" disabled={form.processing || form.data.employee_biometric_id === ''}>
                            {form.processing ? <LoaderCircle className="animate-spin" /> : <Link2 />}
                            Save link
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
