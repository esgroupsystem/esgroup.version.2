import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState, type FormEvent } from 'react';
import { Turnstile, type TurnstileHandle } from '@/components/auth/turnstile';
import './login.css';

interface Props {
    /** Seconds until sign-in is allowed again (too many attempts); 0 = open. */
    seconds: number;
    old: { username: string; remember: boolean };
    turnstileSiteKey: string;
    images: { background: string; logo: string };
    urls: { login: string };
}

type Check = 'pending' | 'passed' | 'expired' | 'help';

const CHECK_TEXT: Record<Check, string> = {
    pending: 'Complete the security check to enable sign in.',
    passed: 'Security check passed. You can now sign in.',
    expired: 'Please complete the security check again before signing in.',
    help: 'Please contact the IT Department to reset your password or get help signing in.',
};

/**
 * Sign-in page. Same look as the former Blade login (login.css is its exact
 * stylesheet, lx- classes): bus photo with a navy wash, hero on the left,
 * card centred in the right half. Same endpoint and fields as before.
 */
export default function Login({ seconds: initialSeconds, old, turnstileSiteKey, images, urls }: Props) {
    const form = useForm({ username: old.username, password: '', remember: old.remember, 'cf-turnstile-response': '' });
    const [show, setShow] = useState(false);
    const [check, setCheck] = useState<Check>('pending');
    const [seconds, setSeconds] = useState(initialSeconds);
    const turnstile = useRef<TurnstileHandle>(null);
    const locked = seconds > 0;
    const verified = form.data['cf-turnstile-response'] !== '';
    const turnstileError = (form.errors as Record<string, string | undefined>).turnstile;

    // Too many attempts: count down, then reload to re-check.
    useEffect(() => setSeconds(initialSeconds), [initialSeconds]);
    useEffect(() => {
        if (!locked) return;
        const timer = window.setInterval(() => {
            setSeconds((value) => {
                if (value <= 1) {
                    window.clearInterval(timer);
                    router.reload();
                    return 0;
                }
                return value - 1;
            });
        }, 1000);

        return () => window.clearInterval(timer);
    }, [locked]);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (locked || !verified) return;
        form.post(urls.login, {
            preserveState: true,
            // A Turnstile token works once: get a new one after a failed attempt.
            onFinish: () => {
                form.reset('password', 'cf-turnstile-response');
                turnstile.current?.reset();
            },
        });
    };

    const help = (event: React.MouseEvent) => {
        event.preventDefault();
        setCheck('help');
    };

    return (
        <div className="lx-body">
            <Head title="Sign in" />
            <main className="lx-page" id="top">
                <img className="lx-bg" src={images.background} alt="" aria-hidden="true" />

                <div className="lx-shell">
                    {/* LEFT: brand + message */}
                    <section className="lx-hero" aria-label="Jell Group">
                        <div className="lx-brand">
                            <div className="lx-brand-mark">
                                <img src={images.logo} alt="" />
                            </div>
                            <div className="lx-brand-name">Jell Group of Company</div>
                        </div>

                        <div className="lx-rule" />

                        <div className="lx-tagline">
                            Safe Travel <i /> Reliable Fleet <i /> Client First
                        </div>

                        <h1 className="lx-title">
                            <span>Moving People Safely,</span>
                            <span>
                                <em>One Journey</em> at a Time.
                            </span>
                        </h1>

                        <p className="lx-lead">Every journey matters. We move with safety, serve with respect, and carry every client toward a better destination.</p>

                        <div className="lx-hero-foot">
                            <div className="lx-pillars">
                                <div className="lx-pillar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        <path d="m9 12 2 2 4-4" />
                                    </svg>
                                    <strong>Safe Travel</strong>
                                    <span>Security in every route</span>
                                </div>
                                <div className="lx-pillar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M8 6v6" />
                                        <path d="M15 6v6" />
                                        <path d="M2 12h19.6" />
                                        <path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3" />
                                        <circle cx="7" cy="18" r="2" />
                                        <path d="M9 18h5" />
                                        <circle cx="16" cy="18" r="2" />
                                    </svg>
                                    <strong>Reliable Fleet</strong>
                                    <span>Ready to serve daily</span>
                                </div>
                                <div className="lx-pillar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    <strong>Client First</strong>
                                    <span>Built on trust and respect</span>
                                </div>
                            </div>
                            <div className="lx-system">
                                <span>Employee Transport Portal</span>
                            </div>
                        </div>
                    </section>

                    {/* RIGHT: login card */}
                    <div className="lx-card-wrap">
                        <div className="lx-card">
                            <div className="lx-card-head">
                                <div className="lx-card-logo">
                                    <img src={images.logo} alt="" />
                                </div>
                                <div className="lx-card-brand">Jell Group of Company</div>
                                <h2 className="lx-card-title">Welcome Back</h2>
                                <p className="lx-card-sub">Sign in to access your company portal</p>
                            </div>

                            {/* Errors and flash messages show as top-right toasts. */}
                            <form onSubmit={submit} id="loginForm">
                                <div className="lx-field">
                                    <label className="lx-label" htmlFor="username">
                                        Username
                                    </label>
                                    <div className="lx-input-wrap">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        <input
                                            className="lx-input"
                                            id="username"
                                            name="username"
                                            type="text"
                                            placeholder="Enter your username"
                                            autoComplete="username"
                                            required
                                            autoFocus
                                            disabled={locked}
                                            value={form.data.username}
                                            onChange={(event) => form.setData('username', event.target.value)}
                                        />
                                    </div>
                                    {form.errors.username && <p className="lx-field-error">{form.errors.username}</p>}
                                </div>

                                <div className="lx-field">
                                    <label className="lx-label" htmlFor="password">
                                        Password
                                    </label>
                                    <div className="lx-input-wrap">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" />
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        <input
                                            className="lx-input"
                                            id="password"
                                            name="password"
                                            type={show ? 'text' : 'password'}
                                            placeholder="Enter your password"
                                            autoComplete="current-password"
                                            required
                                            disabled={locked}
                                            value={form.data.password}
                                            onChange={(event) => form.setData('password', event.target.value)}
                                        />
                                        <button type="button" id="togglePassword" className="lx-eye" aria-label={show ? 'Hide password' : 'Show password'} aria-pressed={show} onClick={() => setShow((value) => !value)}>
                                            {show ? (
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                                                    <path d="M9.9 4.2A10.4 10.4 0 0 1 12 4c6.5 0 10 8 10 8a17.6 17.6 0 0 1-2.2 3.2" />
                                                    <path d="M6.6 6.6A17.4 17.4 0 0 0 2 12s3.5 8 10 8a9.7 9.7 0 0 0 5.4-1.6" />
                                                    <path d="M14.1 14.1a3 3 0 1 1-4.2-4.2" />
                                                    <path d="m2 2 20 20" />
                                                </svg>
                                            ) : (
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            )}
                                        </button>
                                    </div>
                                    {form.errors.password && <p className="lx-field-error">{form.errors.password}</p>}
                                </div>

                                <div className="lx-row">
                                    <label className="lx-check" htmlFor="remember">
                                        <input type="checkbox" id="remember" name="remember" disabled={locked} checked={form.data.remember} onChange={(event) => form.setData('remember', event.target.checked)} />
                                        Remember me
                                    </label>
                                    <a className="lx-link" href="#" id="forgotPassword" onClick={help}>
                                        Forgot Password?
                                    </a>
                                </div>

                                <div className="lx-verify">
                                    <Turnstile
                                        ref={turnstile}
                                        siteKey={turnstileSiteKey}
                                        onVerify={(token) => {
                                            form.setData('cf-turnstile-response', token);
                                            setCheck('passed');
                                        }}
                                        onExpire={() => {
                                            form.setData('cf-turnstile-response', '');
                                            setCheck((value) => (value === 'pending' ? 'pending' : 'expired'));
                                        }}
                                    />
                                </div>

                                <p id="turnstileStatus" className={`lx-status ${check === 'passed' || check === 'help' ? 'text-success' : 'text-danger'}`}>
                                    {CHECK_TEXT[check]}
                                </p>

                                {turnstileError && <p className="lx-field-error">{turnstileError}</p>}

                                <button id="loginBtn" className={`lx-submit${locked || !verified ? ' disabled' : ''}`} type="submit" disabled={locked || !verified || form.processing}>
                                    <span id="loginBtnText">{locked ? `Please wait (${seconds}s)` : form.processing ? 'Signing in…' : 'Sign In'}</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </button>
                            </form>

                            <p className="lx-help">
                                Need help?{' '}
                                <a className="lx-link" href="#" id="contactSupport" onClick={help}>
                                    Contact IT Support
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <footer className="lx-footer">
                    <span>&copy; {new Date().getFullYear()} Jell Group of Company. All rights reserved.</span>
                    <nav aria-label="Legal">
                        <span>Privacy</span>
                        <span>Terms</span>
                        <span>Support</span>
                    </nav>
                </footer>
            </main>
        </div>
    );
}
