import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, LoaderCircle, Save } from 'lucide-react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { useModal } from '@/components/modal/modal-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';

interface Values {
    name: string;
    holiday_type: string;
    source_proclamation: string;
    actual_date: string;
    observed_date: string;
    override_multipliers: boolean;
    not_worked_multiplier: string;
    worked_multiplier: string;
    notes: string;
    is_moved: boolean;
    is_active: boolean;
}

interface Props {
    holiday: { id: number; name: string } | null;
    values: Values;
    standardMultipliers: Record<string, { not_worked_multiplier: number | string; worked_multiplier: number | string }>;
    urls: { index: string; submit: string };
}

export default definePage<Props>({
    title: ({ holiday }) => (holiday ? `Edit holiday — ${holiday.name}` : 'Add holiday'),
    description: () => 'Multipliers follow the holiday type unless custom pay multipliers are turned on.',
    actions: (props) => <BackLink {...props} />,
    size: 'lg',
    Content: HolidayForm,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.index}>
                <ArrowLeft />
                Back
            </Link>
        </Button>
    );
}

function HolidayForm({ holiday, values, standardMultipliers, urls }: Props) {
    const isEdit = holiday !== null;
    const modal = useModal();
    const form = useForm<Values>(values);
    const { data, errors, processing } = form;

    // Standard multipliers follow the type unless "Custom" is on (the server
    // applies the same rule when saving).
    const applyStandard = (type: string) => {
        const standard = standardMultipliers[type];
        return standard
            ? {
                  not_worked_multiplier: Number(standard.not_worked_multiplier).toFixed(2),
                  worked_multiplier: Number(standard.worked_multiplier).toFixed(2),
              }
            : {};
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = modal.visit({ preserveScroll: true });
        if (isEdit) {
            form.put(urls.submit, options);
        } else {
            form.post(urls.submit, options);
        }
    };

    return (
        <form onSubmit={submit} className="grid max-w-4xl gap-4">
            <Card>
                <CardContent className="grid gap-4 md:grid-cols-2">
                    <FormField id="holiday-name" label="Holiday name" required error={errors.name} className="md:col-span-2">
                        <Input id="holiday-name" required value={data.name} onChange={(event) => form.setData('name', event.target.value)} />
                    </FormField>
                    <FormField id="holiday-type" label="Holiday type" error={errors.holiday_type}>
                        <Select value={data.holiday_type} onValueChange={(type) => form.setData((current) => ({ ...current, holiday_type: type, ...applyStandard(type) }))}>
                            <SelectTrigger id="holiday-type" className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="regular">Regular Holiday</SelectItem>
                                <SelectItem value="special">Special Non-Working Holiday</SelectItem>
                            </SelectContent>
                        </Select>
                    </FormField>
                    <FormField id="holiday-source" label="Source proclamation" error={errors.source_proclamation}>
                        <Input id="holiday-source" value={data.source_proclamation} onChange={(event) => form.setData('source_proclamation', event.target.value)} />
                    </FormField>
                    <FormField id="holiday-actual" label="Actual date" required error={errors.actual_date}>
                        <Input id="holiday-actual" type="date" required value={data.actual_date} onChange={(event) => form.setData('actual_date', event.target.value)} />
                    </FormField>
                    <FormField id="holiday-observed" label="Observed date" required error={errors.observed_date}>
                        <Input id="holiday-observed" type="date" required value={data.observed_date} onChange={(event) => form.setData('observed_date', event.target.value)} />
                    </FormField>
                </CardContent>
            </Card>

            <Card>
                <CardContent className="grid gap-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <Label htmlFor="holiday-override">Custom pay multipliers</Label>
                            <p className="text-xs text-muted-foreground">Off: multipliers follow the holiday type automatically.</p>
                        </div>
                        <Switch
                            id="holiday-override"
                            checked={data.override_multipliers}
                            onCheckedChange={(checked) =>
                                form.setData((current) => ({ ...current, override_multipliers: checked, ...(checked ? {} : applyStandard(current.holiday_type)) }))
                            }
                        />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField id="holiday-not-worked" label="Not worked multiplier" error={errors.not_worked_multiplier}>
                            <Input
                                id="holiday-not-worked"
                                type="number"
                                step="0.01"
                                min={0}
                                max={10}
                                readOnly={!data.override_multipliers}
                                className={data.override_multipliers ? undefined : 'bg-muted/50'}
                                value={data.not_worked_multiplier}
                                onChange={(event) => form.setData('not_worked_multiplier', event.target.value)}
                            />
                        </FormField>
                        <FormField id="holiday-worked" label="Worked multiplier" error={errors.worked_multiplier}>
                            <Input
                                id="holiday-worked"
                                type="number"
                                step="0.01"
                                min={0}
                                max={10}
                                readOnly={!data.override_multipliers}
                                className={data.override_multipliers ? undefined : 'bg-muted/50'}
                                value={data.worked_multiplier}
                                onChange={(event) => form.setData('worked_multiplier', event.target.value)}
                            />
                        </FormField>
                    </div>
                    <FormField id="holiday-notes" label="Notes" error={errors.notes}>
                        <Textarea id="holiday-notes" rows={3} value={data.notes} onChange={(event) => form.setData('notes', event.target.value)} />
                    </FormField>
                    <div className="flex flex-wrap gap-6">
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox checked={data.is_moved} onCheckedChange={(checked) => form.setData('is_moved', checked === true)} />
                            Moved observance
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => form.setData('is_active', checked === true)} />
                            Active
                        </label>
                    </div>
                </CardContent>
            </Card>

            <div className="flex justify-end gap-2">
                {modal.inModal ? (
                    <Button type="button" variant="outline" onClick={modal.close}>
                        Cancel
                    </Button>
                ) : (
                    <Button type="button" variant="outline" asChild>
                        <Link href={urls.index}>Cancel</Link>
                    </Button>
                )}
                <Button type="submit" disabled={processing}>
                    {processing ? <LoaderCircle className="animate-spin" /> : <Save />}
                    {isEdit ? 'Update holiday' : 'Save holiday'}
                </Button>
            </div>
        </form>
    );
}
