import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save } from 'lucide-react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';

interface Values {
    garage: string;
    name: string;
    body_number: string;
    plate_number: string;
}

interface Props {
    bus: { id: number; body_number: string } | null;
    values: Values;
    garages: string[];
    urls: { index: string; submit: string };
}

const FIELDS: [keyof Values, string][] = [
    ['garage', 'Garage'],
    ['name', 'Bus name'],
    ['body_number', 'Body number'],
    ['plate_number', 'Plate number'],
];

export default function AllBusForm({ bus, values, garages, urls }: Props) {
    const form = useForm<Values>(values);
    const title = bus ? `Edit bus ${bus.body_number}` : 'Add bus';

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (bus) {
            form.put(urls.submit);
        } else {
            form.post(urls.submit);
        }
    };

    return (
        <AppLayout title={title}>
            <PageHeader
                title={title}
                description="Garage, bus name, body number and plate number. Body and plate numbers must be unique."
                actions={
                    <Button variant="outline" asChild>
                        <Link href={urls.index}>
                            <ArrowLeft />
                            Back to bus list
                        </Link>
                    </Button>
                }
            />
            <Card className="max-w-2xl">
                <CardContent>
                    <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                        {FIELDS.map(([key, label]) => (
                            <FormField key={key} id={key} label={label} required error={form.errors[key]}>
                                <Input
                                    id={key}
                                    required
                                    list={key === 'garage' ? 'garage-options' : undefined}
                                    value={form.data[key]}
                                    aria-invalid={!!form.errors[key]}
                                    onChange={(event) => form.setData(key, event.target.value)}
                                />
                            </FormField>
                        ))}
                        <datalist id="garage-options">
                            {garages.map((garage) => (
                                <option key={garage} value={garage} />
                            ))}
                        </datalist>
                        <div className="flex justify-end gap-2 sm:col-span-2">
                            <Button type="button" variant="outline" asChild>
                                <Link href={urls.index}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                Save
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
