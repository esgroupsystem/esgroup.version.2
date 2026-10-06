import { Calculator, ChevronDown, LoaderCircle } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { peso } from '@/lib/format';
import { postJson } from './test-panel';
import type { SettingValues } from './types';

interface Contributions {
    salary: number;
    sss: { msc: number; employee: number; employer: number; ec: number; mpf_msc: number };
    philhealth: { base: number; rate: number; employee: number; employer: number };
    pagibig: { fund_salary: number; employee_rate: number; employee: number; employer: number };
    employee_total: number;
    employer_total: number;
    sss_table: { from: number; to: number | null; msc: number; employee: number; employer: number; ec: number }[];
}

const percent = (value: number) => `${Number((value * 100).toFixed(4))}%`;

/** SSS / PhilHealth / Pag-IBIG for one monthly salary, with saved or unsaved settings. */
export function ContributionCalculator({ url, values }: { url: string; values?: SettingValues }) {
    const [salary, setSalary] = useState('20000');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState<Contributions | null>(null);

    const compute = async () => {
        setLoading(true);
        try {
            const data = await postJson<Contributions>(url, { salary: Number(salary || 0), ...(values ? { values } : {}) });
            if (data) setResult(data);
        } finally {
            setLoading(false);
        }
    };

    return (
        <Card className="gap-4">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base">
                    <Calculator className="size-4" aria-hidden />
                    Contribution calculator
                </CardTitle>
                <CardDescription>Monthly SSS, PhilHealth and Pag-IBIG for a salary{values ? ', using the values in this form' : ', using the settings in effect today'}.</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4">
                <form
                    className="flex flex-wrap items-end gap-2"
                    onSubmit={(event) => {
                        event.preventDefault();
                        void compute();
                    }}
                >
                    <div className="grid min-w-0 flex-1 gap-1.5 sm:flex-none">
                        <Label htmlFor="calc-salary">Monthly salary / compensation</Label>
                        <div className="relative">
                            <span className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground">₱</span>
                            <Input
                                id="calc-salary"
                                type="number"
                                step="any"
                                min={0}
                                inputMode="decimal"
                                className="w-full pl-7 tabular-nums sm:w-56"
                                value={salary}
                                onChange={(event) => setSalary(event.target.value)}
                            />
                        </div>
                    </div>
                    <Button type="submit" variant="outline" disabled={loading}>
                        {loading ? <LoaderCircle className="animate-spin" /> : <Calculator />}
                        Compute
                    </Button>
                </form>

                {result && (
                    <>
                        <div className="overflow-x-auto rounded-lg border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Contribution</TableHead>
                                        <TableHead>Based on</TableHead>
                                        <TableHead className="text-right">Employee</TableHead>
                                        <TableHead className="text-right">Employer</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell className="font-medium">SSS</TableCell>
                                        <TableCell className="text-xs text-muted-foreground">
                                            MSC {peso(result.sss.msc)}
                                            {result.sss.mpf_msc > 0 && ` (MPF ${peso(result.sss.mpf_msc)})`}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(result.sss.employee)}</TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {peso(result.sss.employer)}
                                            <div className="text-xs text-muted-foreground">+ EC {peso(result.sss.ec)}</div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className="font-medium">PhilHealth</TableCell>
                                        <TableCell className="text-xs text-muted-foreground">
                                            {peso(result.philhealth.base)} × {percent(result.philhealth.rate)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(result.philhealth.employee)}</TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(result.philhealth.employer)}</TableCell>
                                    </TableRow>
                                    <TableRow>
                                        <TableCell className="font-medium">Pag-IBIG</TableCell>
                                        <TableCell className="text-xs text-muted-foreground">
                                            {peso(result.pagibig.fund_salary)} × {percent(result.pagibig.employee_rate)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(result.pagibig.employee)}</TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(result.pagibig.employer)}</TableCell>
                                    </TableRow>
                                    <TableRow className="bg-primary/5 font-semibold hover:bg-primary/5">
                                        <TableCell colSpan={2}>Total per month</TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(result.employee_total)}</TableCell>
                                        <TableCell className="text-right tabular-nums">{peso(result.employer_total)}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <Collapsible>
                            <CollapsibleTrigger asChild>
                                <Button type="button" variant="ghost" size="sm" className="h-auto w-fit max-w-full text-left whitespace-normal">
                                    <ChevronDown />
                                    SSS table ({result.sss_table.length} brackets)
                                </Button>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <div className="mt-2 max-h-96 overflow-auto rounded-lg border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Compensation range</TableHead>
                                                <TableHead className="text-right">MSC</TableHead>
                                                <TableHead className="text-right">Employee</TableHead>
                                                <TableHead className="text-right">Employer</TableHead>
                                                <TableHead className="text-right">EC</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {result.sss_table.map((row) => (
                                                <TableRow key={row.msc}>
                                                    <TableCell className="tabular-nums whitespace-nowrap">
                                                        {row.to === null ? `${peso(row.from)} and up` : `${peso(row.from)} – ${peso(row.to)}`}
                                                    </TableCell>
                                                    <TableCell className="text-right tabular-nums">{peso(row.msc)}</TableCell>
                                                    <TableCell className="text-right tabular-nums">{peso(row.employee)}</TableCell>
                                                    <TableCell className="text-right tabular-nums">{peso(row.employer)}</TableCell>
                                                    <TableCell className="text-right tabular-nums">{peso(row.ec)}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    </>
                )}
            </CardContent>
        </Card>
    );
}
