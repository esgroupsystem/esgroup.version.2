import { useForm } from '@inertiajs/react';
import { Loader2, Plus, Save, X } from 'lucide-react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { SearchSelect, type SearchOption } from '@/components/search-select';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export interface OffenseOption extends SearchOption {
    description: string;
}

export interface ViolationValues {
    title: string;
    ir_number: string;
    offense_id: string[];
    description: string[];
    remarks: string;
    disciplinary_action: string[];
    sda_amount: string;
    sda_terms: string;
    sda_start_date: string;
    sda_end_date: string;
    suspension_start_date: string;
    suspension_end_date: string;
}

export const emptyViolation = (): ViolationValues => ({
    title: 'Violations',
    ir_number: '',
    offense_id: [''],
    description: [''],
    remarks: '',
    disciplinary_action: [],
    sda_amount: '',
    sda_terms: '',
    sda_start_date: '',
    sda_end_date: '',
    suspension_start_date: '',
    suspension_end_date: '',
});

const SDA = 'Salary Deduction Authorization';
const SUSPENSION = 'Suspension';

/** Add / edit one IR case: several offense sections, remarks and disciplinary actions. */
export function ViolationDialog({
    title,
    values,
    offenses,
    actions,
    url,
    method,
    onClose,
}: {
    title: string;
    values: ViolationValues;
    offenses: OffenseOption[];
    actions: string[];
    url: string;
    method: 'post' | 'put';
    onClose: () => void;
}) {
    const form = useForm<ViolationValues>(values.offense_id.length ? values : { ...values, offense_id: [''], description: [''] });
    const modal = useModal();
    const errors = form.errors as Record<string, string>;
    const hasSda = form.data.disciplinary_action.includes(SDA);
    const hasSuspension = form.data.disciplinary_action.includes(SUSPENSION);

    const setOffense = (index: number, offenseId: string) => {
        const offense = offenses.find((option) => option.value === offenseId);
        form.setData({
            ...form.data,
            offense_id: form.data.offense_id.map((value, current) => (current === index ? offenseId : value)),
            // Selecting a section fills its description, like the Blade form.
            description: form.data.description.map((value, current) => (current === index ? (offense?.description ?? value) : value)),
        });
    };

    const removeRow = (index: number) =>
        form.setData({
            ...form.data,
            offense_id: form.data.offense_id.filter((_, current) => current !== index),
            description: form.data.description.filter((_, current) => current !== index),
        });

    const toggleAction = (action: string, checked: boolean) =>
        form.setData('disciplinary_action', checked ? [...form.data.disciplinary_action, action] : form.data.disciplinary_action.filter((value) => value !== action));

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // Fields of unselected actions are cleared, like the disabled Blade inputs.
        form.transform((data) => ({
            ...data,
            ...(data.disciplinary_action.includes(SDA) ? {} : { sda_amount: '', sda_terms: '', sda_start_date: '', sda_end_date: '' }),
            ...(data.disciplinary_action.includes(SUSPENSION) ? {} : { suspension_start_date: '', suspension_end_date: '' }),
        }));
        form[method](url, modal.visit({ preserveScroll: true, onSuccess: onClose }));
    };

    return (
        <Dialog open onOpenChange={(open) => !open && !form.processing && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>{title}</DialogTitle>
                        <DialogDescription>Record the IR number, offense sections, remarks and disciplinary action.</DialogDescription>
                    </DialogHeader>

                    <FormField id="ir_number" label="IR number" required error={errors.ir_number}>
                        <Input id="ir_number" required placeholder="IR-2026-0001" value={form.data.ir_number} onChange={(event) => form.setData('ir_number', event.target.value)} />
                    </FormField>

                    <div className="grid gap-3">
                        <Label>Offense (section)</Label>
                        {errors.offense_id && <p className="text-xs text-destructive">{errors.offense_id}</p>}
                        {form.data.offense_id.map((offenseId, index) => (
                            <div key={index} className="grid gap-2 rounded-lg border p-3">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-semibold">Violation #{index + 1}</span>
                                    {form.data.offense_id.length > 1 && (
                                        <Button type="button" variant="ghost" size="sm" onClick={() => removeRow(index)}>
                                            <X />
                                            Remove
                                        </Button>
                                    )}
                                </div>
                                <SearchSelect
                                    options={offenses}
                                    value={offenseId}
                                    onChange={(value) => setOffense(index, value)}
                                    placeholder="-- Select offense section --"
                                    searchPlaceholder="Search section or description..."
                                    ariaLabel={`Offense section ${index + 1}`}
                                    invalid={!!errors[`offense_id.${index}`]}
                                />
                                {errors[`offense_id.${index}`] && <p className="text-xs text-destructive">{errors[`offense_id.${index}`]}</p>}
                                <Textarea
                                    rows={3}
                                    aria-label={`Description ${index + 1}`}
                                    placeholder="Description"
                                    value={form.data.description[index] ?? ''}
                                    onChange={(event) => form.setData('description', form.data.description.map((value, current) => (current === index ? event.target.value : value)))}
                                />
                            </div>
                        ))}
                        <div className="flex items-center justify-between gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => form.setData({ ...form.data, offense_id: [...form.data.offense_id, ''], description: [...form.data.description, ''] })}
                            >
                                <Plus />
                                Add another violation
                            </Button>
                            <span className="text-xs text-muted-foreground">Selecting a section will auto-fill the description.</span>
                        </div>
                    </div>

                    <FormField id="remarks" label="Other description / remarks (optional)" error={errors.remarks} hint="Additional notes not included in the offense description.">
                        <Textarea
                            id="remarks"
                            rows={3}
                            placeholder="Enter additional remarks, employee explanation, HR notes, or follow-up action details..."
                            value={form.data.remarks}
                            onChange={(event) => form.setData('remarks', event.target.value)}
                        />
                    </FormField>

                    <div className="grid gap-2">
                        <Label>Action (optional)</Label>
                        <div className="flex flex-wrap gap-4">
                            {actions.map((action) => (
                                <label key={action} className="flex items-center gap-2 text-sm">
                                    <Checkbox checked={form.data.disciplinary_action.includes(action)} onCheckedChange={(checked) => toggleAction(action, checked === true)} />
                                    {action}
                                </label>
                            ))}
                        </div>
                        <span className="text-xs text-muted-foreground">You may select multiple actions.</span>
                    </div>

                    {hasSda && (
                        <div className="grid gap-3 rounded-lg border border-amber-300/60 bg-amber-50/50 p-3 sm:grid-cols-2 dark:bg-amber-950/20">
                            <FormField id="sda_amount" label="SDA total amount" error={errors.sda_amount}>
                                <Input id="sda_amount" type="number" step="0.01" min={0} placeholder="Enter total deduction amount" value={form.data.sda_amount} onChange={(event) => form.setData('sda_amount', event.target.value)} />
                            </FormField>
                            <FormField id="sda_terms" label="Deduction terms" error={errors.sda_terms} hint="Number of installments/terms">
                                <Input id="sda_terms" type="number" min={1} step={1} placeholder="e.g. 3" value={form.data.sda_terms} onChange={(event) => form.setData('sda_terms', event.target.value)} />
                            </FormField>
                            <FormField id="sda_start_date" label="Deduction start date" error={errors.sda_start_date}>
                                <Input id="sda_start_date" type="date" value={form.data.sda_start_date} onChange={(event) => form.setData('sda_start_date', event.target.value)} />
                            </FormField>
                            <FormField id="sda_end_date" label="Deduction end date (optional)" error={errors.sda_end_date} hint="Leave blank if ongoing">
                                <Input id="sda_end_date" type="date" value={form.data.sda_end_date} onChange={(event) => form.setData('sda_end_date', event.target.value)} />
                            </FormField>
                        </div>
                    )}

                    {hasSuspension && (
                        <div className="grid gap-3 rounded-lg border border-red-300/60 bg-red-50/50 p-3 sm:grid-cols-2 dark:bg-red-950/20">
                            <FormField id="suspension_start_date" label="Suspension start date" error={errors.suspension_start_date}>
                                <Input id="suspension_start_date" type="date" value={form.data.suspension_start_date} onChange={(event) => form.setData('suspension_start_date', event.target.value)} />
                            </FormField>
                            <FormField id="suspension_end_date" label="Suspension end date" error={errors.suspension_end_date} hint="Leave blank if still suspended">
                                <Input id="suspension_end_date" type="date" value={form.data.suspension_end_date} onChange={(event) => form.setData('suspension_end_date', event.target.value)} />
                            </FormField>
                        </div>
                    )}

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                            {method === 'post' ? 'Save violation' : 'Update IR'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
