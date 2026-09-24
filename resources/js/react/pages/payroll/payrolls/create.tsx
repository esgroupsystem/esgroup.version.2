import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Calculator, Info, LoaderCircle } from 'lucide-react';
import type { FormEvent } from 'react';
import { CutoffPicker, type CutoffValue } from '@/components/cutoff-picker';
import { useModal } from '@/components/modal/modal-context';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { definePage } from '@/lib/define-page';

interface Props {
    defaults: CutoffValue;
    payrollGroups: Record<string, string>;
    years: number[];
    urls: { index: string; store: string };
}

export default definePage<Props>({
    title: () => 'Generate Payroll',
    description: () =>
        'Uses attendance summaries, approved adjustments, salary configurations, government contributions, loans, allowances and deduction schedules.',
    actions: (props) => <BackLink {...props} />,
    size: 'md',
    Content: GeneratePayroll,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.index}>
                <ArrowLeft />
                Back to payroll
            </Link>
        </Button>
    );
}

function GeneratePayroll({ defaults, payrollGroups, years, urls }: Props) {
    const modal = useModal();
    const form = useForm({
        garage_group: '',
        cutoff_month: defaults.cutoff_month,
        cutoff_year: defaults.cutoff_year,
        cutoff_type: defaults.cutoff_type,
        rebuild_summary: true,
        remarks: '',
    });

    const { data, setData, errors, processing } = form;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        // In a modal, the new payroll opens right after it is generated.
        form.post(urls.store, modal.visit({ preserveScroll: true }));
    };

    return (
        <>
            <form onSubmit={submit} className={modal.inModal ? 'grid gap-4' : 'grid max-w-4xl gap-4 lg:gap-6'}>
                <Card>
                    <CardHeader>
                        <CardTitle>Payroll group</CardTitle>
                        <CardDescription>Only active and eligible employees assigned to the selected payroll group will be evaluated.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-1.5 md:max-w-sm">
                        <Label htmlFor="garage-group">Payroll group</Label>
                        <Select value={data.garage_group || undefined} onValueChange={(value) => setData('garage_group', value)}>
                            <SelectTrigger id="garage-group" className="w-full" aria-invalid={Boolean(errors.garage_group)}>
                                <SelectValue placeholder="Select payroll group" />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(payrollGroups).map(([value, label]) => (
                                    <SelectItem key={value} value={value}>
                                        {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.garage_group && <p className="text-sm text-destructive">{errors.garage_group}</p>}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Payroll cutoff</CardTitle>
                        <CardDescription>Define the payroll contribution month and attendance period.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        <div className="grid gap-3 md:grid-cols-3">
                            <CutoffPicker
                                value={{ cutoff_month: data.cutoff_month, cutoff_year: data.cutoff_year, cutoff_type: data.cutoff_type }}
                                onChange={(cutoff) => setData((current) => ({ ...current, ...cutoff }))}
                                years={years}
                                idPrefix="generate-cutoff"
                            />
                        </div>
                        {(errors.cutoff_month || errors.cutoff_year || errors.cutoff_type) && (
                            <p className="text-sm text-destructive">{errors.cutoff_type ?? errors.cutoff_month ?? errors.cutoff_year}</p>
                        )}
                        <Alert>
                            <Info />
                            <AlertTitle>Payroll contribution basis</AlertTitle>
                            <AlertDescription>
                                Business 1st cutoff covers the 26th through the 10th, while business 2nd cutoff covers the 11th through the 25th.
                                Both are assigned to the same contribution month. Government contribution schedules remain configurable through
                                config/payroll.php.
                            </AlertDescription>
                        </Alert>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Processing options</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4">
                        <div className="flex items-start justify-between gap-4 rounded-lg border p-4">
                            <div className="grid gap-1">
                                <Label htmlFor="rebuild-summary">Rebuild attendance summary first</Label>
                                <p className="text-xs text-muted-foreground">
                                    Recommended. Uses the latest schedules, biometrics, adjustments, holidays, and leaves for this cutoff.
                                </p>
                            </div>
                            <Switch id="rebuild-summary" checked={data.rebuild_summary} onCheckedChange={(checked) => setData('rebuild_summary', checked)} />
                        </div>
                        <div className="grid gap-1.5">
                            <Label htmlFor="payroll-remarks">Remarks</Label>
                            <Textarea
                                id="payroll-remarks"
                                rows={3}
                                placeholder="Optional notes for this payroll run."
                                value={data.remarks}
                                onChange={(event) => setData('remarks', event.target.value)}
                            />
                            {errors.remarks && <p className="text-sm text-destructive">{errors.remarks}</p>}
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
                        {processing ? <LoaderCircle className="animate-spin" /> : <Calculator />}
                        Generate payroll
                    </Button>
                </div>
            </form>

            <Dialog open={processing}>
                <DialogContent showCloseButton={false} onInteractOutside={(event) => event.preventDefault()}>
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <LoaderCircle className="size-5 animate-spin" />
                            Generating payroll…
                        </DialogTitle>
                        <DialogDescription>
                            Rebuilding attendance and computing every employee in the selected group. This can take a minute — please keep this page open.
                        </DialogDescription>
                    </DialogHeader>
                </DialogContent>
            </Dialog>
        </>
    );
}
