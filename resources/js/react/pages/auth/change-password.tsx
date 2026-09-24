import { useForm } from '@inertiajs/react';
import { KeyRound, Loader2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';

interface Props {
    requiresCurrent: boolean;
    urls: { update: string };
}

export default function ChangePassword({ requiresCurrent, urls }: Props) {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    const errors = form.errors as Record<string, string | undefined>;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.update, { onFinish: () => form.reset() });
    };

    return (
        <AppLayout title="Change password">
            <PageHeader title="Change password" description="Use a strong password you don't use anywhere else." />

            <Card className="max-w-xl">
                <form onSubmit={submit}>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <KeyRound className="size-5" />
                            New password
                        </CardTitle>
                        <CardDescription>At least 12 characters, with upper and lower case letters, a number and a symbol.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 py-6">
                        {requiresCurrent && (
                            <FormField id="current_password" label="Current password" required error={errors.current_password}>
                                <Input
                                    id="current_password"
                                    type="password"
                                    autoComplete="current-password"
                                    required
                                    value={form.data.current_password}
                                    onChange={(event) => form.setData('current_password', event.target.value)}
                                />
                            </FormField>
                        )}
                        <FormField id="password" label="New password" required error={errors.password}>
                            <Input id="password" type="password" autoComplete="new-password" required value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} />
                        </FormField>
                        <FormField id="password_confirmation" label="Confirm new password" required error={errors.password_confirmation}>
                            <Input
                                id="password_confirmation"
                                type="password"
                                autoComplete="new-password"
                                required
                                value={form.data.password_confirmation}
                                onChange={(event) => form.setData('password_confirmation', event.target.value)}
                            />
                        </FormField>
                    </CardContent>
                    <CardFooter className="justify-end">
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Update password
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </AppLayout>
    );
}
