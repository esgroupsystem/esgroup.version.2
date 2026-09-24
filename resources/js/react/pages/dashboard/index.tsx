import { useForm, usePage } from '@inertiajs/react';
import { CalendarDays, Clock, KeyRound, ShieldCheck, Sparkles, UserCheck } from 'lucide-react';
import type { FormEvent } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardAction, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { splitDateTime } from '@/lib/format';

interface DashboardProps {
    greeting: string;
    message: string;
    today: string;
}

export default function Dashboard({ greeting, message, today }: DashboardProps) {
    const { auth } = usePage().props;
    const user = auth.user;
    const lastLogin = splitDateTime(user?.last_online ?? null);
    const accountStatus = user?.account_status ?? 'active';

    return (
        <AppLayout title="Dashboard">
            <div className="flex flex-col gap-1">
                <p className="flex items-center gap-2 text-sm text-muted-foreground">
                    <CalendarDays className="size-4" />
                    {today}
                </p>
                <h2 className="text-2xl font-semibold tracking-tight">
                    {greeting}, {user?.name ?? 'there'}
                </h2>
                <p className="text-muted-foreground">Welcome back to Jell Group. {message}</p>
            </div>

            <div className="grid grid-cols-1 gap-4 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card *:data-[slot=card]:shadow-xs sm:grid-cols-2 xl:grid-cols-4 dark:*:data-[slot=card]:bg-card">
                <Card className="@container/card">
                    <CardHeader>
                        <CardDescription>Today's Status</CardDescription>
                        <CardTitle className="text-xl font-semibold whitespace-nowrap @[300px]/card:text-2xl tabular-nums">Ready to go</CardTitle>
                        <CardAction>
                            <Badge variant="outline">
                                <Sparkles />
                                Online
                            </Badge>
                        </CardAction>
                    </CardHeader>
                    <CardFooter className="flex-col items-start gap-1.5 text-sm">
                        <div className="font-medium">Signed in and unlocked</div>
                        <div className="text-muted-foreground">Your session is active</div>
                    </CardFooter>
                </Card>

                <Card className="@container/card">
                    <CardHeader>
                        <CardDescription>Last Login</CardDescription>
                        <CardTitle className="text-xl font-semibold whitespace-nowrap @[300px]/card:text-2xl tabular-nums">
                            {lastLogin?.date ?? 'First login'}
                        </CardTitle>
                        <CardAction>
                            <Badge variant="outline">
                                <Clock />
                                PHT
                            </Badge>
                        </CardAction>
                    </CardHeader>
                    <CardFooter className="flex-col items-start gap-1.5 text-sm">
                        <div className="font-medium">
                            {lastLogin ? `at ${lastLogin.time}` : 'Welcome to the system'}
                        </div>
                        <div className="text-muted-foreground">Philippine time</div>
                    </CardFooter>
                </Card>

                <Card className="@container/card">
                    <CardHeader>
                        <CardDescription>Account Status</CardDescription>
                        <CardTitle className="text-xl font-semibold whitespace-nowrap @[300px]/card:text-2xl capitalize">{accountStatus}</CardTitle>
                        <CardAction>
                            <Badge variant="outline">
                                <UserCheck />
                                Verified
                            </Badge>
                        </CardAction>
                    </CardHeader>
                    <CardFooter className="flex-col items-start gap-1.5 text-sm">
                        <div className="font-medium">Account in good standing</div>
                        <div className="text-muted-foreground">Managed by your administrator</div>
                    </CardFooter>
                </Card>

                <Card className="@container/card">
                    <CardHeader>
                        <CardDescription>Role</CardDescription>
                        <CardTitle className="text-xl font-semibold whitespace-nowrap @[300px]/card:text-2xl">{user?.role ?? 'User'}</CardTitle>
                        <CardAction>
                            <Badge variant="outline">
                                <ShieldCheck />
                                Access
                            </Badge>
                        </CardAction>
                    </CardHeader>
                    <CardFooter className="flex-col items-start gap-1.5 text-sm">
                        <div className="font-medium">Menu follows your permissions</div>
                        <div className="text-muted-foreground">Ask an admin for more access</div>
                    </CardFooter>
                </Card>
            </div>

            <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>A little reminder for today</CardTitle>
                        <CardDescription>
                            Small progress is still progress. Keep your day simple, focused, and steady.
                        </CardDescription>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Take it easy</CardTitle>
                        <CardDescription>Breathe, organize, and start one task at a time.</CardDescription>
                    </CardHeader>
                </Card>
            </div>

            {user?.must_change_password && <ForcePasswordDialog />}
        </AppLayout>
    );
}

/** Blocking dialog shown until a temporary password is replaced. */
function ForcePasswordDialog() {
    const { routes } = usePage().props;
    const form = useForm({ password: '', password_confirmation: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(routes.changePassword, {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <Dialog open>
            <DialogContent
                showCloseButton={false}
                onEscapeKeyDown={(event) => event.preventDefault()}
                onPointerDownOutside={(event) => event.preventDefault()}
                onInteractOutside={(event) => event.preventDefault()}
            >
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <KeyRound className="size-5" />
                            Change your password
                        </DialogTitle>
                        <DialogDescription>
                            Please change your temporary password to continue using the system.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-2">
                        <Label htmlFor="password">New password</Label>
                        <Input
                            id="password"
                            type="password"
                            autoComplete="new-password"
                            autoFocus
                            required
                            value={form.data.password}
                            onChange={(event) => form.setData('password', event.target.value)}
                            aria-invalid={Boolean(form.errors.password)}
                        />
                        {form.errors.password && <p className="text-sm text-destructive">{form.errors.password}</p>}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            autoComplete="new-password"
                            required
                            value={form.data.password_confirmation}
                            onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        />
                    </div>

                    <DialogFooter>
                        <Button type="submit" className="w-full" disabled={form.processing}>
                            {form.processing ? 'Updating…' : 'Update password'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
