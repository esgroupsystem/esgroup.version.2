import { useForm } from '@inertiajs/react';
import { Check, Eye, EyeOff, KeyRound, Loader2, X } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { FormField } from '@/components/form-field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';

interface Props {
    requiresCurrent: boolean;
    urls: { update: string };
}

/** Same rule as ChangePasswordRequest: 7+ characters, a number and a special character. */
const RULES = [
    { label: 'At least 7 characters', test: (value: string) => value.length >= 7 },
    { label: 'A number (0-9)', test: (value: string) => /\d/.test(value) },
    { label: 'A special character (e.g. # @ ! $)', test: (value: string) => /[^A-Za-z0-9\s]/.test(value) },
];

export default function ChangePassword({ requiresCurrent, urls }: Props) {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    const errors = form.errors as Record<string, string | undefined>;
    const [show, setShow] = useState(false);
    const passed = RULES.every((rule) => rule.test(form.data.password));
    const matches = form.data.password !== '' && form.data.password === form.data.password_confirmation;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.update, { onFinish: () => form.reset() });
    };

    const type = show ? 'text' : 'password';

    return (
        <AppLayout title="Change password">
            <PageHeader title="Change password" description="Pick something easy for you to remember but hard for others to guess." />

            <Card className="max-w-xl">
                <form onSubmit={submit}>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <KeyRound className="size-5" />
                            New password
                        </CardTitle>
                        <CardDescription>At least 7 characters, with a number and a special character. Example: jell#2026</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 py-6">
                        {requiresCurrent && (
                            <FormField id="current_password" label="Current password" required error={errors.current_password}>
                                <Input
                                    id="current_password"
                                    type={type}
                                    autoComplete="current-password"
                                    required
                                    value={form.data.current_password}
                                    onChange={(event) => form.setData('current_password', event.target.value)}
                                />
                            </FormField>
                        )}
                        <FormField id="password" label="New password" required error={errors.password}>
                            <div className="relative">
                                <Input
                                    id="password"
                                    type={type}
                                    autoComplete="new-password"
                                    required
                                    className="pr-10"
                                    value={form.data.password}
                                    onChange={(event) => form.setData('password', event.target.value)}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShow((value) => !value)}
                                    className="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-muted-foreground hover:text-foreground"
                                    aria-label={show ? 'Hide passwords' : 'Show passwords'}
                                >
                                    {show ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
                                </button>
                            </div>
                        </FormField>
                        <ul className="grid gap-1 text-sm" aria-label="Password rules">
                            {RULES.map((rule) => {
                                const ok = rule.test(form.data.password);

                                return (
                                    <li key={rule.label} className={cn('flex items-center gap-2', ok ? 'text-emerald-700 dark:text-emerald-400' : 'text-muted-foreground')}>
                                        {ok ? <Check className="size-4" /> : <X className="size-4" />}
                                        {rule.label}
                                    </li>
                                );
                            })}
                        </ul>
                        <FormField
                            id="password_confirmation"
                            label="Confirm new password"
                            required
                            error={errors.password_confirmation}
                            hint={form.data.password_confirmation ? (matches ? 'Passwords match.' : 'Passwords do not match yet.') : undefined}
                        >
                            <Input
                                id="password_confirmation"
                                type={type}
                                autoComplete="new-password"
                                required
                                value={form.data.password_confirmation}
                                onChange={(event) => form.setData('password_confirmation', event.target.value)}
                            />
                        </FormField>
                    </CardContent>
                    <CardFooter className="justify-end">
                        <Button type="submit" disabled={form.processing || !passed || !matches}>
                            {form.processing && <Loader2 className="animate-spin" />}
                            Update password
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </AppLayout>
    );
}
