import { Head, useForm } from '@inertiajs/react';
import { ArrowRight, Eye, EyeOff, Loader2, Lock } from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent } from 'react';
import { csrfToken } from '@/lib/format';
import { cn } from '@/lib/utils';

interface Props {
    user: { name: string | null; username: string | null };
    urls: { unlock: string; logout: string };
}

/**
 * Locked session: the whole screen is a blurred app shell (no real data is
 * sent while locked) with a lock and a password box in the middle. Unlocking
 * returns to the page that was open.
 */
export default function LockScreen({ user, urls }: Props) {
    const form = useForm({ password: '' });
    const [show, setShow] = useState(false);
    const input = useRef<HTMLInputElement>(null);
    const logout = useRef<HTMLFormElement>(null);
    const error = form.errors.password;

    useEffect(() => input.current?.focus(), [error]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.unlock, { preserveScroll: true, onFinish: () => form.reset('password') });
    };

    return (
        <div className="fixed inset-0 overflow-hidden bg-slate-100 dark:bg-slate-950">
            <Head title="Locked" />

            {/* Blurred stand-in for the app: sidebar, header and cards, but no data. */}
            <div aria-hidden className="absolute inset-0 scale-105 blur-xl saturate-150">
                <div className="flex h-full">
                    <div className="hidden w-64 flex-col gap-3 bg-white p-5 md:flex dark:bg-slate-900">
                        <div className="h-9 w-40 rounded-lg bg-slate-800 dark:bg-slate-200" />
                        {Array.from({ length: 12 }, (_, index) => (
                            <div key={index} className={cn('h-4 rounded bg-slate-200 dark:bg-slate-700', index % 4 === 0 ? 'mt-4 w-20' : 'w-44')} />
                        ))}
                    </div>
                    <div className="flex flex-1 flex-col gap-6 p-8">
                        <div className="h-8 w-72 rounded-lg bg-slate-300 dark:bg-slate-700" />
                        <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                            {['bg-sky-300', 'bg-emerald-300', 'bg-amber-300', 'bg-violet-300'].map((tone) => (
                                <div key={tone} className="h-28 rounded-2xl bg-white p-4 dark:bg-slate-900">
                                    <div className={cn('h-10 w-24 rounded-lg', tone)} />
                                </div>
                            ))}
                        </div>
                        <div className="flex-1 rounded-2xl bg-white p-6 dark:bg-slate-900">
                            {Array.from({ length: 8 }, (_, index) => (
                                <div key={index} className="mb-4 h-5 rounded bg-slate-200 dark:bg-slate-700" style={{ width: `${90 - ((index * 13) % 40)}%` }} />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
            <div aria-hidden className="absolute inset-0 bg-slate-900/40 backdrop-blur-md" />

            <main className="relative flex h-full items-center justify-center p-4">
                <form onSubmit={submit} className="grid w-full max-w-sm justify-items-center gap-5 text-center text-white" aria-labelledby="lock-title">
                    <div className="relative">
                        <span aria-hidden className="absolute inset-0 animate-ping rounded-full bg-white/20 [animation-duration:2.5s]" />
                        <div className="relative flex size-24 items-center justify-center rounded-full border border-white/30 bg-white/15 shadow-2xl ring-8 ring-white/5 backdrop-blur">
                            <Lock className="size-10" strokeWidth={2.2} />
                        </div>
                    </div>

                    <div className="grid gap-1">
                        <h1 id="lock-title" className="text-2xl font-semibold tracking-tight">
                            Screen locked
                        </h1>
                        <p className="text-sm text-white/75">
                            {user.name ? (
                                <>
                                    Signed in as <span className="font-medium text-white">{user.name}</span>. Enter your password to continue.
                                </>
                            ) : (
                                'Enter your password to continue.'
                            )}
                        </p>
                    </div>

                    <div className="grid w-full gap-2 text-left">
                        <label htmlFor="lock-password" className="sr-only">
                            Password
                        </label>
                        <div
                            className={cn(
                                'flex items-center rounded-xl border bg-white/95 text-slate-900 shadow-xl transition focus-within:ring-4 focus-within:ring-sky-400/40',
                                error ? 'border-red-400' : 'border-white/40',
                            )}
                        >
                            <input
                                ref={input}
                                id="lock-password"
                                name="password"
                                type={show ? 'text' : 'password'}
                                autoComplete="current-password"
                                required
                                placeholder="Password"
                                aria-invalid={!!error}
                                aria-describedby={error ? 'lock-error' : undefined}
                                value={form.data.password}
                                onChange={(event) => form.setData('password', event.target.value)}
                                className="h-12 min-w-0 flex-1 rounded-l-xl bg-transparent px-4 text-base outline-none placeholder:text-slate-400"
                            />
                            <button
                                type="button"
                                onClick={() => setShow((value) => !value)}
                                className="p-2 text-slate-500 hover:text-slate-900"
                                aria-label={show ? 'Hide password' : 'Show password'}
                            >
                                {show ? <EyeOff className="size-5" /> : <Eye className="size-5" />}
                            </button>
                            <button
                                type="submit"
                                disabled={form.processing || !form.data.password}
                                className="m-1 flex size-10 items-center justify-center rounded-lg bg-sky-600 text-white transition hover:bg-sky-700 disabled:opacity-50"
                                aria-label="Unlock"
                            >
                                {form.processing ? <Loader2 className="size-5 animate-spin" /> : <ArrowRight className="size-5" />}
                            </button>
                        </div>
                        {error && (
                            <p id="lock-error" role="alert" className="rounded-lg bg-red-500/90 px-3 py-2 text-sm text-white">
                                {error}
                            </p>
                        )}
                    </div>

                    <button type="button" onClick={() => logout.current?.submit()} className="text-sm text-white/80 underline-offset-4 hover:text-white hover:underline">
                        Not you? Sign out
                    </button>
                </form>
                <form ref={logout} method="POST" action={urls.logout} className="hidden">
                    <input type="hidden" name="_token" value={csrfToken()} />
                </form>
            </main>
        </div>
    );
}
