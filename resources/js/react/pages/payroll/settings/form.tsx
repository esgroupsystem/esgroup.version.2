import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Calculator, FlaskConical, LoaderCircle, RotateCcw, Save } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { DetailDialog } from '@/components/modal/detail-dialog';
import { useModal } from '@/components/modal/modal-context';
import { ContributionCalculator } from '@/components/payroll/settings/contribution-calculator';
import { SettingInput } from '@/components/payroll/settings/setting-input';
import { TestPanel } from '@/components/payroll/settings/test-panel';
import { formatSetting, sameValue, type SettingSection, type SettingValues, type TestOptions } from '@/components/payroll/settings/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';

interface FormValues {
    effective_from: string;
    label: string;
    notes: string;
    values: SettingValues;
}

interface Props {
    version: { id: number; label: string; usage: { total: number; finalized: number } | null } | null;
    values: FormValues;
    sections: SettingSection[];
    defaults: SettingValues;
    test: TestOptions;
    urls: { index: string; submit: string };
}

export default definePage<Props>({
    title: ({ version }) => (version ? `Edit settings — ${version.label}` : 'New settings version'),
    description: () => 'Payrolls whose period starts on or after the effective date use these values. Test them on real attendance before saving.',
    actions: (props) => <BackLink {...props} />,
    size: 'full',
    Content: SettingsForm,
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

function SettingsForm({ version, values, sections, defaults, test, urls }: Props) {
    const modal = useModal();
    const form = useForm<FormValues>(values);
    const { data, errors, processing } = form;
    const fieldErrors = errors as Record<string, string | undefined>;
    const [dialog, setDialog] = useState<'test' | 'calculator' | null>(null);

    const setValue = (key: string, value: string | number) => form.setData('values', { ...data.values, [key]: value });
    const changedIn = (section: SettingSection) => section.fields.filter((field) => !sameValue(data.values[field.key], values.values[field.key])).length;
    const errorsIn = (section: SettingSection) => section.fields.filter((field) => fieldErrors[`values.${field.key}`]).length;
    const totalChanged = sections.reduce((sum, section) => sum + changedIn(section), 0);

    const resetSection = (section: SettingSection) =>
        form.setData('values', { ...data.values, ...Object.fromEntries(section.fields.map((field) => [field.key, defaults[field.key]])) });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = modal.visit({ preserveScroll: true });
        if (version) {
            form.put(urls.submit, options);
        } else {
            form.post(urls.submit, options);
        }
    };

    return (
        <>
            <form onSubmit={submit} className="grid min-w-0 gap-4">
                <Card>
                    <CardContent className="grid gap-4 md:grid-cols-[minmax(0,12rem)_minmax(0,1fr)]">
                        <FormField id="settings-from" label="Effective from" required error={errors.effective_from} hint="Payrolls whose period starts on or after this date.">
                            <Input id="settings-from" type="date" required value={data.effective_from} onChange={(event) => form.setData('effective_from', event.target.value)} />
                        </FormField>
                        <FormField id="settings-label" label="Name" required error={errors.label} hint='e.g. "PhilHealth 2027 rate" or "New OT policy"'>
                            <Input id="settings-label" required maxLength={150} value={data.label} onChange={(event) => form.setData('label', event.target.value)} />
                        </FormField>
                        <FormField id="settings-notes" label="Notes" error={errors.notes} className="md:col-span-2">
                            <Textarea id="settings-notes" rows={2} value={data.notes} onChange={(event) => form.setData('notes', event.target.value)} placeholder="Why it changed, circular number, memo..." />
                        </FormField>
                        {version?.usage && version.usage.total > 0 && (
                            <p className="text-xs text-muted-foreground md:col-span-2">
                                Used by {version.usage.total} payroll(s). The {version.usage.finalized} finalized one(s) keep their amounts; drafts use the new values when recomputed.
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Tabs defaultValue={sections[0]?.key} className="min-w-0">
                    <TabsList className="h-auto max-w-full flex-wrap justify-start">
                        {sections.map((section) => {
                            const changed = changedIn(section);
                            const failed = errorsIn(section);
                            return (
                                <TabsTrigger key={section.key} value={section.key} className="gap-1.5">
                                    {section.title}
                                    {failed > 0 ? (
                                        <Badge variant="destructive" className="h-5 px-1.5">
                                            {failed}
                                        </Badge>
                                    ) : (
                                        changed > 0 && (
                                            <Badge variant="outline" className="h-5 border-amber-300 bg-amber-50 px-1.5 text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                                {changed}
                                            </Badge>
                                        )
                                    )}
                                </TabsTrigger>
                            );
                        })}
                    </TabsList>

                    {sections.map((section) => (
                        <TabsContent key={section.key} value={section.key}>
                            <Card className="gap-4">
                                <CardHeader className="flex flex-row flex-wrap items-start justify-between gap-3">
                                    <div className="grid min-w-0 gap-1">
                                        <CardTitle className="text-base">{section.title}</CardTitle>
                                        <CardDescription>{section.description}</CardDescription>
                                    </div>
                                    <Button type="button" variant="ghost" size="sm" onClick={() => resetSection(section)}>
                                        <RotateCcw />
                                        Starting values
                                    </Button>
                                </CardHeader>
                                <CardContent className="grid gap-x-6 gap-y-4 md:grid-cols-2 xl:grid-cols-3">
                                    {section.fields.map((field) => {
                                        const changed = !sameValue(data.values[field.key], values.values[field.key]);
                                        return (
                                            <FormField
                                                key={field.key}
                                                id={`setting-${field.key}`}
                                                label={
                                                    <span className="flex items-center gap-1.5">
                                                        {field.label}
                                                        {changed && <span className="size-1.5 rounded-full bg-amber-500" aria-label="changed" />}
                                                    </span>
                                                }
                                                error={fieldErrors[`values.${field.key}`]}
                                                hint={changed ? `Was ${formatSetting(field, values.values[field.key])}.${field.help ? ` ${field.help}` : ''}` : field.help}
                                            >
                                                <SettingInput
                                                    id={`setting-${field.key}`}
                                                    field={field}
                                                    value={data.values[field.key]}
                                                    invalid={!!fieldErrors[`values.${field.key}`]}
                                                    onChange={(value) => setValue(field.key, value)}
                                                />
                                            </FormField>
                                        );
                                    })}
                                </CardContent>
                            </Card>
                        </TabsContent>
                    ))}
                </Tabs>

                <div className="sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-2 rounded-lg border bg-background/95 px-4 py-3 shadow-sm backdrop-blur">
                    <div className="flex flex-wrap items-center gap-2">
                        <Button type="button" variant="outline" onClick={() => setDialog('test')}>
                            <FlaskConical />
                            Test these values
                        </Button>
                        <Button type="button" variant="outline" onClick={() => setDialog('calculator')}>
                            <Calculator />
                            Contributions
                        </Button>
                        {totalChanged > 0 && <span className="text-xs text-amber-700 dark:text-amber-300">{totalChanged} value(s) changed</span>}
                    </div>
                    <div className="flex gap-2">
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
                            {version ? 'Save changes' : 'Save version'}
                        </Button>
                    </div>
                </div>
            </form>

            <DetailDialog
                open={dialog === 'test'}
                onOpenChange={(open) => !open && setDialog(null)}
                title="Test these values"
                description="Saved settings for the cutoff vs the values in this form. Nothing is saved."
                size="xl"
            >
                <TestPanel options={test} compare={{ values: data.values }} compareLabel="This form" />
            </DetailDialog>
            <DetailDialog open={dialog === 'calculator'} onOpenChange={(open) => !open && setDialog(null)} title="Contribution calculator" size="lg">
                <ContributionCalculator url={test.urls.contributions} values={data.values} />
            </DetailDialog>
        </>
    );
}
